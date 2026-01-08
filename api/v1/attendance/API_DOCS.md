# Attendance API Documentation

## Overview
API untuk sistem kehadiran karyawan dengan integrasi shift kerja, validasi lokasi, dan deteksi keterlambatan otomatis.

**Base URL:** `/api/v1/attendance/`

---

## Authentication
Semua endpoint memerlukan Bearer token di header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Endpoints

### 1. Check-In

**Endpoint:** `POST /check-in.php`

**Description:** Catat kehadiran masuk karyawan dengan validasi lokasi dan shift.

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "attendance_id": 123,
    "check_in_time": "08:25:00",
    "status": "Terlambat",
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "grace_period": 15
    },
    "late_minutes": 10,
    "office": {
      "id": 1,
      "name": "Kantor Pusat",
      "address": "Jl. Sudirman No. 123"
    },
    "distance": 45.5,
    "message": "Anda terlambat 10 menit"
  }
}
```

**Success Response (On Time):**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "attendance_id": 124,
    "check_in_time": "07:55:00",
    "status": "Hadir",
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "grace_period": 15
    },
    "office": {
      "id": 1,
      "name": "Kantor Pusat",
      "address": "Jl. Sudirman No. 123"
    },
    "distance": 45.5,
    "message": "Selamat bekerja!"
  }
}
```

**Error Responses:**

**Already Checked In (400):**
```json
{
  "success": false,
  "message": "Already checked in today",
  "error_code": "ALREADY_CHECKED_IN"
}
```

**Location Error (400):**
```json
{
  "success": false,
  "message": "Anda berada di luar radius kantor (500m)",
  "error_code": "LOCATION_ERROR"
}
```

**Not Authenticated (401):**
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "error_code": "UNAUTHORIZED"
}
```

---

### 2. Check-Out

**Endpoint:** `POST /check-out.php`

**Description:** Catat kehadiran pulang karyawan.

**Request Body:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "attendance_id": 123,
    "check_out_time": "17:05:00",
    "work_duration": "8 jam 40 menit",
    "office": {
      "id": 1,
      "name": "Kantor Pusat"
    },
    "distance": 38.2,
    "message": "Terima kasih atas kerja keras Anda!"
  }
}
```

**Error Responses:**

**Not Checked In (400):**
```json
{
  "success": false,
  "message": "Belum check-in hari ini",
  "error_code": "NOT_CHECKED_IN"
}
```

**Already Checked Out (400):**
```json
{
  "success": false,
  "message": "Sudah check-out hari ini",
  "error_code": "ALREADY_CHECKED_OUT"
}
```

---

### 3. Today's Attendance

**Endpoint:** `GET /today.php`

**Description:** Ambil data kehadiran hari ini untuk karyawan yang sedang login.

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "attendance_id": 123,
    "date": "2026-01-08",
    "check_in": "08:25:00",
    "check_out": "17:05:00",
    "status": "Terlambat",
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "grace_period": 15
    },
    "late_minutes": 10,
    "work_duration": "8 jam 40 menit",
    "office": {
      "id": 1,
      "name": "Kantor Pusat",
      "address": "Jl. Sudirman No. 123"
    },
    "notes": null
  }
}
```

**No Attendance Today (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Belum ada kehadiran hari ini"
}
```

---

### 4. Attendance History

**Endpoint:** `GET /history.php`

**Description:** Ambil riwayat kehadiran karyawan.

**Query Parameters:**
- `month` (optional) - Bulan (1-12), default: bulan sekarang
- `year` (optional) - Tahun (YYYY), default: tahun sekarang
- `limit` (optional) - Jumlah record, default: 30

**Example:** `GET /history.php?month=12&year=2025&limit=50`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "month": 12,
    "year": 2025,
    "total_records": 22,
    "summary": {
      "hadir": 18,
      "terlambat": 3,
      "izin": 1,
      "sakit": 0,
      "alpha": 0
    },
    "records": [
      {
        "id": 123,
        "date": "2025-12-20",
        "check_in": "08:25:00",
        "check_out": "17:05:00",
        "status": "Terlambat",
        "shift": {
          "name": "Shift Pagi",
          "start_time": "08:00:00",
          "end_time": "17:00:00"
        },
        "late_minutes": 10,
        "work_duration": "8 jam 40 menit",
        "notes": null
      },
      {
        "id": 122,
        "date": "2025-12-19",
        "check_in": "07:55:00",
        "check_out": "17:00:00",
        "status": "Hadir",
        "shift": {
          "name": "Shift Pagi",
          "start_time": "08:00:00",
          "end_time": "17:00:00"
        },
        "work_duration": "9 jam 5 menit",
        "notes": null
      }
    ]
  }
}
```

---

## Shift System

### Shift-Based Late Detection

Sistem otomatis mendeteksi keterlambatan berdasarkan shift karyawan:

**Logic:**
1. Get employee's current shift
2. Calculate: `shift_start_time + grace_period_minutes`
3. If `check_in_time > (shift_start + grace)` → Status: **Terlambat**
4. Calculate late minutes
5. Store shift_id in attendance record

**Example:**
- Shift: Pagi (08:00 - 17:00)
- Grace Period: 15 minutes
- Allowed Until: 08:15
- Check-in: 08:25
- Result: **Terlambat 10 menit**

### No Shift Assigned

Jika karyawan belum di-assign shift:
- Default: 08:00 + 15 min grace = 08:15
- Status tetap dihitung berdasarkan default time

---

## Status Values

| Status | Description |
|--------|-------------|
| `Hadir` | Check-in tepat waktu (dalam grace period) |
| `Terlambat` | Check-in melewati grace period |
| `Izin` | Izin dengan persetujuan |
| `Sakit` | Sakit dengan surat dokter |
| `Cuti` | Cuti yang disetujui |
| `Alpha` | Tidak hadir tanpa keterangan |

---

## Location Validation

### Geofencing

Sistem memvalidasi lokasi check-in/out:
- Radius: 500 meter dari kantor
- Calculation: Haversine formula
- Return: Distance in meters

**Error jika:**
- Distance > 500m
- No office location configured

---

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `UNAUTHORIZED` | 401 | Token invalid/expired |
| `METHOD_NOT_ALLOWED` | 405 | Wrong HTTP method |
| `VALIDATION_ERROR` | 400 | Missing required fields |
| `ALREADY_CHECKED_IN` | 400 | Sudah check-in hari ini |
| `ALREADY_CHECKED_OUT` | 400 | Sudah check-out hari ini |
| `NOT_CHECKED_IN` | 400 | Belum check-in |
| `LOCATION_ERROR` | 400 | Lokasi di luar radius |
| `EMPLOYEE_NOT_FOUND` | 404 | Data karyawan tidak ada |
| `SERVER_ERROR` | 500 | Database/server error |

---

## cURL Examples

### Check-In
```bash
curl -X POST http://localhost/hrm/api/v1/attendance/check-in.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

### Check-Out
```bash
curl -X POST http://localhost/hrm/api/v1/attendance/check-out.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

### Today's Attendance
```bash
curl -X GET http://localhost/hrm/api/v1/attendance/today.php \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### History
```bash
curl -X GET "http://localhost/hrm/api/v1/attendance/history.php?month=12&year=2025" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Flutter/Dart Example

### Check-In
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

Future<Map<String, dynamic>> checkIn(String token, double lat, double lng) async {
  final response = await http.post(
    Uri.parse('http://localhost/hrm/api/v1/attendance/check-in.php'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'latitude': lat,
      'longitude': lng,
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    
    if (data['success']) {
      // Success
      print('Status: ${data['data']['status']}');
      
      if (data['data']['status'] == 'Terlambat') {
        print('Terlambat: ${data['data']['late_minutes']} menit');
      }
      
      // Show shift info
      if (data['data']['shift'] != null) {
        print('Shift: ${data['data']['shift']['name']}');
        print('Jam: ${data['data']['shift']['start_time']} - ${data['data']['shift']['end_time']}');
      }
      
      return data['data'];
    } else {
      throw Exception(data['message']);
    }
  } else {
    throw Exception('Failed to check in');
  }
}

// Usage
void main() async {
  try {
    final result = await checkIn('your_token', -6.200000, 106.816666);
    print('Check-in successful: ${result['message']}');
  } catch (e) {
    print('Error: $e');
  }
}
```

### Get Today's Attendance
```dart
Future<Map<String, dynamic>?> getTodayAttendance(String token) async {
  final response = await http.get(
    Uri.parse('http://localhost/hrm/api/v1/attendance/today.php'),
    headers: {
      'Authorization': 'Bearer $token',
    },
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return data['data']; // null if no attendance today
  }
  
  throw Exception('Failed to get attendance');
}
```

---

## Notes

### Shift Assignment
- Karyawan harus di-assign shift terlebih dahulu via admin panel
- Shift bisa permanent atau temporary (dengan end_date)
- Satu karyawan hanya bisa punya 1 active shift

### Grace Period
- Configurable per shift (default: 15 minutes)
- Contoh: Shift 08:00 + Grace 15 min = Allowed until 08:15
- Check-in at 08:16 = Terlambat 1 menit

### Shift Allowance
- Tunjangan shift disimpan di master shift
- Akan digunakan untuk perhitungan payroll
- Night shift biasanya dapat allowance lebih tinggi

### Time Zones
- Server timezone: Asia/Jakarta (WIB)
- Semua waktu dalam format 24-hour (HH:mm:ss)

---

## Setup

### 1. Import Database
```bash
mysql -u root -p hris_db < database/shifts_schema.sql
```

### 2. Assign Shift to Employee
Via admin panel: `/admin/shifts/assign.php`

### 3. Configure Office Location
Pastikan ada minimal 1 office location dengan koordinat yang benar.

### 4. Test API
Gunakan Postman atau cURL untuk test endpoints.

---

## Support

Untuk pertanyaan atau issue, hubungi tim development.

**Version:** 2.0  
**Last Updated:** 2026-01-08
