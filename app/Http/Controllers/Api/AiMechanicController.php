<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiDiagnosis;
use App\Models\Vehicle;
use Illuminate\Http\Request;
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
        ]);

        $apiKey = config('services.anthropic.api_key');
        if (!$apiKey) {
            return response()->json([
                'error' => 'ANTHROPIC_API_KEY belum diset di backend - fitur AI Mechanic '
                    . 'butuh ini untuk berfungsi.',
            ], 500);
        }

        $explanation = $this->callAnthropic($apiKey, $data, $vehicle);
        if ($explanation === null) {
            return response()->json(['error' => 'Gagal memanggil AI - coba lagi.'], 502);
        }

        $diagnosis = $vehicle->aiDiagnoses()->create([
            'input_context' => $data,
            'explanation' => $explanation,
        ]);

        return response()->json($diagnosis, 201);
    }

    public function history(Vehicle $vehicle)
    {
        return $vehicle->aiDiagnoses()->orderByDesc('created_at')->limit(20)->get();
    }

    protected function callAnthropic(string $apiKey, array $context, Vehicle $vehicle): ?string
    {
        $prompt = $this->buildPrompt($context, $vehicle);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 500,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]);

            if ($response->successful()) {
                $blocks = $response->json('content', []);
                foreach ($blocks as $block) {
                    if (($block['type'] ?? null) === 'text') {
                        return $block['text'];
                    }
                }
            }

            Log::warning('AI Mechanic call failed: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('AI Mechanic exception: ' . $e->getMessage());
        }

        return null;
    }

    protected function buildPrompt(array $context, Vehicle $vehicle): string
    {
        $vehicleDesc = "{$vehicle->brand} {$vehicle->model} {$vehicle->year} ({$vehicle->engine_name})";

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

        Tulis 3-5 kalimat:
        1. Ringkas kondisi kendaraan secara keseluruhan.
        2. Kalau ada DTC/anomali, jelaskan kemungkinan penyebab paling umum
           (bukan pasti - ini indikasi, sampaikan sebagai kemungkinan).
        3. Sarankan langkah berikutnya yang wajar (pantau, servis rutin, atau
           segera ke bengkel - sesuaikan urgensi dengan data).

        Jangan mengarang kode/istilah yang tidak disebutkan di atas. Kalau
        data terlalu sedikit untuk kesimpulan yang berarti, katakan itu
        dengan jujur alih-alih memaksakan diagnosa.
        PROMPT;
    }
}
