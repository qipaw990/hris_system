# Sick Leave & Permission Request API Documentation

## Overview
API endpoints for submitting and managing sick leave (Sakit) and permission (Izin) requests via mobile app.

---

## 1. Submit Request

### Endpoint
`POST /api/v1/leave/sick-permission/request.php`

### Description
Submit sick leave or permission request with optional file upload for medical certificate.

### Request (Multipart Form Data)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| leave_type | string | Yes | "Sakit" or "Izin" |
| start_date | string | Yes | Start date (YYYY-MM-DD) |
| end_date | string | Yes | End date (YYYY-MM-DD) |
| reason | string | Yes | Reason for leave |
| attachment | file | Conditional | Medical certificate (required for Sakit >3 days) |

### Validation Rules

**Sakit (Sick Leave):**
- Max 3 days without medical certificate
- >3 days requires attachment upload
- Can submit for today, yesterday, or day before yesterday
- Accepted formats: JPG, PNG, PDF (max 5MB)

**Izin (Permission):**
- Max 1 day per request
- Can submit for today or recent past
- No attachment required

### Example Request (cURL)

**Sakit without attachment (1-3 days):**
```bash
curl -X POST http://localhost/hrm/api/v1/leave/sick-permission/request.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "leave_type=Sakit" \
  -F "start_date=2026-01-07" \
  -F "end_date=2026-01-08" \
  -F "reason=Demam dan flu"
```

**Sakit with attachment (>3 days):**
```bash
curl -X POST http://localhost/hrm/api/v1/leave/sick-permission/request.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "leave_type=Sakit" \
  -F "start_date=2026-01-07" \
  -F "end_date=2026-01-11" \
  -F "reason=Demam tinggi dan perlu istirahat" \
  -F "attachment=@/path/to/surat_dokter.pdf"
```

**Izin:**
```bash
curl -X POST http://localhost/hrm/api/v1/leave/sick-permission/request.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "leave_type=Izin" \
  -F "start_date=2026-01-08" \
  -F "end_date=2026-01-08" \
  -F "reason=Keperluan keluarga"
```

### Success Response (201)
```json
{
  "success": true,
  "message": "Sakit request submitted successfully",
  "data": {
    "request_id": 15,
    "leave_type": "Sakit",
    "start_date": "2026-01-07",
    "end_date": "2026-01-11",
    "total_days": 5,
    "status": "Pending",
    "has_attachment": true,
    "message": "Your request is pending approval"
  }
}
```

### Error Responses

**400 - Missing attachment:**
```json
{
  "success": false,
  "message": "Medical certificate required for sick leave more than 3 days",
  "error_code": "ATTACHMENT_REQUIRED"
}
```

**400 - Izin too long:**
```json
{
  "success": false,
  "message": "Permission leave (Izin) max 1 day per request",
  "error_code": "VALIDATION_ERROR"
}
```

**400 - Duplicate request:**
```json
{
  "success": false,
  "message": "You already have a leave request for this date range",
  "error_code": "DUPLICATE_REQUEST"
}
```

**400 - File too large:**
```json
{
  "success": false,
  "message": "File size must be less than 5MB",
  "error_code": "FILE_TOO_LARGE"
}
```

---

## 2. Get Requests History

### Endpoint
`GET /api/v1/leave/sick-permission/requests.php`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| leave_type | string | No | Filter by "Sakit" or "Izin" |
| status | string | No | Filter by "Pending", "Approved", "Rejected" |
| page | integer | No | Page number (default: 1) |
| limit | integer | No | Items per page (default: 20) |

### Example Request
```http
GET /api/v1/leave/sick-permission/requests.php?leave_type=Sakit&status=Pending&page=1
Authorization: Bearer YOUR_TOKEN
```

### Success Response (200)
```json
{
  "success": true,
  "message": "Requests retrieved successfully",
  "data": {
    "requests": [
      {
        "id": 15,
        "leave_type": "Sakit",
        "start_date": "2026-01-07",
        "end_date": "2026-01-11",
        "total_days": 5,
        "reason": "Demam tinggi dan perlu istirahat",
        "status": "Pending",
        "has_attachment": true,
        "attachment_url": "/hrm/assets/uploads/leave_attachments/leave_12345.pdf",
        "created_at": "2026-01-07 08:00:00",
        "approved_at": null
      },
      {
        "id": 14,
        "leave_type": "Izin",
        "start_date": "2026-01-05",
        "end_date": "2026-01-05",
        "total_days": 1,
        "reason": "Keperluan keluarga",
        "status": "Approved",
        "has_attachment": false,
        "attachment_url": null,
        "created_at": "2026-01-04 15:30:00",
        "approved_at": "2026-01-05 09:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_records": 2,
      "total_pages": 1,
      "per_page": 20
    }
  }
}
```

---

## Flutter/Dart Example

### Submit Sakit Request with File
```dart
Future<void> submitSickLeave({
  required String startDate,
  required String endDate,
  required String reason,
  File? attachment,
}) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/leave/sick-permission/request.php'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.fields['leave_type'] = 'Sakit';
  request.fields['start_date'] = startDate;
  request.fields['end_date'] = endDate;
  request.fields['reason'] = reason;
  
  if (attachment != null) {
    request.files.add(await http.MultipartFile.fromPath(
      'attachment',
      attachment.path,
    ));
  }
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  var data = jsonDecode(responseData);
  
  if (data['success']) {
    print('Request submitted: ${data['data']['request_id']}');
  } else {
    throw Exception(data['message']);
  }
}
```

### Submit Izin Request
```dart
Future<void> submitPermission({
  required String date,
  required String reason,
}) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/leave/sick-permission/request.php'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.fields['leave_type'] = 'Izin';
  request.fields['start_date'] = date;
  request.fields['end_date'] = date;
  request.fields['reason'] = reason;
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  var data = jsonDecode(responseData);
  
  if (data['success']) {
    showSuccessMessage('Izin berhasil diajukan');
  }
}
```

### Get Requests
```dart
Future<List<LeaveRequest>> getSickPermissionRequests({
  String? leaveType,
  String? status,
}) async {
  final queryParams = {
    if (leaveType != null) 'leave_type': leaveType,
    if (status != null) 'status': status,
    'page': '1',
    'limit': '50',
  };
  
  final uri = Uri.parse('$baseUrl/leave/sick-permission/requests.php')
      .replace(queryParameters: queryParams);
  
  final response = await http.get(
    uri,
    headers: {'Authorization': 'Bearer $token'},
  );
  
  final data = jsonDecode(response.body);
  
  if (data['success']) {
    return (data['data']['requests'] as List)
        .map((json) => LeaveRequest.fromJson(json))
        .toList();
  } else {
    throw Exception(data['message']);
  }
}
```

---

## Setup Instructions

### 1. Import Database Schema
```sql
-- Run in phpMyAdmin
source database/sick_permission_schema.sql;
```

Or manually:
```sql
INSERT INTO leave_types (leave_name, max_days, description) VALUES
('Sakit', 365, 'Sick leave - requires medical certificate if >3 days'),
('Izin', 12, 'Permission leave - max 1 day per request');

ALTER TABLE leave_requests ADD COLUMN attachment VARCHAR(255) AFTER reason;
```

### 2. Create Upload Directory
```bash
mkdir -p assets/uploads/leave_attachments
chmod 755 assets/uploads/leave_attachments
```

### 3. Test API
Use Postman or cURL to test endpoints

---

## Notes

- ⚠️ Sakit >3 days MUST include medical certificate
- ⚠️ Izin limited to 1 day per request
- ⚠️ Can only submit for today, yesterday, or 2 days ago
- ⚠️ File upload max 5MB, formats: JPG, PNG, PDF
- ✅ Automatic overlap detection
- ✅ Admin approval required
- ✅ Attachment stored securely
