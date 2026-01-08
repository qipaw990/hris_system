# Quick Fix: Import Attendance Correction Tables

## Error
```
Table 'hris_db.attendance_correction_requests' doesn't exist
```

## Solution - Import Database Schema

### Option 1: Via phpMyAdmin (Recommended)

1. **Open phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Select Database**
   - Click on `hris_db` di sidebar kiri

3. **Import Schema**
   - Click tab "Import"
   - Click "Choose File"
   - Select: `c:\xampp3\htdocs\hrm\database\attendance_corrections_schema.sql`
   - Click "Go"

4. **Verify**
   - Check if table `attendance_correction_requests` muncul di list

---

### Option 2: Copy-Paste SQL

1. **Open File**
   - Buka file: `database/attendance_corrections_schema.sql`

2. **Copy SQL**
   - Copy semua isi file

3. **Execute in phpMyAdmin**
   - phpMyAdmin → hris_db → SQL tab
   - Paste SQL
   - Click "Go"

---

### Option 3: Manual SQL (Quick)

Jika file tidak ada, copy-paste SQL ini langsung:

```sql
-- Attendance Correction Requests Schema
CREATE TABLE IF NOT EXISTS attendance_correction_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    request_date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    reason TEXT NOT NULL,
    proof_attachment VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_request (employee_id, request_date, status),
    INDEX idx_status (status),
    INDEX idx_employee (employee_id),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Verify Installation

Run this query in phpMyAdmin SQL tab:

```sql
DESCRIBE attendance_correction_requests;
```

You should see table structure with columns:
- id
- employee_id
- request_date
- check_in_time
- check_out_time
- reason
- proof_attachment
- status
- reviewed_by
- reviewed_at
- rejection_reason
- created_at
- updated_at

---

## Test API Again

After importing, test the API endpoint:

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

Should return success response.

---

## Also Import (If Not Done)

While you're at it, also import these tables if they don't exist:

### 1. Holidays Table
```sql
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_name VARCHAR(100) NOT NULL,
    holiday_date DATE NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_holiday_date (holiday_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample holidays for 2026
INSERT INTO holidays (holiday_name, holiday_date, description) VALUES
('Tahun Baru 2026', '2026-01-01', 'Tahun Baru Masehi'),
('Idul Fitri', '2026-04-20', 'Hari Raya Idul Fitri 1447 H'),
('Hari Kemerdekaan RI', '2026-08-17', 'Hari Kemerdekaan Republik Indonesia'),
('Hari Natal', '2026-12-25', 'Hari Natal');
```

### 2. Activity Logs Table
```sql
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Files Location

All schema files are in:
```
c:\xampp3\htdocs\hrm\database\
├── attendance_corrections_schema.sql
└── holidays_schema.sql
```

---

## Troubleshooting

**Error: Foreign key constraint fails**
- Make sure `employees` and `users` tables exist first
- Check if employee_id and user_id columns exist

**Error: Table already exists**
- Table sudah ada, tidak perlu import lagi
- Check dengan: `SHOW TABLES LIKE 'attendance_correction_requests';`

**Still getting error after import**
- Clear browser cache
- Restart Apache/MySQL
- Check database connection in `config/database.php`
