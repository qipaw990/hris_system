# Auto-Absent System - Setup Guide

## Overview
Sistem otomatis untuk menandai karyawan sebagai "Alpha" (Absent) ketika jam kerja sudah lewat dan karyawan belum check-in.

## Files Created
1. ✅ `cron/mark_absent.php` - Cron job script
2. ✅ `admin/attendance/auto_absent.php` - Admin interface
3. ✅ `admin/attendance/process_override.php` - Override processor

## Setup Instructions

### 1. Setup Cron Job (Windows - Task Scheduler)

**Option A: Using Task Scheduler GUI**
1. Open Task Scheduler (`taskschd.msc`)
2. Create New Task:
   - Name: "HRIS Auto-Absent Marker"
   - Description: "Mark employees as absent after work hours"
3. Triggers:
   - Daily at 18:00 (6 PM)
   - Days: Monday to Friday
4. Actions:
   - Program: `C:\xampp3\php\php.exe`
   - Arguments: `C:\xampp3\htdocs\hrm\cron\mark_absent.php`
5. Conditions:
   - ✅ Start only if computer is on AC power (uncheck)
   - ✅ Wake computer to run this task

**Option B: Using Command Line**
```powershell
schtasks /create /tn "HRIS_AutoAbsent" /tr "C:\xampp3\php\php.exe C:\xampp3\htdocs\hrm\cron\mark_absent.php" /sc daily /st 18:00 /d MON,TUE,WED,THU,FRI
```

### 2. Manual Testing

Test the script manually before scheduling:
```powershell
cd C:\xampp3\htdocs\hrm\cron
php mark_absent.php
```

Check the log file:
```powershell
type logs\auto_absent_2026-01.log
```

### 3. Configure Working Days

Edit `mark_absent.php` if your working days are different:
```php
// Line 27: Change weekend days
if (in_array($currentDay, ['Saturday', 'Sunday'])) {
    // Change to your weekend days
}
```

### 4. Create Holidays Table (if not exists)

```sql
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_name VARCHAR(100) NOT NULL,
    holiday_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example holidays
INSERT INTO holidays (holiday_name, holiday_date) VALUES
('Tahun Baru', '2026-01-01'),
('Idul Fitri', '2026-04-01'),
('Hari Kemerdekaan', '2026-08-17');
```

### 5. Create Activity Logs Table (if not exists)

```sql
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## How It Works

### Automatic Process (Daily at 18:00)
1. ✅ Check if today is weekend → Skip
2. ✅ Check if today is holiday → Skip
3. ✅ Get all active employees
4. ✅ For each employee:
   - Check if has attendance record → Skip
   - Check if on approved leave → Skip
   - Mark as "Alpha" with note "Auto-marked absent"
5. ✅ Log all actions to file

### Manual Override (Admin)
1. Admin opens `/admin/attendance/auto_absent.php`
2. View all auto-absent records
3. Click "Override" button
4. Select new status (Hadir, Izin, Sakit, Cuti)
5. Provide reason for override
6. System logs the override action

## Log Files

Logs are stored in: `cron/logs/auto_absent_YYYY-MM.log`

Example log entry:
```
[2026-01-07 18:00:01] === Starting Auto-Absent Process ===
[2026-01-07 18:00:01] Date: 2026-01-07 (Tuesday)
[2026-01-07 18:00:01] Found 50 active employees
[2026-01-07 18:00:02] [EMP001] John Doe - Already has attendance record (Status: Hadir)
[2026-01-07 18:00:02] [EMP002] Jane Smith - On approved leave (Cuti Tahunan)
[2026-01-07 18:00:03] [EMP003] Bob Wilson - Marked as ALPHA (Auto-absent)
[2026-01-07 18:00:05] === Auto-Absent Process Completed ===
[2026-01-07 18:00:05] Total Marked as Absent: 5
[2026-01-07 18:00:05] Total Skipped: 45
[2026-01-07 18:00:05] Total Processed: 50
```

## Admin Interface Features

### View Auto-Absent Records
- Filter by date
- Monthly summary
- List of all auto-marked employees
- Department breakdown

### Override Status
- Change status from Alpha to:
  - Hadir (Present)
  - Izin (Permission)
  - Sakit (Sick)
  - Cuti (Leave)
- Provide reason for override
- Automatic logging

## Troubleshooting

### Script doesn't run
1. Check PHP path: `where php`
2. Check file permissions
3. Check Task Scheduler logs

### No employees marked
1. Check if today is weekend/holiday
2. Verify employees have no attendance records
3. Check log file for details

### Database errors
1. Verify database connection in `config/database.php`
2. Check if tables exist
3. Review error logs

## Customization

### Change work hours end time
Edit Task Scheduler trigger time (default: 18:00)

### Change weekend days
Edit `mark_absent.php` line 27

### Add custom exclusions
Add logic in `mark_absent.php` before marking absent

## Security Notes

- ✅ Only admin/hr can view auto-absent records
- ✅ All overrides are logged
- ✅ CSRF protection on override form
- ✅ Detailed audit trail

## Next Steps

1. ✅ Test manually first
2. ✅ Setup cron job
3. ✅ Add holidays to database
4. ✅ Monitor logs daily
5. ✅ Train admin staff on override process
