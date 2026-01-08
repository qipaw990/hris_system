# Timezone & System Configuration API

## ✅ Timezone Configuration

Semua API endpoint sekarang menggunakan **timezone Jakarta (Asia/Jakarta / WIB - UTC+7)**.

### Setting Timezone
Timezone sudah di-set secara global di `api/v1/config.php`:
```php
date_default_timezone_set('Asia/Jakarta');
```

Semua waktu yang dikembalikan oleh API menggunakan timezone Jakarta.

---

## 📍 New Endpoints

### 1. Get Server Time
**Endpoint:** `GET /api/v1/time.php`  
**Authentication:** Not required  
**Description:** Get current server time in Jakarta timezone

**Response:**
```json
{
  "success": true,
  "message": "Server time retrieved successfully",
  "data": {
    "timezone": "Asia/Jakarta",
    "timezone_offset": "+07:00",
    "current_time": "20:59:18",
    "current_date": "2026-01-07",
    "current_datetime": "2026-01-07 20:59:18",
    "timestamp": 1736257158,
    "day_of_week": "Tuesday",
    "formatted": {
      "time_12h": "08:59:18 PM",
      "date_indo": "07/01/2026",
      "datetime_indo": "07/01/2026 20:59:18",
      "full": "Tuesday, 07 January 2026 20:59:18"
    }
  }
}
```

**Use Case:**
- Sync mobile app time with server
- Display server time to users
- Validate time-based operations

---

### 2. Get System Configuration
**Endpoint:** `GET /api/v1/system/config.php`  
**Authentication:** Required (Bearer Token)  
**Description:** Get system configuration for mobile app

**Response:**
```json
{
  "success": true,
  "message": "System configuration retrieved successfully",
  "data": {
    "timezone": {
      "timezone": "Asia/Jakarta",
      "timezone_name": "Western Indonesia Time",
      "timezone_abbr": "WIB",
      "offset": "+07:00",
      "current_time": "20:59:18",
      "current_date": "2026-01-07"
    },
    "work_hours": {
      "work_start_time": "08:00:00",
      "work_end_time": "17:00:00",
      "late_tolerance_minutes": 15,
      "break_duration_minutes": 60,
      "working_days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "weekend_days": ["Saturday", "Sunday"]
    },
    "app": {
      "app_name": "HRIS Mobile",
      "app_version": "1.0.0",
      "api_version": "v1",
      "min_app_version": "1.0.0",
      "force_update": false
    },
    "attendance": {
      "geofencing_enabled": true,
      "default_radius_meters": 100,
      "allow_outside_radius": false,
      "require_photo": false,
      "auto_checkout_enabled": false,
      "auto_checkout_time": "18:00:00"
    }
  }
}
```

**Use Case:**
- Get work hours configuration
- Check timezone settings
- Get attendance rules
- App version checking

---

## 🕐 Time Format Standards

All API endpoints return time in the following formats:

### Date Format
- **ISO 8601:** `2026-01-07`
- **Indonesian:** `07/01/2026`

### Time Format
- **24-hour:** `20:59:18`
- **12-hour:** `08:59:18 PM`

### DateTime Format
- **ISO 8601:** `2026-01-07T20:59:18+07:00`
- **Simple:** `2026-01-07 20:59:18`

### Timezone
- **Name:** Asia/Jakarta
- **Abbreviation:** WIB (Western Indonesia Time)
- **Offset:** UTC+7 / +07:00

---

## 📱 Flutter Integration

### Get Server Time
```dart
Future<void> getServerTime() async {
  final response = await http.get(
    Uri.parse('$baseUrl/time.php'),
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    print('Server time: ${data['data']['current_datetime']}');
    print('Timezone: ${data['data']['timezone']}');
  }
}
```

### Get System Config
```dart
Future<Map<String, dynamic>> getSystemConfig() async {
  final response = await http.get(
    Uri.parse('$baseUrl/system/config.php'),
    headers: {'Authorization': 'Bearer $token'},
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return data['data'];
  }
  throw Exception('Failed to load config');
}
```

### Sync Time with Server
```dart
Future<void> syncTimeWithServer() async {
  final response = await http.get(
    Uri.parse('$baseUrl/time.php'),
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    final serverTime = DateTime.parse(data['data']['current_datetime']);
    final localTime = DateTime.now();
    final timeDiff = serverTime.difference(localTime);
    
    print('Time difference: ${timeDiff.inSeconds} seconds');
    
    // Warn if difference > 5 minutes
    if (timeDiff.inMinutes.abs() > 5) {
      print('⚠️ Warning: Device time is not synced with server!');
    }
  }
}
```

---

## ⚙️ Configuration Notes

### Work Hours
- **Start:** 08:00:00 (8 AM)
- **End:** 17:00:00 (5 PM)
- **Late Tolerance:** 15 minutes
- **Break Duration:** 60 minutes

### Attendance Rules
- **Geofencing:** Enabled
- **Default Radius:** 100 meters
- **Allow Outside Radius:** No
- **Photo Required:** No
- **Auto Checkout:** Disabled

### Working Days
- **Weekdays:** Monday - Friday
- **Weekend:** Saturday - Sunday

---

## 🔄 Time Synchronization

**Important:** Mobile app should sync with server time on:
1. App launch
2. Before check-in/out
3. Every 30 minutes (background)

**Why?**
- Ensure accurate attendance time
- Prevent time manipulation
- Consistent time across devices

---

**Files:**
- ✅ `api/v1/config.php` - Timezone set to Asia/Jakarta
- ✅ `api/v1/time.php` - Get server time endpoint
- ✅ `api/v1/system/config.php` - System configuration endpoint
