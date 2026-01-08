# Complete Mobile API Documentation

## Overview
Dokumentasi lengkap API untuk aplikasi mobile HRIS Flutter.

**Base URL:** `http://your-server.com/hrm/api/v1/`  
**Authentication:** Bearer Token (kecuali login)  
**Version:** 3.0  
**Updated:** 2026-01-08

---

## Table of Contents

1. [Authentication](#authentication)
2. [Profile](#profile)
3. [Attendance](#attendance)
4. [Leave Management](#leave-management)
5. [Payroll](#payroll)
6. [Error Codes](#error-codes)

---

## Authentication

### 1. Login

**Endpoint:** `POST /auth/login.php`

**Request:**
```json
{
  "username": "john.doe",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 123,
      "username": "john.doe",
      "role": "Employee",
      "employee_id": 45
    }
  }
}
```

---

## Profile

### 1. Get Profile

**Endpoint:** `GET /profile/me.php`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "employee_code": "EMP001",
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@company.com",
    "phone": "081234567890",
    "department": "IT",
    "position": "Developer"
  }
}
```

### 2. Change Password

**Endpoint:** `POST /profile/change-password.php`

**Request:**
```json
{
  "current_password": "oldpass123",
  "new_password": "newpass456",
  "confirm_password": "newpass456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password berhasil diubah",
  "data": {
    "user_id": 123,
    "changed_at": "2026-01-08 17:30:00"
  }
}
```

**Error Codes:**
- `INVALID_CURRENT_PASSWORD` - Password saat ini salah
- `PASSWORD_TOO_SHORT` - Password minimal 6 karakter
- `SAME_PASSWORD` - Password baru sama dengan lama
- `PASSWORD_MISMATCH` - Konfirmasi tidak cocok

---

## Attendance

### 1. Get Shift Info

**Endpoint:** `GET /attendance/shift-info.php`

**Response:**
```json
{
  "success": true,
  "data": {
    "has_shift": true,
    "shift": {
      "name": "Shift Pagi",
      "code": "PAGI",
      "start_time_formatted": "08:00",
      "end_time_formatted": "17:00",
      "grace_period_minutes": 15,
      "shift_allowance_formatted": "Rp 0"
    },
    "current_status": {
      "status": "CAN_CHECK_IN_ON_TIME",
      "message": "Check-in sekarang masih tepat waktu",
      "can_check_in": true,
      "can_check_out": false,
      "is_late": false
    },
    "display": {
      "shift_badge": "PAGI",
      "shift_badge_color": "#0d6efd",
      "work_hours": "08:00 - 17:00"
    }
  }
}
```

### 2. Check-In

**Endpoint:** `POST /attendance/check-in.php`

**Validation:** Hanya selama jam shift (start_time - end_time)

**Request:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Response (On Time):**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "attendance_id": 124,
    "check_in_time": "08:05:00",
    "status": "Hadir",
    "shift": {
      "name": "Shift Pagi"
    },
    "message": "Selamat bekerja!"
  }
}
```

**Response (Late):**
```json
{
  "success": true,
  "data": {
    "status": "Terlambat",
    "late_minutes": 10,
    "message": "Anda terlambat 10 menit"
  }
}
```

**Error Codes:**
- `TOO_EARLY` - Belum waktunya (sebelum shift)
- `TOO_LATE` - Sudah lewat (setelah shift)
- `ALREADY_CHECKED_IN` - Sudah check-in hari ini
- `LOCATION_ERROR` - Di luar radius kantor

### 3. Check-Out

**Endpoint:** `POST /attendance/check-out.php`

**Validation:** Hanya setelah shift berakhir

**Request:**
```json
{
  "latitude": -6.200000,
  "longitude": 106.816666
}
```

**Response:**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "attendance_id": 124,
    "check_in_time": "08:05:00",
    "check_out_time": "17:10:00",
    "work_hours": 9.08
  }
}
```

**Error Codes:**
- `TOO_EARLY_CHECKOUT` - Belum waktunya (shift belum berakhir)
- `NOT_CHECKED_IN` - Belum check-in

### 4. Today's Attendance

**Endpoint:** `GET /attendance/today.php`

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
      "name": "Shift Pagi"
    },
    "work_duration": "9 jam 5 menit"
  }
}
```

### 5. Attendance History

**Endpoint:** `GET /attendance/history.php?month=12&year=2025&limit=30`

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
      "izin": 1
    },
    "records": [...]
  }
}
```

---

## Leave Management

### 1. Get Leave Balance

**Endpoint:** `GET /leave/balance.php`

**Response:**
```json
{
  "success": true,
  "data": {
    "balances": [
      {
        "leave_type": "Cuti Tahunan",
        "max_days": 12,
        "used_days": 5,
        "remaining_days": 7
      }
    ],
    "year": 2026
  }
}
```

### 2. Submit Leave Request

**Endpoint:** `POST /leave/request.php`

**Validation:** Tidak bisa submit jika ada request pending

**Request:**
```json
{
  "leave_type_id": 1,
  "start_date": "2026-01-15",
  "end_date": "2026-01-17",
  "reason": "Liburan keluarga"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Leave request submitted",
  "data": {
    "request_id": 45,
    "total_days": 3,
    "status": "Pending"
  }
}
```

**Error Codes:**
- `PENDING_REQUEST_EXISTS` - Masih ada request pending
- `INSUFFICIENT_BALANCE` - Saldo cuti tidak cukup

### 3. Get Leave Requests

**Endpoint:** `GET /leave/requests.php?status=Pending`

**Response:**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "id": 45,
        "leave_type": "Cuti Tahunan",
        "start_date": "2026-01-15",
        "end_date": "2026-01-17",
        "total_days": 3,
        "status": "Pending",
        "reason": "Liburan keluarga"
      }
    ]
  }
}
```

### 4. Submit Sick/Permission Request

**Endpoint:** `POST /leave/sick-permission/request.php`

**Validation:** Tidak bisa submit jika ada request pending

**Request (Multipart Form):**
```
leave_type: "Sakit"
start_date: "2026-01-10"
end_date: "2026-01-10"
reason: "Demam tinggi"
medical_certificate: [file] (optional untuk Sakit)
```

**Response:**
```json
{
  "success": true,
  "message": "Request submitted successfully",
  "data": {
    "request_id": 67,
    "leave_type": "Sakit",
    "total_days": 1,
    "status": "Pending"
  }
}
```

**Error Codes:**
- `PENDING_REQUEST_EXISTS` - Masih ada request pending
- `MEDICAL_CERTIFICATE_REQUIRED` - Surat dokter wajib untuk sakit >3 hari

### 5. Get Sick/Permission Requests

**Endpoint:** `GET /leave/sick-permission/requests.php?status=Pending`

**Response:**
```json
{
  "success": true,
  "data": {
    "requests": [
      {
        "id": 67,
        "leave_type": "Sakit",
        "start_date": "2026-01-10",
        "end_date": "2026-01-10",
        "total_days": 1,
        "status": "Pending",
        "has_certificate": true
      }
    ]
  }
}
```

---

## Payroll

### 1. Get Payroll Slips

**Endpoint:** `GET /payroll/slips.php?month=12&year=2025`

**Response:**
```json
{
  "success": true,
  "data": {
    "slips": [
      {
        "id": 123,
        "month": 12,
        "year": 2025,
        "basic_salary": 5000000,
        "allowances": 500000,
        "deductions": 250000,
        "net_salary": 5250000,
        "status": "Paid"
      }
    ]
  }
}
```

### 2. Get Slip Detail

**Endpoint:** `GET /payroll/slip-detail.php?slip_id=123`

**Response:**
```json
{
  "success": true,
  "data": {
    "slip_id": 123,
    "employee": {
      "name": "John Doe",
      "employee_code": "EMP001"
    },
    "period": "December 2025",
    "earnings": {
      "basic_salary": 5000000,
      "shift_allowance": 100000,
      "transport_allowance": 400000
    },
    "deductions": {
      "tax": 250000
    },
    "net_salary": 5250000
  }
}
```

---

## Error Codes

### General Errors

| Code | HTTP | Description |
|------|------|-------------|
| `UNAUTHORIZED` | 401 | Token invalid/expired |
| `METHOD_NOT_ALLOWED` | 405 | Wrong HTTP method |
| `VALIDATION_ERROR` | 400 | Missing/invalid fields |
| `SERVER_ERROR` | 500 | Database/server error |

### Authentication Errors

| Code | Description |
|------|-------------|
| `INVALID_CREDENTIALS` | Username/password salah |
| `INVALID_CURRENT_PASSWORD` | Password saat ini salah |
| `PASSWORD_TOO_SHORT` | Password minimal 6 karakter |
| `SAME_PASSWORD` | Password baru sama dengan lama |
| `PASSWORD_MISMATCH` | Konfirmasi password tidak cocok |

### Attendance Errors

| Code | Description |
|------|-------------|
| `TOO_EARLY` | Check-in sebelum shift dimulai |
| `TOO_LATE` | Check-in setelah shift berakhir |
| `TOO_EARLY_CHECKOUT` | Check-out sebelum shift berakhir |
| `ALREADY_CHECKED_IN` | Sudah check-in hari ini |
| `NOT_CHECKED_IN` | Belum check-in hari ini |
| `LOCATION_ERROR` | Lokasi di luar radius kantor |

### Leave Errors

| Code | Description |
|------|-------------|
| `PENDING_REQUEST_EXISTS` | Masih ada request pending |
| `INSUFFICIENT_BALANCE` | Saldo cuti tidak cukup |
| `MEDICAL_CERTIFICATE_REQUIRED` | Surat dokter wajib |

---

## Flutter Service Classes

### 1. Base API Service

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ApiService {
  final String baseUrl = 'http://your-server.com/hrm/api/v1';
  final String token;

  ApiService(this.token);

  Future<Map<String, dynamic>> get(String endpoint) async {
    final response = await http.get(
      Uri.parse('$baseUrl$endpoint'),
      headers: {'Authorization': 'Bearer $token'},
    );
    return _handleResponse(response);
  }

  Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode(data),
    );
    return _handleResponse(response);
  }

  Map<String, dynamic> _handleResponse(http.Response response) {
    final data = jsonDecode(response.body);
    
    if (response.statusCode == 200 && data['success']) {
      return data;
    } else {
      throw ApiException(
        data['message'] ?? 'Unknown error',
        data['error_code'] ?? 'UNKNOWN',
      );
    }
  }
}

class ApiException implements Exception {
  final String message;
  final String errorCode;

  ApiException(this.message, this.errorCode);

  @override
  String toString() => message;
}
```

### 2. Attendance Service

```dart
class AttendanceService extends ApiService {
  AttendanceService(String token) : super(token);

  Future<Map<String, dynamic>> getShiftInfo() async {
    final result = await get('/attendance/shift-info.php');
    return result['data'];
  }

  Future<Map<String, dynamic>> checkIn(double lat, double lng) async {
    final result = await post('/attendance/check-in.php', {
      'latitude': lat,
      'longitude': lng,
    });
    return result['data'];
  }

  Future<Map<String, dynamic>> checkOut(double lat, double lng) async {
    final result = await post('/attendance/check-out.php', {
      'latitude': lat,
      'longitude': lng,
    });
    return result['data'];
  }

  Future<Map<String, dynamic>?> getTodayAttendance() async {
    final result = await get('/attendance/today.php');
    return result['data'];
  }
}
```

### 3. Leave Service

```dart
class LeaveService extends ApiService {
  LeaveService(String token) : super(token);

  Future<List<dynamic>> getBalance() async {
    final result = await get('/leave/balance.php');
    return result['data']['balances'];
  }

  Future<void> submitRequest({
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    required String reason,
  }) async {
    await post('/leave/request.php', {
      'leave_type_id': leaveTypeId,
      'start_date': startDate,
      'end_date': endDate,
      'reason': reason,
    });
  }

  Future<List<dynamic>> getRequests({String? status}) async {
    final endpoint = status != null
        ? '/leave/requests.php?status=$status'
        : '/leave/requests.php';
    final result = await get(endpoint);
    return result['data']['requests'];
  }
}
```

### 4. Profile Service

```dart
class ProfileService extends ApiService {
  ProfileService(String token) : super(token);

  Future<Map<String, dynamic>> getProfile() async {
    final result = await get('/profile/me.php');
    return result['data'];
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    await post('/profile/change-password.php', {
      'current_password': currentPassword,
      'new_password': newPassword,
      'confirm_password': confirmPassword,
    });
  }
}
```

---

## Common Usage Patterns

### Error Handling

```dart
try {
  await attendanceService.checkIn(lat, lng);
  showSuccess('Check-in berhasil');
} on ApiException catch (e) {
  switch (e.errorCode) {
    case 'TOO_EARLY':
      showError('Belum waktunya check-in');
      break;
    case 'LOCATION_ERROR':
      showError('Lokasi di luar radius kantor');
      break;
    default:
      showError(e.message);
  }
} catch (e) {
  showError('Terjadi kesalahan');
}
```

### Loading State

```dart
bool isLoading = false;

Future<void> loadData() async {
  setState(() => isLoading = true);
  try {
    final data = await service.getData();
    // Process data
  } finally {
    setState(() => isLoading = false);
  }
}
```

---

## Testing

### Postman Collection

Import endpoints untuk testing:
- Authentication: Login
- Profile: Get Profile, Change Password
- Attendance: Shift Info, Check-In, Check-Out, Today, History
- Leave: Balance, Submit Request, Get Requests
- Payroll: Get Slips, Slip Detail

### Test Credentials

```
Username: test.employee
Password: password123
```

---

## Notes

- **Timezone:** Asia/Jakarta (WIB)
- **Date Format:** YYYY-MM-DD
- **Time Format:** HH:mm:ss (24-hour)
- **Distance:** Meters (max 500m from office)
- **Token Expiry:** 30 days

**Version:** 3.0  
**Last Updated:** 2026-01-08
