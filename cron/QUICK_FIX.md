# Quick Fix: Import Holidays Table

## Error
```
Table 'hris_db.holidays' doesn't exist
```

## Solution

### Option 1: Via phpMyAdmin (Recommended)
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database `hris_db`
3. Click "Import" tab
4. Choose file: `c:\xampp3\htdocs\hrm\database\holidays_schema.sql`
5. Click "Go"

### Option 2: Via MySQL Command Line
```bash
# Open Command Prompt as Administrator
cd C:\xampp\mysql\bin

# Import the schema
mysql.exe -u root hris_db < "C:\xampp3\htdocs\hrm\database\holidays_schema.sql"
```

### Option 3: Copy-Paste SQL
1. Open the file: `database/holidays_schema.sql`
2. Copy all SQL content
3. Open phpMyAdmin → hris_db → SQL tab
4. Paste and execute

## Verify Installation

Run this query in phpMyAdmin:
```sql
SELECT * FROM holidays;
```

You should see 16 holidays for 2026.

## Test Auto-Absent Script

After importing, test the script:
```powershell
cd C:\xampp3\htdocs\hrm\cron
php mark_absent.php
```

Check the log:
```powershell
type logs\auto_absent_2026-01.log
```

## What's Fixed

✅ Script now handles missing holidays table gracefully
✅ Will continue without holiday check if table doesn't exist
✅ Logs warning message for missing table
