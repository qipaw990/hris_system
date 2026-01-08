# Authorization Header Fix - Testing Guide

## ✅ What Was Fixed

### 1. Created `.htaccess` File
**Location:** `c:\xampp3\htdocs\hrm\api\v1\.htaccess`

This file tells Apache to pass the Authorization header to PHP:
```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

### 2. Improved `getAuthHeader()` Function
**Location:** `c:\xampp3\htdocs\hrm\api\v1\config.php`

Now checks **4 different methods** to find the Authorization header:
1. `$_SERVER['HTTP_AUTHORIZATION']` - Standard Apache
2. `$_SERVER['REDIRECT_HTTP_AUTHORIZATION']` - After .htaccess rewrite
3. `$_SERVER['HTTP_X_AUTH_TOKEN']` - Custom header fallback
4. `apache_request_headers()` - Direct header access

### 3. Created Test Endpoint
**Location:** `c:\xampp3\htdocs\hrm\api\v1\test-auth.php`

Use this to debug Authorization header issues.

---

## 🧪 Testing Steps

### Step 1: Test with Browser/Postman

**URL:** `http://localhost/hrm/api/v1/test-auth.php`

**Add Header:**
```
Authorization: Bearer test-token-12345
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Authorization header debug info",
  "headers_found": {
    "HTTP_AUTHORIZATION": "Bearer test-token-12345"
  },
  "has_authorization": true,
  "recommendation": "✅ Authorization header is being passed correctly!"
}
```

**If NOT working:**
- Check if `.htaccess` file exists
- Check if Apache `mod_rewrite` is enabled
- Check Apache error logs

### Step 2: Test Login API

**URL:** `http://localhost/hrm/api/v1/auth/login.php`

**Request:**
```json
POST /api/v1/auth/login.php
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ1c2VyX2lkIjo4...",
    "user": {...}
  }
}
```

### Step 3: Test Get User API

**URL:** `http://localhost/hrm/api/v1/auth/me.php`

**Add Header:**
```
Authorization: Bearer {token_from_login}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User info retrieved successfully",
  "data": {
    "id": 1,
    "username": "admin",
    ...
  }
}
```

**If you get "Authorization header missing":**
- The `.htaccess` is not working
- Try restarting Apache
- Check if `mod_rewrite` is enabled

---

## 🔧 Troubleshooting

### Problem 1: .htaccess Not Working

**Check if mod_rewrite is enabled:**

1. Open `c:\xampp3\apache\conf\httpd.conf`
2. Find this line:
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Remove the `#` to uncomment:
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. Restart Apache

**Check if .htaccess is allowed:**

1. Open `c:\xampp3\apache\conf\httpd.conf`
2. Find `<Directory "C:/xampp3/htdocs">`
3. Make sure `AllowOverride` is set to `All`:
   ```apache
   <Directory "C:/xampp3/htdocs">
       AllowOverride All
   </Directory>
   ```
4. Restart Apache

### Problem 2: Still Getting "Authorization header missing"

**Use the custom header workaround:**

**In Flutter:**
```dart
// Instead of Authorization header
headers: {
  'Content-Type': 'application/json',
  'X-Auth-Token': token,  // Custom header
}
```

**Backend already supports this!** The `getAuthHeader()` function will automatically detect `X-Auth-Token` and convert it to `Bearer {token}`.

### Problem 3: JSON Parse Error

**Make sure:**
1. No PHP errors/warnings are displayed
2. `error_reporting(0)` is set in `config.php` ✅ (already done)
3. No extra whitespace before `<?php` tag
4. Output buffering is working ✅ (already done)

---

## 📱 Flutter Testing

### Test 1: Login
```dart
final response = await http.post(
  Uri.parse('http://YOUR_IP/hrm/api/v1/auth/login.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'username': 'admin',
    'password': 'admin123',
  }),
);

print('Status: ${response.statusCode}');
print('Body: ${response.body}');

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  final token = data['data']['token'];
  print('✅ Token: $token');
}
```

### Test 2: Get User with Token
```dart
final response = await http.get(
  Uri.parse('http://YOUR_IP/hrm/api/v1/auth/me.php'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
);

print('Status: ${response.statusCode}');
print('Body: ${response.body}');

if (response.statusCode == 200) {
  print('✅ Authorization header working!');
} else {
  print('❌ Authorization header NOT working');
  print('Try custom header instead');
}
```

### Test 3: Using Custom Header (Fallback)
```dart
// If Authorization header doesn't work, use this:
final response = await http.get(
  Uri.parse('http://YOUR_IP/hrm/api/v1/auth/me.php'),
  headers: {
    'X-Auth-Token': token,  // Custom header
    'Content-Type': 'application/json',
  },
);
```

---

## ✅ Verification Checklist

- [ ] `.htaccess` file created in `/api/v1/` folder
- [ ] Apache `mod_rewrite` enabled
- [ ] Apache restarted after changes
- [ ] Test endpoint returns "✅ Authorization header is being passed correctly!"
- [ ] Login API returns token
- [ ] Get user API works with token
- [ ] Flutter app can authenticate successfully

---

## 🆘 If Still Not Working

1. **Test the debug endpoint first:**
   ```
   http://localhost/hrm/api/v1/test-auth.php
   ```
   With header: `Authorization: Bearer test123`

2. **Check the response** - it will tell you exactly what's wrong

3. **Use custom header as temporary workaround:**
   - Change Flutter to use `X-Auth-Token` header
   - Backend already supports it!

4. **Share the output** of `test-auth.php` for further debugging

---

**Files Modified:**
- ✅ `api/v1/.htaccess` (NEW)
- ✅ `api/v1/config.php` (Updated `getAuthHeader()`)
- ✅ `api/v1/test-auth.php` (NEW - for testing)

**Status:** Ready to test! 🚀
