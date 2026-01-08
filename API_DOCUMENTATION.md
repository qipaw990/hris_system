# HRIS Mobile API - Complete Documentation

## 📱 Overview

REST API untuk HRIS Mobile Application dengan 19 endpoints lengkap.

**Base URL:** `http://localhost/hrm/api/v1/`  
**Authentication:** Bearer Token  
**Response Format:** JSON

---

## 🔐 Authentication

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Token Info
- **Expiry:** 7 days
- **Format:** Base64 encoded payload + HMAC signature
- **Storage:** Secure storage (Keychain/Keystore)

---

## 📚 API Endpoints (19 Total)

### **1. Authentication (3 endpoints)**

#### 1.1 Login
```
POST /auth/login.php
```

**Request:**
```json
{
  "username": "employee1",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "employee1",
      "email": "employee@example.com",
      "role": "Employee",
      "employee": {
        "id": 5,
        "employee_code": "EMP001",
        "first_name": "John",
        "last_name": "Doe",
        "full_name": "John Doe",
        "photo": "/hrm/uploads/employees/photo.jpg"
      }
    }
  }
}
```

#### 1.2 Get Current User
```
GET /auth/me.php
```

**Response:**
```json
{
  "success": true,
  "message": "User info retrieved successfully",
  "data": {
    "id": 1,
    "username": "employee1",
    "email": "employee@example.com",
    "role": "Employee",
    "employee": {...}
  }
}
```

#### 1.3 Logout
```
POST /auth/logout.php
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": {
    "message": "Token has been invalidated"
  }
}
```

---

### **2. Attendance - Geofencing (5 endpoints)**

#### 2.1 Get Office Locations
```
GET /attendance/offices.php
```

**Response:**
```json
{
  "success": true,
  "message": "Office locations retrieved successfully",
  "data": {
    "offices": [
      {
        "id": 1,
        "location_name": "Kantor Pusat Jakarta",
        "address": "Jl. Sudirman No. 123",
        "latitude": "-6.2088",
        "longitude": "106.8456",
        "radius_meters": 100
      }
    ],
    "count": 1
  }
}
```

#### 2.2 Check-In
```
POST /attendance/check-in.php
```

**Request:**
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Check-in successful",
  "data": {
    "attendance_id": 123,
    "check_in_time": "08:30:00",
    "status": "On Time",
    "office": {
      "id": 1,
      "name": "Kantor Pusat Jakarta",
      "address": "Jl. Sudirman No. 123"
    },
    "distance": 45.5,
    "message": "Selamat bekerja!"
  }
}
```

**Response (Error - Outside Radius):**
```json
{
  "success": false,
  "message": "Anda berada 250 meter dari Kantor Pusat Jakarta. Jarak maksimal: 100 meter",
  "error_code": "LOCATION_ERROR"
}
```

#### 2.3 Check-Out
```
POST /attendance/check-out.php
```

**Request:**
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

**Response:**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "attendance_id": 123,
    "check_in_time": "08:30:00",
    "check_out_time": "17:00:00",
    "work_hours": 8.5,
    "office": {
      "id": 1,
      "name": "Kantor Pusat Jakarta"
    },
    "distance": 38.2,
    "message": "Terima kasih, sampai jumpa besok!"
  }
}
```

#### 2.4 Get Today's Attendance
```
GET /attendance/today.php
```

**Response:**
```json
{
  "success": true,
  "message": "Today's attendance retrieved successfully",
  "data": {
    "has_checked_in": true,
    "has_checked_out": false,
    "attendance": {
      "id": 123,
      "date": "2026-01-07",
      "check_in_time": "08:30:00",
      "check_out_time": null,
      "status": "On Time",
      "work_hours": null,
      "office": {
        "name": "Kantor Pusat Jakarta",
        "address": "Jl. Sudirman No. 123"
      },
      "distance": 45
    }
  }
}
```

#### 2.5 Get Attendance History
```
GET /attendance/history.php?month=2026-01&page=1&limit=30
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance history retrieved successfully",
  "data": {
    "records": [
      {
        "id": 123,
        "date": "2026-01-07",
        "check_in_time": "08:30:00",
        "check_out_time": "17:00:00",
        "status": "On Time",
        "office": {
          "name": "Kantor Pusat Jakarta",
          "address": "Jl. Sudirman No. 123"
        },
        "distance": 45
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_records": 20,
      "total_pages": 1,
      "per_page": 30
    },
    "month": "2026-01"
  }
}
```

---

### **3. Leave Management (3 endpoints)**

#### 3.1 Get Leave Balance
```
GET /leave/balance.php
```

**Response:**
```json
{
  "success": true,
  "message": "Leave balance retrieved successfully",
  "data": {
    "balances": [
      {
        "leave_type_id": 1,
        "leave_type": "Annual Leave",
        "total_days": 12,
        "used_days": 3,
        "remaining_days": 9
      },
      {
        "leave_type_id": 2,
        "leave_type": "Sick Leave",
        "total_days": 12,
        "used_days": 1,
        "remaining_days": 11
      }
    ],
    "year": "2026"
  }
}
```

#### 3.2 Get Leave Requests
```
GET /leave/requests.php?status=Pending
```

**Query Parameters:**
- `status` (optional): Pending, Approved, Rejected

**Response:**
```json
{
  "success": true,
  "message": "Leave requests retrieved successfully",
  "data": {
    "requests": [
      {
        "id": 1,
        "leave_type": "Annual Leave",
        "start_date": "2026-01-15",
        "end_date": "2026-01-17",
        "days_requested": 3,
        "reason": "Family vacation",
        "status": "Pending",
        "created_at": "2026-01-07 10:00:00"
      }
    ],
    "count": 1
  }
}
```

#### 3.3 Submit Leave Request
```
POST /leave/request.php
```

**Request:**
```json
{
  "leave_type_id": 1,
  "start_date": "2026-01-15",
  "end_date": "2026-01-17",
  "reason": "Family vacation"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Leave request submitted successfully",
  "data": {
    "request_id": 1,
    "days_requested": 3,
    "remaining_balance": 6,
    "status": "Pending",
    "message": "Your leave request is pending approval"
  }
}
```

---

### **4. Payroll (3 endpoints)**

#### 4.1 Get Payroll Slips
```
GET /payroll/slips.php?year=2026&page=1&limit=12
```

**Response:**
```json
{
  "success": true,
  "message": "Payroll slips retrieved successfully",
  "data": {
    "slips": [
      {
        "id": 1,
        "period": "January 2026",
        "period_start": "2026-01-01",
        "period_end": "2026-01-31",
        "basic_salary": 5000000,
        "total_earnings": 5500000,
        "total_deductions": 500000,
        "net_salary": 5000000,
        "status": "Paid",
        "payment_date": "2026-02-01"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_records": 12,
      "total_pages": 1,
      "per_page": 12
    },
    "year": 2026
  }
}
```

#### 4.2 Get Payroll Slip Detail
```
GET /payroll/slip.php?id=1
```

**Response:**
```json
{
  "success": true,
  "message": "Payroll slip detail retrieved successfully",
  "data": {
    "id": 1,
    "employee": {
      "name": "John Doe",
      "code": "EMP001",
      "department": "IT",
      "position": "Software Engineer"
    },
    "period": {
      "month": "January 2026",
      "start": "2026-01-01",
      "end": "2026-01-31"
    },
    "salary": {
      "basic_salary": 5000000,
      "total_earnings": 5500000,
      "total_deductions": 500000,
      "net_salary": 5000000
    },
    "earnings": [
      {
        "name": "Basic Salary",
        "amount": 5000000
      },
      {
        "name": "Transport Allowance",
        "amount": 500000
      }
    ],
    "deductions": [
      {
        "name": "Tax",
        "amount": 500000
      }
    ],
    "status": "Paid",
    "payment_date": "2026-02-01"
  }
}
```

#### 4.3 Get Latest Payroll
```
GET /payroll/latest.php
```

**Response:**
```json
{
  "success": true,
  "message": "Latest payroll slip retrieved successfully",
  "data": {
    "has_payroll": true,
    "slip": {
      "id": 1,
      "period": "January 2026",
      "net_salary": 5000000,
      "status": "Paid",
      "payment_date": "2026-02-01"
    }
  }
}
```

---

### **5. Contracts (3 endpoints)**

#### 5.1 Get Contracts List
```
GET /contracts/list.php?status=Active
```

**Response:**
```json
{
  "success": true,
  "message": "Contracts retrieved successfully",
  "data": {
    "contracts": [
      {
        "id": 1,
        "contract_number": "CNT-2026-001",
        "contract_type": "Permanent",
        "start_date": "2026-01-01",
        "end_date": null,
        "salary": 5000000,
        "status": "Active",
        "has_file": true
      }
    ],
    "count": 1
  }
}
```

#### 5.2 Get Contract Detail
```
GET /contracts/detail.php?id=1
```

**Response:**
```json
{
  "success": true,
  "message": "Contract detail retrieved successfully",
  "data": {
    "id": 1,
    "contract_number": "CNT-2026-001",
    "employee": {
      "name": "John Doe",
      "code": "EMP001",
      "department": "IT",
      "position": "Software Engineer"
    },
    "contract_type": {
      "name": "Permanent",
      "description": "Permanent employment contract"
    },
    "period": {
      "start_date": "2026-01-01",
      "end_date": null,
      "duration_months": null,
      "remaining_days": null
    },
    "salary": 5000000,
    "terms": "Standard employment terms...",
    "status": "Active",
    "file": {
      "has_file": true,
      "filename": "contract.pdf",
      "url": "/hrm/uploads/contracts/contract.pdf"
    }
  }
}
```

#### 5.3 Get Active Contract
```
GET /contracts/active.php
```

**Response:**
```json
{
  "success": true,
  "message": "Active contract retrieved successfully",
  "data": {
    "has_active_contract": true,
    "contract": {
      "id": 1,
      "contract_number": "CNT-2026-001",
      "contract_type": "Permanent",
      "start_date": "2026-01-01",
      "end_date": null,
      "remaining_days": null,
      "salary": 5000000,
      "status": "Active"
    }
  }
}
```

---

### **6. Profile (4 endpoints)**

#### 6.1 Get Basic Profile
```
GET /profile/index.php
```

**Response:**
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "username": "employee1",
    "email": "employee@example.com",
    "role": "Employee",
    "status": "Active",
    "employee": {
      "id": 5,
      "employee_code": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "full_name": "John Doe",
      "photo": "/hrm/uploads/employees/photo.jpg"
    }
  }
}
```

#### 6.2 Get Complete Profile
```
GET /profile/complete.php
```

**Response:**
```json
{
  "success": true,
  "message": "Complete profile retrieved successfully",
  "data": {
    "user": {...},
    "employee": {
      "id": 5,
      "employee_code": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "full_name": "John Doe",
      "date_of_birth": "1990-01-01",
      "gender": "Male",
      "phone": "081234567890",
      "address": "Jakarta",
      "hire_date": "2020-01-01",
      "photo": "/hrm/uploads/employees/photo.jpg",
      "department": {
        "id": 1,
        "name": "IT"
      },
      "position": {
        "id": 1,
        "name": "Software Engineer",
        "description": "Develops software"
      }
    },
    "contract": {
      "id": 1,
      "contract_number": "CNT-2026-001",
      "type": "Permanent",
      "start_date": "2026-01-01",
      "end_date": null,
      "remaining_days": null,
      "salary": 5000000,
      "status": "Active"
    },
    "attendance_summary": {
      "current_month": "January 2026",
      "total_days": 20,
      "on_time": 18,
      "late": 2,
      "incomplete": 0
    },
    "leave_balance": [
      {
        "type": "Annual Leave",
        "total": 12,
        "used": 3,
        "remaining": 9
      }
    ],
    "latest_payroll": {
      "id": 1,
      "period": "January 2026",
      "net_salary": 5000000,
      "status": "Paid",
      "payment_date": "2026-02-01"
    },
    "kpi_summary": {
      "average_score": 85.5,
      "total_evaluations": 4,
      "year": "2026"
    }
  }
}
```

#### 6.3 Update Profile
```
POST /profile/update.php
```

**Request:**
```json
{
  "email": "newemail@example.com",
  "phone": "081234567890",
  "address": "New address"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "username": "employee1",
    "email": "newemail@example.com",
    "role": "Employee",
    "employee": {
      "phone": "081234567890",
      "address": "New address"
    }
  }
}
```

#### 6.4 Change Password
```
POST /profile/password.php
```

**Request:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "confirm_password": "newpassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": {
    "message": "Your password has been updated"
  }
}
```

---

## ❌ Error Codes

| Code | Description | HTTP Status |
|------|-------------|-------------|
| `AUTH_REQUIRED` | Authorization header missing | 401 |
| `INVALID_TOKEN` | Invalid or expired token | 401 |
| `AUTH_FAILED` | Invalid credentials | 401 |
| `VALIDATION_ERROR` | Required field missing or invalid | 400 |
| `LOCATION_ERROR` | Outside allowed radius | 400 |
| `ALREADY_CHECKED_IN` | Already checked in today | 400 |
| `NOT_CHECKED_IN` | No check-in record found | 400 |
| `EMPLOYEE_NOT_FOUND` | Employee data not found | 404 |
| `USER_NOT_FOUND` | User not found | 404 |
| `NOT_FOUND` | Resource not found | 404 |
| `INSUFFICIENT_BALANCE` | Not enough leave balance | 400 |
| `SERVER_ERROR` | Internal server error | 500 |
| `METHOD_NOT_ALLOWED` | Wrong HTTP method | 405 |

---

## 🧪 Testing with Postman

### 1. Import Collection
Import file: `HRIS_Mobile_API.postman_collection.json`

### 2. Set Variables
- `base_url`: `http://localhost/hrm/api/v1`
- `token`: (will be set after login)

### 3. Login Flow
1. **Login** → Copy token from response
2. Set `token` variable
3. Test other endpoints

### 4. Test Geofencing
Use coordinates near office:
```json
{
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

---

## 📱 Mobile Integration

### Flutter Example

#### Setup
```dart
class ApiService {
  static const String baseUrl = 'http://your-domain/hrm/api/v1';
  String? _token;
  
  Future<void> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login.php'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'username': username,
        'password': password,
      }),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      if (data['success']) {
        _token = data['data']['token'];
        await storage.write(key: 'token', value: _token);
      }
    }
  }
  
  Future<Map<String, dynamic>> checkIn(double lat, double lng) async {
    final response = await http.post(
      Uri.parse('$baseUrl/attendance/check-in.php'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'latitude': lat,
        'longitude': lng,
      }),
    );
    
    return jsonDecode(response.body);
  }
}
```

#### Get GPS Location
```dart
Future<void> performCheckIn() async {
  Position position = await Geolocator.getCurrentPosition(
    desiredAccuracy: LocationAccuracy.high,
  );
  
  final result = await apiService.checkIn(
    position.latitude,
    position.longitude,
  );
  
  if (result['success']) {
    // Show success message
  } else {
    // Show error with distance info
  }
}
```

### React Native Example

```javascript
import axios from 'axios';
import * as Location from 'expo-location';

const API_BASE_URL = 'http://your-domain/hrm/api/v1';

// Login
async function login(username, password) {
  const response = await axios.post(`${API_BASE_URL}/auth/login.php`, {
    username,
    password,
  });
  
  if (response.data.success) {
    await AsyncStorage.setItem('token', response.data.data.token);
    return response.data.data.user;
  }
}

// Check-in with GPS
async function checkIn() {
  const { status } = await Location.requestForegroundPermissionsAsync();
  if (status !== 'granted') return;
  
  const location = await Location.getCurrentPositionAsync({
    accuracy: Location.Accuracy.High,
  });
  
  const token = await AsyncStorage.getItem('token');
  
  const response = await axios.post(
    `${API_BASE_URL}/attendance/check-in.php`,
    {
      latitude: location.coords.latitude,
      longitude: location.coords.longitude,
    },
    {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    }
  );
  
  return response.data;
}
```

---

## 🔒 Security Best Practices

1. **HTTPS Only** in production
2. **Token Storage**: Use secure storage (Keychain/Keystore)
3. **Token Refresh**: Implement token refresh before expiry
4. **GPS Accuracy**: Use high accuracy for check-in
5. **Error Handling**: Handle all error codes properly
6. **Rate Limiting**: Implement client-side rate limiting
7. **Sensitive Data**: Never log tokens or passwords

---

## 📊 API Summary

**Total Endpoints:** 19  
**Authentication:** Token-based (7 days)  
**Response Format:** JSON  
**Error Handling:** Comprehensive error codes  
**Geofencing:** Haversine formula validation  
**Pagination:** Supported on list endpoints  

---

**Version:** 1.0.0  
**Last Updated:** January 7, 2026  
**Status:** Production Ready ✅
