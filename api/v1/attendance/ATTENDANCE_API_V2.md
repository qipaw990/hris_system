# Attendance API Documentation v2.0

## Overview
API untuk sistem kehadiran karyawan dengan integrasi shift kerja, validasi lokasi, deteksi keterlambatan otomatis, dan batasan waktu check-in/check-out.

**Base URL:** `/api/v1/attendance/`  
**Authentication:** Bearer Token Required  
**Version:** 2.0 (Updated: 2026-01-08)

---

## Table of Contents
1. [Authentication](#authentication)
2. [Endpoints](#endpoints)
   - [Check-In](#1-check-in)
   - [Check-Out](#2-check-out)
   - [Get Shift Info](#3-get-shift-info)
   - [Today's Attendance](#4-todays-attendance)
   - [Attendance History](#5-attendance-history)
3. [Shift System](#shift-system)
4. [Time Validation Rules](#time-validation-rules)
5. [Error Codes](#error-codes)
6. [Code Examples](#code-examples)

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

**Description:** Catat kehadiran masuk dengan validasi shift dan lokasi.

**Time Restrictions:**
- ✅ Allowed: 2 jam sebelum shift s/d 3 jam setelah shift dimulai
- ❌ Error jika di luar window

**Request:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
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

**Success Response (Late):**
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
      "name": "Kantor Pusat"
    },
    "distance": 45.5,
    "message": "Anda terlambat 10 menit"
  }
}
```

**Error Response (Too Early):**
```json
{
  "success": false,
  "message": "Belum waktunya check-in. Anda bisa check-in mulai pukul 06:00 (2 jam sebelum shift dimulai)",
  "error_code": "TOO_EARLY"
}
```

**Error Response (Too Late):**
```json
{
  "success": false,
  "message": "Waktu check-in sudah lewat. Batas check-in adalah pukul 11:00. Silakan hubungi HRD untuk koreksi kehadiran.",
  "error_code": "TOO_LATE"
}
```

**Error Response (Already Checked In):**
```json
{
  "success": false,
  "message": "Already checked in today",
  "error_code": "ALREADY_CHECKED_IN"
}
```

**Error Response (Location):**
```json
{
  "success": false,
  "message": "Anda berada di luar radius kantor (500m)",
  "error_code": "LOCATION_ERROR"
}
```

---

### 2. Check-Out

**Endpoint:** `POST /check-out.php`

**Description:** Catat kehadiran pulang dengan validasi minimum jam kerja.

**Time Restrictions:**
- ✅ Minimum: 4 jam kerja
- ✅ Earliest: 1 jam sebelum shift berakhir
- ❌ Error jika belum mencapai minimum

**Request:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "attendance_id": 123,
    "check_in_time": "08:25:00",
    "check_out_time": "17:05:00",
    "work_hours": 8.67,
    "office": {
      "id": 1,
      "name": "Kantor Pusat"
    },
    "distance": 38.2,
    "message": "Terima kasih atas kerja keras Anda!"
  }
}
```

**Error Response (Too Early):**
```json
{
  "success": false,
  "message": "Belum waktunya check-out. Anda baru bekerja 2.5 jam. Minimal kerja 4 jam atau check-out mulai pukul 16:00",
  "error_code": "TOO_EARLY_CHECKOUT"
}
```

**Error Response (Not Checked In):**
```json
{
  "success": false,
  "message": "No check-in record found for today",
  "error_code": "NOT_CHECKED_IN"
}
```

---

### 3. Get Shift Info

**Endpoint:** `GET /shift-info.php`

**Description:** Ambil informasi shift karyawan, batasan waktu, dan aturan check-in/out.

**Use Case:** Tampilkan di mobile app sebelum check-in untuk info shift dan batasan waktu.

**Success Response (Has Shift):**
```json
{
  "success": true,
  "message": "Shift info retrieved",
  "data": {
    "has_shift": true,
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "code": "PAGI",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "grace_period_minutes": 15,
      "shift_allowance": 0,
      "is_night_shift": false,
      "description": "Shift kerja pagi standar"
    },
    "assignment": {
      "effective_date": "2026-01-01",
      "end_date": null,
      "is_permanent": true
    },
    "check_in_window": {
      "earliest": "06:00:00",
      "latest": "11:00:00",
      "can_check_in_now": true
    },
    "check_out_window": {
      "earliest": "16:00:00",
      "recommended": "17:00:00"
    },
    "rules": {
      "check_in_allowed": "2 jam sebelum shift s/d 3 jam setelah shift dimulai",
      "check_out_allowed": "1 jam sebelum shift berakhir",
      "minimum_work_hours": 4,
      "late_if_after": "08:15"
    }
  }
}
```

**Success Response (No Shift):**
```json
{
  "success": true,
  "message": "No shift assigned",
  "data": {
    "has_shift": false,
    "message": "Anda belum di-assign shift. Menggunakan jam kerja default (08:00 - 17:00)",
    "default_shift": {
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "grace_period": 15
    },
    "check_in_window": {
      "earliest": "06:00:00",
      "latest": "11:00:00"
    }
  }
}
```

---

### 4. Today's Attendance

**Endpoint:** `GET /today.php`

**Description:** Ambil data kehadiran hari ini.

**Success Response:**
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
      "end_time": "17:00:00"
    },
    "late_minutes": 10,
    "work_duration": "8 jam 40 menit",
    "office": {
      "id": 1,
      "name": "Kantor Pusat"
    }
  }
}
```

**No Attendance:**
```json
{
  "success": true,
  "data": null,
  "message": "Belum ada kehadiran hari ini"
}
```

---

### 5. Attendance History

**Endpoint:** `GET /history.php`

**Description:** Ambil riwayat kehadiran.

**Query Parameters:**
- `month` (optional) - Bulan (1-12)
- `year` (optional) - Tahun (YYYY)
- `limit` (optional) - Jumlah record (default: 30)

**Example:** `GET /history.php?month=12&year=2025&limit=50`

**Success Response:**
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
          "name": "Shift Pagi"
        },
        "late_minutes": 10,
        "work_duration": "8 jam 40 menit"
      }
    ]
  }
}
```

---

## Shift System

### Shift-Based Late Detection

**Logic:**
1. Get employee's current shift
2. Calculate: `shift_start_time + grace_period_minutes`
3. If `check_in_time > (shift_start + grace)` → Status: **Terlambat**
4. Calculate late minutes
5. Store shift_id in attendance

**Example:**
```
Shift: Pagi (08:00 - 17:00)
Grace Period: 15 minutes
Allowed Until: 08:15
Check-in: 08:25
Result: Terlambat 10 menit
```

### Shift Types

| Shift | Start | End | Grace | Allowance |
|-------|-------|-----|-------|-----------|
| Pagi | 08:00 | 17:00 | 15 min | Rp 0 |
| Siang | 14:00 | 22:00 | 15 min | Rp 50,000 |
| Malam | 22:00 | 06:00 | 15 min | Rp 100,000 |

---

## Time Validation Rules

### Check-In Window

**With Shift:**
- **Earliest:** 2 jam sebelum shift start
- **Latest:** 3 jam setelah shift start
- **Example:** Shift 08:00 → Window: 06:00 - 11:00

**No Shift (Default):**
- **Window:** 06:00 - 11:00

### Check-Out Window

**With Shift:**
- **Earliest:** 1 jam sebelum shift end OR 4 jam setelah check-in (mana yang lebih lama)
- **Example:** Shift end 17:00 → Earliest: 16:00

**Minimum Work Hours:**
- **Required:** 4 jam minimum
- **Example:** Check-in 08:00 → Earliest checkout: 12:00

### Validation Flow

```
Check-In:
1. Get employee shift
2. Calculate window (shift_start - 2h to shift_start + 3h)
3. Validate current_time in window
4. If outside → Error (TOO_EARLY or TOO_LATE)
5. If inside → Proceed with late detection

Check-Out:
1. Get employee shift
2. Calculate minimum (check_in + 4h)
3. Calculate earliest (shift_end - 1h)
4. Use later of the two
5. If current_time < earliest → Error (TOO_EARLY_CHECKOUT)
6. If valid → Proceed
```

---

## Error Codes

| Code | HTTP | Description | Solution |
|------|------|-------------|----------|
| `UNAUTHORIZED` | 401 | Token invalid/expired | Login ulang |
| `TOO_EARLY` | 400 | Check-in terlalu pagi | Tunggu sampai window terbuka |
| `TOO_LATE` | 400 | Check-in terlalu terlambat | Hubungi HRD untuk koreksi |
| `TOO_EARLY_CHECKOUT` | 400 | Check-out terlalu cepat | Tunggu minimal 4 jam kerja |
| `ALREADY_CHECKED_IN` | 400 | Sudah check-in hari ini | - |
| `NOT_CHECKED_IN` | 400 | Belum check-in | Check-in dulu |
| `LOCATION_ERROR` | 400 | Lokasi di luar radius | Datang ke kantor |
| `EMPLOYEE_NOT_FOUND` | 404 | Data karyawan tidak ada | Hubungi admin |
| `SERVER_ERROR` | 500 | Database/server error | Coba lagi |

---

## Code Examples

### cURL

**Get Shift Info:**
```bash
curl -X GET http://localhost/hrm/api/v1/attendance/shift-info.php \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Check-In:**
```bash
curl -X POST http://localhost/hrm/api/v1/attendance/check-in.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

**Check-Out:**
```bash
curl -X POST http://localhost/hrm/api/v1/attendance/check-out.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

### Flutter/Dart

**Get Shift Info:**
```dart
Future<Map<String, dynamic>> getShiftInfo(String token) async {
  final response = await http.get(
    Uri.parse('http://localhost/hrm/api/v1/attendance/shift-info.php'),
    headers: {'Authorization': 'Bearer $token'},
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    if (data['success']) {
      return data['data'];
    }
  }
  throw Exception('Failed to get shift info');
}
```

**Check-In with Validation:**
```dart
Future<Map<String, dynamic>> checkIn(String token, double lat, double lng) async {
  try {
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

    final data = jsonDecode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      // Success
      return data['data'];
    } else {
      // Handle errors
      switch (data['error_code']) {
        case 'TOO_EARLY':
          throw Exception('Belum waktunya check-in');
        case 'TOO_LATE':
          throw Exception('Waktu check-in sudah lewat, hubungi HRD');
        case 'ALREADY_CHECKED_IN':
          throw Exception('Anda sudah check-in hari ini');
        case 'LOCATION_ERROR':
          throw Exception('Lokasi di luar radius kantor');
        default:
          throw Exception(data['message']);
      }
    }
  } catch (e) {
    rethrow;
  }
}
```

**Complete Mobile Flow:**
```dart
class AttendanceService {
  final String baseUrl = 'http://localhost/hrm/api/v1/attendance';
  
  // 1. Get shift info before showing check-in button
  Future<ShiftInfo> getShiftInfo(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/shift-info.php'),
      headers: {'Authorization': 'Bearer $token'},
    );
    
    final data = jsonDecode(response.body);
    return ShiftInfo.fromJson(data['data']);
  }
  
  // 2. Show shift info in UI
  Widget buildShiftCard(ShiftInfo shift) {
    return Card(
      child: Column(
        children: [
          Text('Shift: ${shift.shiftName}'),
          Text('Jam Kerja: ${shift.startTime} - ${shift.endTime}'),
          Text('Grace Period: ${shift.gracePeriod} menit'),
          if (shift.canCheckInNow)
            ElevatedButton(
              onPressed: () => checkIn(),
              child: Text('Check In'),
            )
          else
            Text('Check-in tersedia: ${shift.checkInWindow.earliest}'),
        ],
      ),
    );
  }
  
  // 3. Check-in with error handling
  Future<void> checkIn() async {
    try {
      final position = await getCurrentPosition();
      final result = await checkInAPI(
        token,
        position.latitude,
        position.longitude,
      );
      
      // Show success
      if (result['status'] == 'Terlambat') {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: Text('Check-in Berhasil'),
            content: Text('Anda terlambat ${result['late_minutes']} menit'),
          ),
        );
      } else {
        showSnackBar('Check-in berhasil! Selamat bekerja!');
      }
    } on Exception catch (e) {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('Error'),
          content: Text(e.toString()),
        ),
      );
    }
  }
}
```

---

## Setup & Testing

### 1. Import Database
```bash
mysql -u root -p hris_db < database/shifts_schema.sql
```

### 2. Assign Shift
Via admin panel: `/admin/shifts/assign.php`

### 3. Test Endpoints

**Test Shift Info:**
```bash
curl -X GET http://localhost/hrm/api/v1/attendance/shift-info.php \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Test Check-In (Valid Time):**
```bash
# During allowed window (06:00 - 11:00 for Shift Pagi)
curl -X POST http://localhost/hrm/api/v1/attendance/check-in.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"latitude": -6.200000, "longitude": 106.816666}'
```

**Test Check-In (Too Early):**
```bash
# Before 06:00
# Should return TOO_EARLY error
```

**Test Check-Out (Too Early):**
```bash
# Less than 4 hours after check-in
# Should return TOO_EARLY_CHECKOUT error
```

---

## Best Practices

### Mobile App
1. **Call `/shift-info.php` on app start** - Display shift info
2. **Show check-in window** - Let user know when they can check-in
3. **Handle all error codes** - Provide helpful messages
4. **Cache shift info** - Reduce API calls
5. **Show late warning** - Alert if approaching late time

### Error Handling
```dart
try {
  await checkIn();
} on Exception catch (e) {
  if (e.toString().contains('TOO_EARLY')) {
    // Show: "Belum waktunya, coba lagi nanti"
  } else if (e.toString().contains('TOO_LATE')) {
    // Show: "Hubungi HRD untuk koreksi kehadiran"
  } else if (e.toString().contains('LOCATION_ERROR')) {
    // Show: "Anda harus berada di kantor"
  }
}
```

---

## Notes

- **Timezone:** Asia/Jakarta (WIB)
- **Time Format:** 24-hour (HH:mm:ss)
- **Distance:** Meters (max 500m from office)
- **Minimum Work:** 4 hours
- **Grace Period:** Configurable per shift (default: 15 min)

---

## Support

**Version:** 2.0  
**Last Updated:** 2026-01-08  
**Contact:** Development Team
