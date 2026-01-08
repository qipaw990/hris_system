# Attendance API Documentation for Flutter

## Overview
API lengkap untuk aplikasi mobile Flutter dengan sistem shift terintegrasi.

**Base URL:** `http://your-server.com/hrm/api/v1/attendance/`  
**Authentication:** Bearer Token Required  
**Updated:** 2026-01-08

---

## Table of Contents
1. [Authentication](#authentication)
2. [Shift Info](#1-get-shift-info)
3. [Check-In](#2-check-in)
4. [Check-Out](#3-check-out)
5. [Today's Attendance](#4-todays-attendance)
6. [Attendance History](#5-attendance-history)
7. [Flutter Implementation](#flutter-implementation)

---

## Authentication

Semua endpoint memerlukan Bearer token:
```dart
headers: {
  'Authorization': 'Bearer $token',
  'Content-Type': 'application/json',
}
```

---

## 1. Get Shift Info

**Endpoint:** `GET /shift-info.php`

**Purpose:** Ambil informasi shift karyawan untuk ditampilkan di mobile app.

**Response:**
```json
{
  "success": true,
  "message": "Shift info retrieved",
  "data": {
    "has_shift": true,
    "employee": {
      "id": 123,
      "name": "John Doe"
    },
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "code": "PAGI",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "start_time_formatted": "08:00",
      "end_time_formatted": "17:00",
      "grace_period_minutes": 15,
      "shift_allowance": 0,
      "shift_allowance_formatted": "Rp 0",
      "is_night_shift": false
    },
    "check_in_window": {
      "earliest": "08:00:00",
      "latest": "17:00:00",
      "earliest_formatted": "08:00",
      "latest_formatted": "17:00",
      "can_check_in_now": true,
      "description": "Selama jam kerja shift (08:00 - 17:00)"
    },
    "check_out_window": {
      "earliest": "17:00:00",
      "earliest_formatted": "17:00",
      "can_check_out_now": false,
      "description": "Setelah shift berakhir (17:00)"
    },
    "current_status": {
      "status": "CAN_CHECK_IN_ON_TIME",
      "message": "Check-in sekarang masih tepat waktu",
      "current_time": "08:10:00",
      "current_time_formatted": "08:10",
      "can_check_in": true,
      "can_check_out": false,
      "is_late": false
    },
    "rules": {
      "check_in_allowed": "Selama jam kerja shift",
      "check_out_allowed": "Setelah shift berakhir",
      "minimum_work_hours": 0,
      "late_if_after": "08:15"
    },
    "display": {
      "shift_badge": "PAGI",
      "shift_badge_color": "#0d6efd",
      "work_hours": "08:00 - 17:00",
      "summary": "Shift Pagi (08:00 - 17:00)"
    }
  }
}
```

**Status Types:**
- `TOO_EARLY` - Belum waktunya (sebelum shift dimulai)
- `CAN_CHECK_IN_EARLY` - Bisa check-in (tepat saat shift dimulai)
- `CAN_CHECK_IN_ON_TIME` - Tepat waktu (dalam grace period)
- `CAN_CHECK_IN_LATE` - Terlambat tapi masih bisa check-in
- `TOO_LATE` - Shift sudah berakhir

---

## 2. Check-In

**Endpoint:** `POST /check-in.php`

**Validation Rules:**
- ✅ Allowed: Selama jam kerja shift (start_time - end_time)
- ❌ Error: Sebelum shift dimulai atau setelah shift berakhir

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
    "check_in_time": "08:05:00",
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
      "name": "Kantor Pusat"
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
    "attendance_id": 125,
    "check_in_time": "08:25:00",
    "status": "Terlambat",
    "shift": {
      "id": 1,
      "name": "Shift Pagi"
    },
    "late_minutes": 10,
    "message": "Anda terlambat 10 menit"
  }
}
```

**Error Responses:**

**Too Early:**
```json
{
  "success": false,
  "message": "Belum waktunya check-in. Shift Anda dimulai pukul 08:00",
  "error_code": "TOO_EARLY"
}
```

**Too Late:**
```json
{
  "success": false,
  "message": "Waktu check-in sudah lewat. Shift berakhir pukul 17:00. Silakan hubungi HRD untuk koreksi kehadiran.",
  "error_code": "TOO_LATE"
}
```

**Already Checked In:**
```json
{
  "success": false,
  "message": "Already checked in today",
  "error_code": "ALREADY_CHECKED_IN"
}
```

**Location Error:**
```json
{
  "success": false,
  "message": "Anda berada di luar radius kantor (500m)",
  "error_code": "LOCATION_ERROR"
}
```

---

## 3. Check-Out

**Endpoint:** `POST /check-out.php`

**Validation Rules:**
- ✅ Allowed: Setelah shift berakhir
- ❌ Error: Sebelum shift berakhir
- ℹ️ No minimum work hours requirement

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
    "attendance_id": 124,
    "check_in_time": "08:05:00",
    "check_out_time": "17:10:00",
    "work_hours": 9.08,
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
  "message": "Belum waktunya check-out. Shift berakhir pukul 17:00. Anda baru bekerja 5.5 jam.",
  "error_code": "TOO_EARLY_CHECKOUT"
}
```

---

## 4. Today's Attendance

**Endpoint:** `GET /today.php`

**Response:**
```json
{
  "success": true,
  "data": {
    "attendance_id": 124,
    "date": "2026-01-08",
    "check_in": "08:05:00",
    "check_out": "17:10:00",
    "status": "Hadir",
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00:00",
      "end_time": "17:00:00"
    },
    "work_duration": "9 jam 5 menit",
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

## 5. Attendance History

**Endpoint:** `GET /history.php?month=12&year=2025&limit=30`

**Response:**
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
        "id": 124,
        "date": "2025-12-20",
        "check_in": "08:05:00",
        "check_out": "17:10:00",
        "status": "Hadir",
        "shift": {
          "name": "Shift Pagi"
        },
        "work_duration": "9 jam 5 menit"
      }
    ]
  }
}
```

---

## Flutter Implementation

### 1. API Service Class

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class AttendanceService {
  final String baseUrl = 'http://your-server.com/hrm/api/v1/attendance';
  final String token;

  AttendanceService(this.token);

  // Get Shift Info
  Future<Map<String, dynamic>> getShiftInfo() async {
    final response = await http.get(
      Uri.parse('$baseUrl/shift-info.php'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success']) {
        return data['data'];
      }
      throw Exception(data['message']);
    }
    throw Exception('Failed to get shift info');
  }

  // Check-In
  Future<Map<String, dynamic>> checkIn(double lat, double lng) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/check-in.php'),
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
        return data['data'];
      } else {
        throw AttendanceException(
          data['message'],
          data['error_code'],
        );
      }
    } catch (e) {
      rethrow;
    }
  }

  // Check-Out
  Future<Map<String, dynamic>> checkOut(double lat, double lng) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/check-out.php'),
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
        return data['data'];
      } else {
        throw AttendanceException(
          data['message'],
          data['error_code'],
        );
      }
    } catch (e) {
      rethrow;
    }
  }

  // Get Today's Attendance
  Future<Map<String, dynamic>?> getTodayAttendance() async {
    final response = await http.get(
      Uri.parse('$baseUrl/today.php'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    }
    throw Exception('Failed to get attendance');
  }

  // Get History
  Future<Map<String, dynamic>> getHistory({
    int? month,
    int? year,
    int limit = 30,
  }) async {
    final params = {
      if (month != null) 'month': month.toString(),
      if (year != null) 'year': year.toString(),
      'limit': limit.toString(),
    };

    final uri = Uri.parse('$baseUrl/history.php').replace(
      queryParameters: params,
    );

    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success']) {
        return data['data'];
      }
    }
    throw Exception('Failed to get history');
  }
}

// Custom Exception
class AttendanceException implements Exception {
  final String message;
  final String errorCode;

  AttendanceException(this.message, this.errorCode);

  @override
  String toString() => message;
}
```

### 2. Shift Info Widget

```dart
class ShiftInfoCard extends StatelessWidget {
  final Map<String, dynamic> shiftInfo;

  const ShiftInfoCard({required this.shiftInfo});

  @override
  Widget build(BuildContext context) {
    final shift = shiftInfo['shift'];
    final display = shiftInfo['display'];
    final currentStatus = shiftInfo['current_status'];

    return Card(
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Shift Badge
            Container(
              padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Color(int.parse(
                  display['shift_badge_color'].substring(1),
                  radix: 16,
                ) + 0xFF000000),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                display['shift_badge'],
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            SizedBox(height: 12),

            // Shift Name
            Text(
              shift['name'],
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 4),

            // Work Hours
            Row(
              children: [
                Icon(Icons.access_time, size: 16),
                SizedBox(width: 4),
                Text(display['work_hours']),
              ],
            ),
            SizedBox(height: 8),

            // Grace Period
            Text(
              'Grace Period: ${shift['grace_period_minutes']} menit',
              style: TextStyle(color: Colors.grey[600]),
            ),

            // Shift Allowance (if any)
            if (shift['shift_allowance'] > 0) ...[
              SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.attach_money, size: 16),
                  SizedBox(width: 4),
                  Text(shift['shift_allowance_formatted']),
                ],
              ),
            ],

            SizedBox(height: 16),
            Divider(),
            SizedBox(height: 8),

            // Current Status
            Container(
              padding: EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: currentStatus['is_late']
                    ? Colors.orange[50]
                    : Colors.green[50],
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Icon(
                    currentStatus['is_late']
                        ? Icons.warning_amber
                        : Icons.check_circle,
                    color: currentStatus['is_late']
                        ? Colors.orange
                        : Colors.green,
                  ),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      currentStatus['message'],
                      style: TextStyle(
                        color: currentStatus['is_late']
                            ? Colors.orange[900]
                            : Colors.green[900],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

### 3. Check-In Screen

```dart
class CheckInScreen extends StatefulWidget {
  @override
  _CheckInScreenState createState() => _CheckInScreenState();
}

class _CheckInScreenState extends State<CheckInScreen> {
  final AttendanceService _service = AttendanceService(token);
  Map<String, dynamic>? shiftInfo;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadShiftInfo();
  }

  Future<void> _loadShiftInfo() async {
    try {
      final info = await _service.getShiftInfo();
      setState(() {
        shiftInfo = info;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      _showError(e.toString());
    }
  }

  Future<void> _handleCheckIn() async {
    try {
      // Get current location
      final position = await _getCurrentPosition();

      // Show loading
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => Center(child: CircularProgressIndicator()),
      );

      // Check-in
      final result = await _service.checkIn(
        position.latitude,
        position.longitude,
      );

      // Close loading
      Navigator.pop(context);

      // Show result
      _showSuccess(result);
    } on AttendanceException catch (e) {
      Navigator.pop(context);
      _handleError(e);
    } catch (e) {
      Navigator.pop(context);
      _showError(e.toString());
    }
  }

  void _handleError(AttendanceException e) {
    String title = 'Error';
    String message = e.message;

    switch (e.errorCode) {
      case 'TOO_EARLY':
        title = 'Belum Waktunya';
        break;
      case 'TOO_LATE':
        title = 'Sudah Terlambat';
        break;
      case 'ALREADY_CHECKED_IN':
        title = 'Sudah Check-in';
        break;
      case 'LOCATION_ERROR':
        title = 'Lokasi Error';
        break;
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (shiftInfo == null) {
      return Scaffold(
        body: Center(child: Text('Failed to load shift info')),
      );
    }

    final canCheckIn = shiftInfo!['current_status']['can_check_in'];

    return Scaffold(
      appBar: AppBar(title: Text('Check-In')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            ShiftInfoCard(shiftInfo: shiftInfo!),
            SizedBox(height: 24),

            // Check-in Button
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: canCheckIn ? _handleCheckIn : null,
                child: Text(
                  canCheckIn ? 'Check In Sekarang' : 'Belum Waktunya',
                  style: TextStyle(fontSize: 16),
                ),
              ),
            ),

            if (!canCheckIn) ...[
              SizedBox(height: 12),
              Text(
                'Check-in tersedia: ${shiftInfo!['check_in_window']['earliest_formatted']}',
                style: TextStyle(color: Colors.grey),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
```

### 4. Error Handling

```dart
void handleAttendanceError(dynamic error, BuildContext context) {
  String message = 'Terjadi kesalahan';

  if (error is AttendanceException) {
    switch (error.errorCode) {
      case 'TOO_EARLY':
        message = 'Belum waktunya check-in. Tunggu sampai shift dimulai.';
        break;
      case 'TOO_LATE':
        message = 'Waktu check-in sudah lewat. Hubungi HRD untuk koreksi.';
        break;
      case 'TOO_EARLY_CHECKOUT':
        message = 'Belum waktunya check-out. Tunggu sampai shift berakhir.';
        break;
      case 'ALREADY_CHECKED_IN':
        message = 'Anda sudah check-in hari ini.';
        break;
      case 'NOT_CHECKED_IN':
        message = 'Anda belum check-in hari ini.';
        break;
      case 'LOCATION_ERROR':
        message = 'Lokasi Anda di luar radius kantor.';
        break;
      default:
        message = error.message;
    }
  }

  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: Text(message),
      backgroundColor: Colors.red,
    ),
  );
}
```

---

## Validation Summary

### Check-In Rules
- ✅ **Allowed:** Selama jam kerja shift (start_time - end_time)
- ✅ **Late Detection:** Setelah start_time + grace_period
- ❌ **Rejected:** Sebelum shift dimulai atau setelah shift berakhir

### Check-Out Rules
- ✅ **Allowed:** Setelah shift berakhir (end_time)
- ❌ **Rejected:** Sebelum shift berakhir
- ℹ️ **No minimum work hours**

### Example Timeline (Shift Pagi 08:00-17:00, Grace 15 min)

```
07:30 → ❌ Check-in: "Shift dimulai pukul 08:00"
08:00 → ✅ Check-in: "Selamat bekerja!" (On time)
08:10 → ✅ Check-in: "Selamat bekerja!" (On time, dalam grace)
08:20 → ✅ Check-in: "Terlambat 5 menit" (Late)
16:00 → ❌ Check-out: "Shift berakhir pukul 17:00"
17:00 → ✅ Check-out: "Terima kasih!" (Allowed)
17:30 → ✅ Check-out: "Terima kasih!" (Lembur)
18:00 → ❌ Check-in: "Shift berakhir pukul 17:00"
```

---

## Notes

- Timezone: Asia/Jakarta (WIB)
- Time Format: 24-hour (HH:mm:ss)
- Distance: Meters (max 500m from office)
- All times are server time

**Version:** 3.0  
**Last Updated:** 2026-01-08
