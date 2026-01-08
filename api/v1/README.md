# HRIS Mobile API

REST API untuk HRIS Mobile Application dengan fitur attendance geofencing, leave management, dan profile management.

## 🚀 Quick Start

### Base URL
```
http://localhost/hrm/api/v1/
```

### Authentication
Semua endpoint (kecuali login) memerlukan Bearer token di header:
```
Authorization: Bearer {your_token}
```

## 📋 Available Endpoints

### Authentication
- `POST /auth/login.php` - Login dan dapatkan token

### Attendance (Geofencing)
- `GET /attendance/offices.php` - Get office locations
- `POST /attendance/check-in.php` - Check-in dengan GPS
- `POST /attendance/check-out.php` - Check-out dengan GPS
- `GET /attendance/history.php` - Attendance history
- `GET /attendance/today.php` - Today's attendance status

### Profile
- `GET /profile/index.php` - Get user profile

## 🔐 Security

- **Token Expiry:** 7 days
- **GPS Validation:** Check-in/out hanya valid dalam radius kantor
- **HTTPS:** Wajib di production
- **Secret Key:** Ganti di `auth.php`

## 📖 Full Documentation

Lihat [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) untuk dokumentasi lengkap dengan:
- Request/Response examples
- Error codes
- Postman testing guide
- Flutter integration examples

## 🧪 Testing

### Postman
1. Import collection dari dokumentasi
2. Login untuk mendapatkan token
3. Set token di Authorization header
4. Test endpoints

### cURL Example
```bash
# Login
curl -X POST http://localhost/hrm/api/v1/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Check-in
curl -X POST http://localhost/hrm/api/v1/attendance/check-in.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"latitude":-6.2088,"longitude":106.8456}'
```

## 📱 Mobile Integration

### Flutter Example
```dart
// Login
final response = await http.post(
  Uri.parse('$baseUrl/auth/login.php'),
  body: jsonEncode({'username': username, 'password': password}),
);

// Check-in with GPS
Position position = await Geolocator.getCurrentPosition();
await http.post(
  Uri.parse('$baseUrl/attendance/check-in.php'),
  headers: {'Authorization': 'Bearer $token'},
  body: jsonEncode({
    'latitude': position.latitude,
    'longitude': position.longitude,
  }),
);
```

## 🛠️ Development

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite

### Setup
1. Ensure database is configured
2. Update secret key in `api/v1/auth.php`
3. Configure CORS if needed in `api/v1/config.php`
4. Test endpoints

## 📞 Support

Untuk pertanyaan atau issues, hubungi system administrator.

---

**Version:** 1.0.0  
**Last Updated:** January 2026
