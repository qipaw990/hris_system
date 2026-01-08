# Attendance Correction Request API Documentation

## Overview
API endpoints untuk fitur koreksi kehadiran (forgot attendance). Memungkinkan karyawan mengajukan request koreksi untuk hari-hari yang terlewat.

---

## Authentication
Semua endpoint memerlukan Bearer token authentication.

```http
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Submit Correction Request

Submit request koreksi kehadiran untuk tanggal yang terlewat.

**Endpoint:** `POST /api/v1/attendance/correction/request.php`

**Request Body:**
```json
{
  "request_date": "2026-01-06",
  "check_in_time": "08:30:00",
  "check_out_time": "17:00:00",
  "reason": "Lupa check-in karena meeting urgent di luar kantor pagi hari"
}
```

**Request Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| request_date | string (YYYY-MM-DD) | Yes | Tanggal yang ingin dikoreksi |
| check_in_time | string (HH:MM:SS) | No | Waktu check-in |
| check_out_time | string (HH:MM:SS) | No | Waktu check-out |
| reason | string | Yes | Alasan koreksi (min 20 karakter) |

**Validation Rules:**
- ✅ `request_date` harus tanggal masa lalu (max 7 hari)
- ✅ Tidak boleh weekend (Saturday/Sunday)
- ✅ `reason` minimal 20 karakter
- ✅ `check_in_time` harus sebelum `check_out_time`
- ✅ Tidak boleh duplicate request untuk tanggal yang sama
- ✅ Tidak boleh jika sudah ada attendance record

**Success Response (201):**
```json
{
  "success": true,
  "message": "Correction request submitted successfully",
  "data": {
    "request_id": 1,
    "request_date": "2026-01-06",
    "status": "Pending",
    "message": "Your request is pending admin approval"
  }
}
```

**Error Responses:**

**400 - Validation Error:**
```json
{
  "success": false,
  "message": "Reason must be at least 20 characters",
  "error_code": "VALIDATION_ERROR"
}
```

**400 - Date Too Old:**
```json
{
  "success": false,
  "message": "Can only request correction for last 7 days",
  "error_code": "VALIDATION_ERROR"
}
```

**400 - Weekend:**
```json
{
  "success": false,
  "message": "Cannot request correction for weekends",
  "error_code": "VALIDATION_ERROR"
}
```

**400 - Duplicate Record:**
```json
{
  "success": false,
  "message": "Attendance record already exists for this date",
  "error_code": "DUPLICATE_RECORD"
}
```

**400 - Duplicate Request:**
```json
{
  "success": false,
  "message": "You already have a pending request for this date",
  "error_code": "DUPLICATE_REQUEST"
}
```

---

### 2. Get Correction Requests

Mendapatkan daftar request koreksi milik user yang sedang login.

**Endpoint:** `GET /api/v1/attendance/correction/requests.php`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status: Pending, Approved, Rejected |
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Items per page (default: 20) |

**Example Request:**
```http
GET /api/v1/attendance/correction/requests.php?status=Pending&page=1&limit=10
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Correction requests retrieved successfully",
  "data": {
    "requests": [
      {
        "id": 1,
        "request_date": "2026-01-06",
        "check_in_time": "08:30:00",
        "check_out_time": "17:00:00",
        "reason": "Lupa check-in karena meeting urgent di luar kantor",
        "status": "Pending",
        "reviewed_by": null,
        "reviewed_at": null,
        "rejection_reason": null,
        "created_at": "2026-01-07 08:00:00"
      },
      {
        "id": 2,
        "request_date": "2026-01-05",
        "check_in_time": "09:00:00",
        "check_out_time": "17:30:00",
        "reason": "Lupa check-in karena langsung ke client meeting",
        "status": "Approved",
        "reviewed_by": "admin",
        "reviewed_at": "2026-01-06 10:30:00",
        "rejection_reason": null,
        "created_at": "2026-01-06 09:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_records": 2,
      "total_pages": 1,
      "per_page": 10
    }
  }
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Request ID |
| request_date | string | Tanggal yang dikoreksi |
| check_in_time | string | Waktu check-in |
| check_out_time | string | Waktu check-out |
| reason | string | Alasan koreksi |
| status | string | Pending, Approved, Rejected |
| reviewed_by | string | Username reviewer (null jika pending) |
| reviewed_at | string | Waktu review (null jika pending) |
| rejection_reason | string | Alasan reject (null jika approved/pending) |
| created_at | string | Waktu submit request |

---

## Status Flow

```
┌─────────┐
│ Pending │ ──────┐
└─────────┘       │
                  │
         ┌────────┴────────┐
         │                 │
         ▼                 ▼
    ┌──────────┐      ┌──────────┐
    │ Approved │      │ Rejected │
    └──────────┘      └──────────┘
         │
         ▼
    ┌──────────────────────┐
    │ Attendance Created/  │
    │ Updated (Alpha→Hadir)│
    └──────────────────────┘
```

---

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 400 | Input validation failed |
| DUPLICATE_RECORD | 400 | Attendance already exists |
| DUPLICATE_REQUEST | 400 | Pending request already exists |
| EMPLOYEE_NOT_FOUND | 404 | Employee data not found |
| SERVER_ERROR | 500 | Internal server error |

---

## Usage Examples

### Flutter/Dart Example

```dart
// Submit correction request
Future<void> submitCorrectionRequest({
  required String requestDate,
  required String checkInTime,
  required String checkOutTime,
  required String reason,
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/attendance/correction/request.php'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'request_date': requestDate,
      'check_in_time': checkInTime,
      'check_out_time': checkOutTime,
      'reason': reason,
    }),
  );

  final data = jsonDecode(response.body);
  
  if (data['success']) {
    print('Request submitted: ${data['data']['request_id']}');
  } else {
    throw Exception(data['message']);
  }
}

// Get correction requests
Future<List<CorrectionRequest>> getCorrectionRequests({
  String? status,
  int page = 1,
}) async {
  final queryParams = {
    if (status != null) 'status': status,
    'page': page.toString(),
    'limit': '20',
  };
  
  final uri = Uri.parse('$baseUrl/attendance/correction/requests.php')
      .replace(queryParameters: queryParams);
  
  final response = await http.get(
    uri,
    headers: {'Authorization': 'Bearer $token'},
  );

  final data = jsonDecode(response.body);
  
  if (data['success']) {
    return (data['data']['requests'] as List)
        .map((json) => CorrectionRequest.fromJson(json))
        .toList();
  } else {
    throw Exception(data['message']);
  }
}
```

### JavaScript/Axios Example

```javascript
// Submit correction request
async function submitCorrectionRequest(data) {
  try {
    const response = await axios.post(
      '/api/v1/attendance/correction/request.php',
      {
        request_date: data.requestDate,
        check_in_time: data.checkInTime,
        check_out_time: data.checkOutTime,
        reason: data.reason
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    
    return response.data;
  } catch (error) {
    console.error('Error:', error.response.data.message);
    throw error;
  }
}

// Get correction requests
async function getCorrectionRequests(status = null, page = 1) {
  try {
    const params = new URLSearchParams({
      page: page,
      limit: 20
    });
    
    if (status) {
      params.append('status', status);
    }
    
    const response = await axios.get(
      `/api/v1/attendance/correction/requests.php?${params}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );
    
    return response.data.data;
  } catch (error) {
    console.error('Error:', error.response.data.message);
    throw error;
  }
}
```

---

## Testing with cURL

### Submit Request
```bash
curl -X POST http://localhost/hrm/api/v1/attendance/correction/request.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "request_date": "2026-01-06",
    "check_in_time": "08:30:00",
    "check_out_time": "17:00:00",
    "reason": "Lupa check-in karena meeting urgent di luar kantor pagi hari"
  }'
```

### Get Requests
```bash
curl -X GET "http://localhost/hrm/api/v1/attendance/correction/requests.php?status=Pending&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Best Practices

### 1. Validation on Client Side
```dart
// Validate before submit
String? validateCorrectionRequest({
  required String requestDate,
  required String reason,
  String? checkInTime,
  String? checkOutTime,
}) {
  // Check date range
  final date = DateTime.parse(requestDate);
  final today = DateTime.now();
  final diff = today.difference(date).inDays;
  
  if (diff > 7) {
    return 'Hanya bisa request koreksi untuk 7 hari terakhir';
  }
  
  if (diff <= 0) {
    return 'Hanya bisa request untuk tanggal yang sudah lewat';
  }
  
  // Check weekend
  if (date.weekday >= 6) {
    return 'Tidak bisa request koreksi untuk weekend';
  }
  
  // Check reason length
  if (reason.length < 20) {
    return 'Alasan minimal 20 karakter';
  }
  
  // Check time order
  if (checkInTime != null && checkOutTime != null) {
    final checkIn = TimeOfDay.fromDateTime(DateTime.parse('2000-01-01 $checkInTime'));
    final checkOut = TimeOfDay.fromDateTime(DateTime.parse('2000-01-01 $checkOutTime'));
    
    if (checkIn.hour > checkOut.hour || 
        (checkIn.hour == checkOut.hour && checkIn.minute >= checkOut.minute)) {
      return 'Check-out harus setelah check-in';
    }
  }
  
  return null; // Valid
}
```

### 2. Handle Errors Gracefully
```dart
try {
  await submitCorrectionRequest(...);
  showSuccessMessage('Request berhasil disubmit');
} on DioError catch (e) {
  if (e.response?.statusCode == 400) {
    final errorCode = e.response?.data['error_code'];
    
    switch (errorCode) {
      case 'DUPLICATE_REQUEST':
        showErrorMessage('Anda sudah punya request pending untuk tanggal ini');
        break;
      case 'DUPLICATE_RECORD':
        showErrorMessage('Attendance sudah ada untuk tanggal ini');
        break;
      default:
        showErrorMessage(e.response?.data['message'] ?? 'Terjadi kesalahan');
    }
  }
}
```

### 3. Refresh After Status Change
```dart
// Listen to request status changes
void listenToRequestUpdates() {
  // Poll every 30 seconds for pending requests
  Timer.periodic(Duration(seconds: 30), (timer) async {
    final requests = await getCorrectionRequests(status: 'Pending');
    
    // Check if any request was approved/rejected
    for (var request in requests) {
      if (request.status != 'Pending') {
        showNotification('Request Anda telah di-${request.status}');
        timer.cancel();
      }
    }
  });
}
```

---

## Notes

- ⚠️ Request hanya bisa disubmit untuk max 7 hari ke belakang
- ⚠️ Tidak bisa request untuk weekend
- ⚠️ Satu tanggal hanya bisa punya 1 pending request
- ⚠️ Admin approval diperlukan sebelum attendance record dibuat/diupdate
- ✅ Jika attendance sudah ada dengan status "Alpha", akan di-update ke "Hadir"
- ✅ Jika belum ada attendance, akan dibuat record baru
