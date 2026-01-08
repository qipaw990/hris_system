# Dashboard API Documentation

## Endpoint
`GET /api/v1/dashboard.php`

## Description
Returns comprehensive dashboard data for mobile home screen including employee info, contract details, today's attendance, and quick statistics.

## Authentication
Required. Bearer token in Authorization header.

## Request
```http
GET /api/v1/dashboard.php
Authorization: Bearer {token}
```

## Response

### Success (200)
```json
{
  "success": true,
  "message": "Dashboard data retrieved successfully",
  "data": {
    "employee": {
      "id": 1,
      "employee_code": "EMP001",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "081234567890",
      "department": "Information Technology",
      "position": "Software Engineer",
      "photo_url": "/hrm/assets/uploads/photo.jpg"
    },
    "contract": {
      "contract_type": "PKWT",
      "start_date": "2025-01-01",
      "end_date": "2026-12-31",
      "days_remaining": 358,
      "status": "Active",
      "is_expiring_soon": false,
      "is_expired": false,
      "warning_message": null
    },
    "today_attendance": {
      "status": "Hadir",
      "check_in": "08:30:00",
      "check_out": "17:00:00",
      "notes": null
    },
    "quick_stats": {
      "pending_leave_requests": 1,
      "this_month_present": 15,
      "this_month_late": 2,
      "this_month_absent": 0
    }
  }
}
```

### Contract Expiring Soon
```json
{
  "contract": {
    "contract_type": "PKWT",
    "start_date": "2025-01-01",
    "end_date": "2026-02-15",
    "days_remaining": 25,
    "status": "Active",
    "is_expiring_soon": true,
    "is_expired": false,
    "warning_message": "Kontrak akan berakhir dalam 25 hari"
  }
}
```

### Contract Expired
```json
{
  "contract": {
    "contract_type": "PKWT",
    "start_date": "2024-01-01",
    "end_date": "2025-12-31",
    "days_remaining": -7,
    "status": "Active",
    "is_expiring_soon": false,
    "is_expired": true,
    "warning_message": "Kontrak Anda sudah berakhir"
  }
}
```

### No Active Contract
```json
{
  "contract": null
}
```

### No Attendance Today
```json
{
  "today_attendance": null
}
```

## Response Fields

### Employee Object
| Field | Type | Description |
|-------|------|-------------|
| id | integer | Employee ID |
| employee_code | string | Employee code |
| name | string | Full name |
| email | string | Email address |
| phone | string | Phone number |
| department | string | Department name |
| position | string | Position name |
| photo_url | string | Photo URL (null if no photo) |

### Contract Object
| Field | Type | Description |
|-------|------|-------------|
| contract_type | string | PKWT, PKWTT, etc. |
| start_date | string | Contract start date (YYYY-MM-DD) |
| end_date | string | Contract end date (YYYY-MM-DD) |
| days_remaining | integer | Days until expiration (negative if expired) |
| status | string | Contract status |
| is_expiring_soon | boolean | True if expires within 30 days |
| is_expired | boolean | True if already expired |
| warning_message | string | Warning text (null if no warning) |

### Today Attendance Object
| Field | Type | Description |
|-------|------|-------------|
| status | string | Hadir, Terlambat, Alpha, etc. |
| check_in | string | Check-in time (HH:MM:SS) |
| check_out | string | Check-out time (HH:MM:SS) |
| notes | string | Notes |

### Quick Stats Object
| Field | Type | Description |
|-------|------|-------------|
| pending_leave_requests | integer | Number of pending leave requests |
| this_month_present | integer | Present days this month |
| this_month_late | integer | Late days this month |
| this_month_absent | integer | Absent days this month |

## UI Display Examples

### Contract Warning Card
```dart
if (contract != null && contract.warningMessage != null) {
  return Card(
    color: contract.isExpired ? Colors.red : Colors.orange,
    child: ListTile(
      leading: Icon(Icons.warning),
      title: Text('Kontrak'),
      subtitle: Text(contract.warningMessage),
      trailing: Text('${contract.daysRemaining} hari'),
    ),
  );
}
```

### Contract Info Display
```dart
Text('Kontrak: ${contract.contractType}')
Text('Berakhir: ${formatDate(contract.endDate)}')
Text('Sisa: ${contract.daysRemaining} hari')
```

## Error Responses

### 404 - Employee Not Found
```json
{
  "success": false,
  "message": "Employee data not found",
  "error_code": "EMPLOYEE_NOT_FOUND"
}
```

### 500 - Server Error
```json
{
  "success": false,
  "message": "Database error: ...",
  "error_code": "SERVER_ERROR"
}
```

## Notes
- Contract info will be `null` if employee has no active contract
- Today attendance will be `null` if employee hasn't checked in yet
- `days_remaining` can be negative if contract is expired
- `is_expiring_soon` is true when days_remaining <= 30 and > 0
- Warning message only appears when contract is expiring soon or expired
