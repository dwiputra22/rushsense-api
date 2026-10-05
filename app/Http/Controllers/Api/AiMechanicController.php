<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiDiagnosis;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMechanicController extends Controller
{
    public function analyze(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'dtc_codes' => 'nullable|array',
            'dtc_codes.*' => 'string',
            'anomalies' => 'nullable|array',
            'anomalies.*.label' => 'required_with:anomalies|string',
            'anomalies.*.actual_value' => 'required_with:anomalies|numeric',
            'anomalies.*.expected_mean' => 'required_with:anomalies|numeric',
            'anomalies.*.severity' => 'required_with:anomalies|string',
            'health_score' => 'nullable|integer|min:0|max:100',
            'recent_avg_fuel_consumption' => 'nullable|numeric',
            'recent_eco_score' => 'nullable|integer',
            'drift_signals' => 'nullable|array',
            'drift_signals.*.sensor_label' => 'required_with:drift_signals|string',
            'drift_signals.*.state_label' => 'required_with:drift_signals|string',
            'drift_signals.*.old_mean' => 'required_with:drift_signals|numeric',
            'drift_signals.*.new_mean' => 'required_with:drift_signals|numeric',
            'drift_signals.*.change_percent' => 'required_with:drift_signals|numeric',
            'drift_signals.*.direction' => 'required_with:drift_signals|string',
        ]);

        $timeout = (int) config('services.ollama.timeout', 120);
        $model = config('services.ollama.model');

        if (!config('services.ollama.url') || !$model) {
            return response()->json([
                'error' => 'OLLAMA_URL / OLLAMA_MODEL belum diset di backend - fitur AI Mechanic '
                    . 'butuh Ollama yang berjalan di server.',
            ], 500);
        }

        @set_time_limit($timeout + 15);

        $lock = Cache::lock('ai-mechanic-busy', $timeout + 15);
        if (!$lock->get()) {
            return response()->json([
                'error' => 'AI Mechanic masih memproses permintaan sebelumnya - coba lagi sebentar.',
            ], 429);
        }

        try {
            $explanation = $this->callOllama($data, $vehicle);
        } finally {
            $lock->release();
        }

        if ($explanation === null) {
            return response()->json(['error' => 'AI di server tidak merespons - coba lagi.'], 502);
        }

        $diagnosis = $vehicle->aiDiagnoses()->create([
            'input_context' => $data + ['_model' => $model],
            'explanation' => $explanation,
        ]);

        return response()->json($diagnosis, 201);
    }

    public function history(Vehicle $vehicle)
    {
        return $vehicle->aiDiagnoses()->orderByDesc('created_at')->limit(20)->get();
    }

    protected function callOllama(array $context, Vehicle $vehicle): ?string
    {
        $cfg = config('services.ollama');
        $prompt = $this->buildPrompt($context, $vehicle);

        try {
            $request = Http::connectTimeout(5)->timeout((int) $cfg['timeout'])->acceptJson();
            if (!empty($cfg['api_key'])) {
                $request = $request->withToken($cfg['api_key']);
            }

            $response = $request->post(rtrim($cfg['url'], '/') . '/api/chat', [
                'model' => $cfg['model'],
                'stream' => false,
                'keep_alive' => $cfg['keep_alive'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda asisten mekanik mobil. Jawab HANYA dalam Bahasa Indonesia, '
                            . 'singkat, tanpa markdown, dan jangan mengarang fakta di luar data yang diberikan.',
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'options' => [
                    'temperature' => 0.3,
                    'num_predict' => 400,
                    'num_ctx' => 2048,
                ],
            ]);

            if ($response->successful()) {
                $text = trim((string) $response->json('message.content', ''));
                if ($text !== '') {
                    return $text;
                }
            }

            Log::warning('AI Mechanic (Ollama) call failed: ' . $response->status() . ' ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('AI Mechanic (Ollama) exception: ' . $e->getMessage());
        }

        return null;
    }

    protected function buildPrompt(array $context, Vehicle $vehicle): string
    {
        $vehicleDesc = trim(implode(' ', array_filter([$vehicle->brand, $vehicle->model, $vehicle->year])));
        if (!empty($vehicle->engine_name)) {
            $vehicleDesc .= " ({$vehicle->engine_name})";
        }

        $dtcList = empty($context['dtc_codes'])
            ? 'Tidak ada kode error aktif.'
            : 'Kode error aktif: ' . implode(', ', $context['dtc_codes']) . '.';

        $anomalyLines = [];
        foreach ($context['anomalies'] ?? [] as $a) {
            $anomalyLines[] = "- {$a['label']}: nilai saat ini {$a['actual_value']}, "
                . "biasanya sekitar {$a['expected_mean']} untuk mobil ini (tingkat: {$a['severity']}).";
        }
        $anomalyText = empty($anomalyLines) ? 'Tidak ada anomali sensor.' : implode("\n", $anomalyLines);

        $healthScore = $context['health_score'] ?? 'tidak diketahui';

        $driftLines = [];
        foreach ($context['drift_signals'] ?? [] as $d) {
            $driftLines[] = "- {$d['sensor_label']} saat {$d['state_label']}: {$d['direction']} "
                . round($d['change_percent'], 1) . "% dibanding baseline lama "
                . "(dari {$d['old_mean']} ke {$d['new_mean']}).";
        }
        $driftText = empty($driftLines)
            ? 'Belum ada tren jangka panjang yang cukup data untuk dianalisa.'
            : implode("\n", $driftLines);

        return <<<PROMPT
        Anda adalah mekanik berpengalaman yang menjelaskan kondisi kendaraan
        ke pemilik awam (bukan mekanik) dalam Bahasa Indonesia yang jelas dan
        tidak menakut-nakuti secara berlebihan.

        Kendaraan: {$vehicleDesc}
        Vehicle Health Score: {$healthScore}/100
        {$dtcList}

        Anomali sensor dibanding kebiasaan normal mobil ini sendiri (baseline
        dipelajari dari riwayat berkendara mobil ini, BUKAN standar pabrik):
        {$anomalyText}

        Tren jangka panjang - baseline mobil ini SENDIRI yang bergeser dari
        waktu ke waktu (bukan penyimpangan hari ini, tapi "normal"-nya yang
        berubah pelan, mis. indikasi aki menua atau masalah berkembang):
        {$driftText}

        Tulis 3-6 kalimat:
        1. Ringkas kondisi kendaraan secara keseluruhan.
        2. Kalau ada DTC/anomali, jelaskan kemungkinan penyebab paling umum
           (bukan pasti - ini indikasi, sampaikan sebagai kemungkinan).
        3. Kalau ada tren jangka panjang, jelaskan apa artinya (mis. drift
           voltase turun = kemungkinan aki mulai menua) - ini yang
           membedakan dari sekadar cek kondisi sesaat.
        4. Sarankan langkah berikutnya yang wajar (pantau, servis rutin, atau
           segera ke bengkel - sesuaikan urgensi dengan data).

        Jangan mengarang kode/istilah yang tidak disebutkan di atas. Kalau
        data terlalu sedikit untuk kesimpulan yang berarti, katakan itu
        dengan jujur alih-alih memaksakan diagnosa.
        PROMPT;
    }
}
