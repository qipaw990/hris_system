# Change Password API Documentation

## Overview
API untuk mengubah password user dari aplikasi mobile.

**Endpoint:** `POST /api/v1/profile/change-password.php`  
**Authentication:** Bearer Token Required  
**Method:** POST

---

## Request

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword456",
  "confirm_password": "newpassword456"
}
```

**Fields:**
- `current_password` (required) - Password saat ini
- `new_password` (required) - Password baru (minimal 6 karakter)
- `confirm_password` (required) - Konfirmasi password baru

---

## Response

### Success Response (200)

```json
{
  "success": true,
  "message": "Password berhasil diubah",
  "data": {
    "user_id": 123,
    "username": "john.doe",
    "changed_at": "2026-01-08 17:30:00"
  }
}
```

### Error Responses

**Invalid Current Password (400)**
```json
{
  "success": false,
  "message": "Password saat ini salah",
  "error_code": "INVALID_CURRENT_PASSWORD"
}
```

**Password Too Short (400)**
```json
{
  "success": false,
  "message": "Password baru minimal 6 karakter",
  "error_code": "PASSWORD_TOO_SHORT"
}
```

**Same Password (400)**
```json
{
  "success": false,
  "message": "Password baru tidak boleh sama dengan password lama",
  "error_code": "SAME_PASSWORD"
}
```

**Password Mismatch (400)**
```json
{
  "success": false,
  "message": "Konfirmasi password tidak cocok",
  "error_code": "PASSWORD_MISMATCH"
}
```

**Unauthorized (401)**
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "error_code": "UNAUTHORIZED"
}
```

---

## Validation Rules

1. **Current Password** - Must match user's current password
2. **New Password** - Minimum 6 characters
3. **New Password** - Cannot be same as current password
4. **Confirm Password** - Must match new password

---

## Flutter Implementation

### 1. Service Class

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ProfileService {
  final String baseUrl = 'http://your-server.com/hrm/api/v1/profile';
  final String token;

  ProfileService(this.token);

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/change-password.php'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success']) {
        return;
      } else {
        throw PasswordException(
          data['message'],
          data['error_code'],
        );
      }
    } catch (e) {
      rethrow;
    }
  }
}

// Custom Exception
class PasswordException implements Exception {
  final String message;
  final String errorCode;

  PasswordException(this.message, this.errorCode);

  @override
  String toString() => message;
}
```

### 2. Change Password Screen

```dart
class ChangePasswordScreen extends StatefulWidget {
  @override
  _ChangePasswordScreenState createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  
  bool _isLoading = false;
  bool _obscureCurrentPassword = true;
  bool _obscureNewPassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleChangePassword() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() => _isLoading = true);

    try {
      final service = ProfileService(token);
      
      await service.changePassword(
        currentPassword: _currentPasswordController.text,
        newPassword: _newPasswordController.text,
        confirmPassword: _confirmPasswordController.text,
      );

      // Success
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Password berhasil diubah'),
          backgroundColor: Colors.green,
        ),
      );
    } on PasswordException catch (e) {
      _handleError(e);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _handleError(PasswordException e) {
    String message = e.message;

    switch (e.errorCode) {
      case 'INVALID_CURRENT_PASSWORD':
        message = 'Password saat ini salah';
        break;
      case 'PASSWORD_TOO_SHORT':
        message = 'Password baru minimal 6 karakter';
        break;
      case 'SAME_PASSWORD':
        message = 'Password baru tidak boleh sama dengan password lama';
        break;
      case 'PASSWORD_MISMATCH':
        message = 'Konfirmasi password tidak cocok';
        break;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Ubah Password'),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Current Password
              TextFormField(
                controller: _currentPasswordController,
                obscureText: _obscureCurrentPassword,
                decoration: InputDecoration(
                  labelText: 'Password Saat Ini',
                  prefixIcon: Icon(Icons.lock_outline),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureCurrentPassword
                          ? Icons.visibility
                          : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureCurrentPassword = !_obscureCurrentPassword;
                      });
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Password saat ini harus diisi';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),

              // New Password
              TextFormField(
                controller: _newPasswordController,
                obscureText: _obscureNewPassword,
                decoration: InputDecoration(
                  labelText: 'Password Baru',
                  prefixIcon: Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureNewPassword
                          ? Icons.visibility
                          : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureNewPassword = !_obscureNewPassword;
                      });
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Password baru harus diisi';
                  }
                  if (value.length < 6) {
                    return 'Password minimal 6 karakter';
                  }
                  if (value == _currentPasswordController.text) {
                    return 'Password baru tidak boleh sama dengan password lama';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),

              // Confirm Password
              TextFormField(
                controller: _confirmPasswordController,
                obscureText: _obscureConfirmPassword,
                decoration: InputDecoration(
                  labelText: 'Konfirmasi Password Baru',
                  prefixIcon: Icon(Icons.lock),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _obscureConfirmPassword
                          ? Icons.visibility
                          : Icons.visibility_off,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureConfirmPassword = !_obscureConfirmPassword;
                      });
                    },
                  ),
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Konfirmasi password harus diisi';
                  }
                  if (value != _newPasswordController.text) {
                    return 'Konfirmasi password tidak cocok';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),

              // Submit Button
              ElevatedButton(
                onPressed: _isLoading ? null : _handleChangePassword,
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isLoading
                    ? SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.white,
                          ),
                        ),
                      )
                    : Text(
                        'Ubah Password',
                        style: TextStyle(fontSize: 16),
                      ),
              ),

              SizedBox(height: 16),

              // Info Card
              Card(
                color: Colors.blue[50],
                child: Padding(
                  padding: EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.info_outline, color: Colors.blue),
                          SizedBox(width: 8),
                          Text(
                            'Persyaratan Password',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.blue[900],
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 8),
                      Text(
                        '• Minimal 6 karakter\n'
                        '• Tidak boleh sama dengan password lama\n'
                        '• Konfirmasi password harus cocok',
                        style: TextStyle(color: Colors.blue[900]),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
```

### 3. Simple Usage

```dart
// In your profile/settings screen
ListTile(
  leading: Icon(Icons.lock),
  title: Text('Ubah Password'),
  trailing: Icon(Icons.chevron_right),
  onTap: () {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ChangePasswordScreen(),
      ),
    );
  },
)
```

---

## Security Features

1. **Current Password Verification** - Ensures user knows current password
2. **Password Hashing** - Uses `password_hash()` with bcrypt
3. **Minimum Length** - Enforces 6 character minimum
4. **Same Password Check** - Prevents using same password
5. **Activity Logging** - Logs password changes for audit trail
6. **Token Authentication** - Requires valid bearer token

---

## Error Handling

```dart
try {
  await profileService.changePassword(
    currentPassword: current,
    newPassword: newPass,
    confirmPassword: confirm,
  );
  
  // Success - show message and navigate back
  showSuccessDialog();
  
} on PasswordException catch (e) {
  // Handle specific password errors
  switch (e.errorCode) {
    case 'INVALID_CURRENT_PASSWORD':
      showError('Password saat ini salah');
      break;
    case 'PASSWORD_TOO_SHORT':
      showError('Password minimal 6 karakter');
      break;
    case 'SAME_PASSWORD':
      showError('Gunakan password yang berbeda');
      break;
    case 'PASSWORD_MISMATCH':
      showError('Konfirmasi password tidak cocok');
      break;
  }
} catch (e) {
  // Handle general errors
  showError('Terjadi kesalahan');
}
```

---

## Testing

### cURL Example

```bash
curl -X POST http://localhost/hrm/api/v1/profile/change-password.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "oldpass123",
    "new_password": "newpass456",
    "confirm_password": "newpass456"
  }'
```

### Test Cases

1. **Valid Change** - All fields correct
2. **Wrong Current Password** - Should return INVALID_CURRENT_PASSWORD
3. **Short Password** - Should return PASSWORD_TOO_SHORT
4. **Same Password** - Should return SAME_PASSWORD
5. **Mismatch Confirm** - Should return PASSWORD_MISMATCH
6. **No Token** - Should return UNAUTHORIZED

---

## Notes

- Password changes are logged in `activity_logs` table
- User remains logged in after password change
- No email notification (can be added if needed)
- Token remains valid after password change

**Version:** 1.0  
**Last Updated:** 2026-01-08
