# RushSense AI — Backend (Laravel + MySQL)

API ini dibuat PERSIS mengikuti apa yang benar-benar dipanggil oleh
`ApiService` di kode Flutter Anda (`rushsenseai`) — bukan API generik.
Nama kolom, bentuk response, dan endpoint-nya sudah disesuaikan supaya
Flutter-nya tidak perlu diubah sama sekali di sisi pemanggilan API.

**Tidak ada fitur login/user** — app ini untuk satu pemilik kendaraan
saja. Akses API dijaga satu API key statis (`DEVICE_API_KEY`), bukan
akun/password.

## Setup

```bash
composer create-project laravel/laravel rushsense-backend
cd rushsense-backend

# Salin file-file dari hasil scaffold ini ke project baru:
# - database/migrations/*.php
# - app/Models/Vehicle.php, TelemetryReading.php, Trip.php, MaintenanceRecord.php
# - app/Http/Controllers/Api/*.php
# - app/Http/Middleware/VerifyDeviceApiKey.php
# - routes/api.php  <- TIMPA yang default
# - Tambahkan isi config-services-snippet.php ke config/services.php
# - Tambahkan withMiddleware(...) dari bootstrap-app-middleware-snippet.php
#   ke bootstrap/app.php (file ini sudah ada di project, cukup isi bagian
#   withMiddleware-nya, jangan timpa seluruh file)

cp .env.example .env   # isi kredensial MySQL + DEVICE_API_KEY
php artisan key:generate
php artisan migrate

php artisan serve
```

## Endpoint yang tersedia

Semua endpoint di bawah wajib header `X-API-Key: <DEVICE_API_KEY Anda>`
(lihat middleware `device.key`).

| Method | Path | Dipanggil dari Flutter |
|---|---|---|
| GET | `/api/vehicles` | *(tersedia, belum dipanggil UI)* |
| POST | `/api/vehicles` | `VehicleProfilePage` (tombol "Sinkronkan ke server") |
| POST | `/api/telemetry` | `ApiService.sendTelemetry` + `TelemetrySyncService` (retry offline) |
| GET | `/api/vehicles/{id}/telemetry` | *(tersedia, belum dipanggil UI)* |
| POST | `/api/vehicles/{id}/trips` | `TripController.stopTrip()` otomatis + tombol "Sinkronkan" manual |
| GET | `/api/vehicles/{id}/trips` | *(tersedia, belum dipanggil UI)* |
| GET | `/api/vehicles/{id}/trips/{tripId}` | *(tersedia - detail 1 trip termasuk rute GPS lengkap)* |
| POST | `/api/vehicles/{id}/maintenance` | `MaintenanceController.addRecord()` otomatis + tombol "Sinkronkan" manual |
| GET | `/api/vehicles/{id}/maintenance` | *(tersedia, belum dipanggil UI)* |

