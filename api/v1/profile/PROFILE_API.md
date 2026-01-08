# Profile API Documentation

## Get User Profile

**Endpoint:** `GET /api/v1/profile/me.php`

**Authentication:** Bearer Token Required

**Purpose:** Mendapatkan data profil user yang sedang login beserta foto profil.

---

## Response

### Success Response (200)

```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "user_id": 123,
    "username": "john.doe",
    "role": "Employee",
    "email": "john.doe@company.com",
    "employee": {
      "id": 45,
      "employee_code": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "full_name": "John Doe",
      "email": "john.doe@company.com",
      "phone": "081234567890",
      "date_of_birth": "1990-05-15",
      "gender": "Male",
      "address": "Jl. Sudirman No. 123, Jakarta",
      "department": {
        "id": 3,
        "name": "IT Department"
      },
      "position": {
        "id": 5,
        "name": "Software Developer"
      },
      "hire_date": "2020-01-15",
      "employment_status": "Active",
      "photo": "employee_photo_123.jpg",
      "photo_url": "http://your-server.com/hrm/assets/uploads/employee_photo_123.jpg"
    }
  }
}
```

### Response (User without Employee Data)

```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "user_id": 1,
    "username": "admin",
    "role": "Admin",
    "email": "admin@company.com",
    "employee": null
  }
}
```

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

  Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('$baseUrl/me.php'),
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
    throw Exception('Failed to get profile');
  }
}
```

### 2. Profile Model

```dart
class UserProfile {
  final int userId;
  final String username;
  final String role;
  final String? email;
  final Employee? employee;

  UserProfile({
    required this.userId,
    required this.username,
    required this.role,
    this.email,
    this.employee,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      userId: json['user_id'],
      username: json['username'],
      role: json['role'],
      email: json['email'],
      employee: json['employee'] != null
          ? Employee.fromJson(json['employee'])
          : null,
    );
  }
}

class Employee {
  final int id;
  final String employeeCode;
  final String firstName;
  final String lastName;
  final String fullName;
  final String? email;
  final String? phone;
  final String? dateOfBirth;
  final String? gender;
  final String? address;
  final Department? department;
  final Position? position;
  final String? hireDate;
  final String? employmentStatus;
  final String? photo;
  final String? photoUrl;

  Employee({
    required this.id,
    required this.employeeCode,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    this.email,
    this.phone,
    this.dateOfBirth,
    this.gender,
    this.address,
    this.department,
    this.position,
    this.hireDate,
    this.employmentStatus,
    this.photo,
    this.photoUrl,
  });

  factory Employee.fromJson(Map<String, dynamic> json) {
    return Employee(
      id: json['id'],
      employeeCode: json['employee_code'],
      firstName: json['first_name'],
      lastName: json['last_name'],
      fullName: json['full_name'],
      email: json['email'],
      phone: json['phone'],
      dateOfBirth: json['date_of_birth'],
      gender: json['gender'],
      address: json['address'],
      department: json['department'] != null
          ? Department.fromJson(json['department'])
          : null,
      position: json['position'] != null
          ? Position.fromJson(json['position'])
          : null,
      hireDate: json['hire_date'],
      employmentStatus: json['employment_status'],
      photo: json['photo'],
      photoUrl: json['photo_url'],
    );
  }
}

class Department {
  final int? id;
  final String? name;

  Department({this.id, this.name});

  factory Department.fromJson(Map<String, dynamic> json) {
    return Department(
      id: json['id'],
      name: json['name'],
    );
  }
}

class Position {
  final int? id;
  final String? name;

  Position({this.id, this.name});

  factory Position.fromJson(Map<String, dynamic> json) {
    return Position(
      id: json['id'],
      name: json['name'],
    );
  }
}
```

### 3. Profile Screen

```dart
class ProfileScreen extends StatefulWidget {
  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ProfileService _service = ProfileService(token);
  UserProfile? profile;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    try {
      final data = await _service.getProfile();
      setState(() {
        profile = UserProfile.fromJson(data);
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load profile: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (profile == null) {
      return Scaffold(
        body: Center(child: Text('Failed to load profile')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Profile'),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            // Profile Photo
            Center(
              child: CircleAvatar(
                radius: 60,
                backgroundImage: profile!.employee?.photoUrl != null
                    ? NetworkImage(profile!.employee!.photoUrl!)
                    : null,
                child: profile!.employee?.photoUrl == null
                    ? Icon(Icons.person, size: 60)
                    : null,
              ),
            ),
            SizedBox(height: 16),

            // Full Name
            Text(
              profile!.employee?.fullName ?? profile!.username,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 4),

            // Employee Code
            if (profile!.employee != null)
              Text(
                profile!.employee!.employeeCode,
                style: TextStyle(
                  color: Colors.grey[600],
                  fontSize: 16,
                ),
              ),
            SizedBox(height: 24),

            // Profile Details
            if (profile!.employee != null) ...[
              _buildInfoCard(
                icon: Icons.email,
                title: 'Email',
                value: profile!.employee!.email ?? '-',
              ),
              _buildInfoCard(
                icon: Icons.phone,
                title: 'Phone',
                value: profile!.employee!.phone ?? '-',
              ),
              _buildInfoCard(
                icon: Icons.business,
                title: 'Department',
                value: profile!.employee!.department?.name ?? '-',
              ),
              _buildInfoCard(
                icon: Icons.work,
                title: 'Position',
                value: profile!.employee!.position?.name ?? '-',
              ),
              _buildInfoCard(
                icon: Icons.calendar_today,
                title: 'Hire Date',
                value: profile!.employee!.hireDate ?? '-',
              ),
              _buildInfoCard(
                icon: Icons.info,
                title: 'Status',
                value: profile!.employee!.employmentStatus ?? '-',
              ),
            ],

            SizedBox(height: 24),

            // Change Password Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => ChangePasswordScreen(),
                    ),
                  );
                },
                icon: Icon(Icons.lock),
                label: Text('Change Password'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String title,
    required String value,
  }) {
    return Card(
      margin: EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: Icon(icon, color: Colors.blue),
        title: Text(
          title,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
        subtitle: Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w500,
          ),
        ),
      ),
    );
  }
}
```

### 4. Display Photo in Other Screens

```dart
// In AppBar or Drawer
CircleAvatar(
  backgroundImage: profile?.employee?.photoUrl != null
      ? NetworkImage(profile!.employee!.photoUrl!)
      : null,
  child: profile?.employee?.photoUrl == null
      ? Icon(Icons.person)
      : null,
)

// In List Item
ListTile(
  leading: CircleAvatar(
    backgroundImage: employee.photoUrl != null
        ? NetworkImage(employee.photoUrl!)
        : null,
    child: employee.photoUrl == null
        ? Text(employee.firstName[0])
        : null,
  ),
  title: Text(employee.fullName),
  subtitle: Text(employee.employeeCode),
)
```

---

## Photo URL Format

The API automatically generates the full photo URL:

```
http://your-server.com/hrm/assets/uploads/employee_photo_123.jpg
```

**Components:**
- Protocol: `http` or `https` (auto-detected)
- Host: Server hostname
- Path: `/hrm/assets/uploads/`
- Filename: From database `photo` field

---

## Error Handling

```dart
try {
  final profile = await profileService.getProfile();
  
  // Load photo
  if (profile.employee?.photoUrl != null) {
    // Use NetworkImage with error handling
    Image.network(
      profile.employee!.photoUrl!,
      errorBuilder: (context, error, stackTrace) {
        return Icon(Icons.person, size: 60);
      },
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return CircularProgressIndicator();
      },
    );
  }
} catch (e) {
  print('Error loading profile: $e');
}
```

---

## Notes

- Photo URL is automatically generated based on server configuration
- Returns `null` if employee has no photo
- Photo path is relative to `/assets/uploads/` directory
- Supports both HTTP and HTTPS

**Version:** 1.0  
**Last Updated:** 2026-01-08
