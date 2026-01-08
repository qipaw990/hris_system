# Debugging Approve Process

## Issue
Status tetap "Pending" setelah klik approve button.

## Possible Causes

### 1. Activity Logs Table Missing
**Fixed:** Made activity_logs insert optional
- Moved `commit()` before activity logging
- Wrapped activity log in try-catch
- Won't rollback transaction if logging fails

### 2. Check Error Messages
Look for error messages in:
- Browser (flash messages)
- PHP error log
- Browser console

### 3. Verify Database Changes

**Check if request status updated:**
```sql
SELECT id, employee_id, request_date, status, reviewed_by, reviewed_at 
FROM attendance_correction_requests 
WHERE id = YOUR_REQUEST_ID;
```

**Check if attendance record created:**
```sql
SELECT * FROM attendance 
WHERE employee_id = YOUR_EMPLOYEE_ID 
AND attendance_date = 'YOUR_REQUEST_DATE';
```

### 4. Common Issues

**Issue:** Foreign key constraint fails
- `reviewed_by` references `users.id`
- Make sure your user_id exists in users table

**Issue:** Attendance table column mismatch
- Check if `check_in` and `check_out` columns exist
- Some systems use `check_in_time` and `check_out_time`

**Issue:** Transaction rollback
- Check PHP error log for exceptions
- Look for "Transaction error in approve:" messages

## Testing Steps

1. **Click Approve Button**
   - Should show confirm dialog
   - Click OK

2. **Check for Flash Message**
   - Success: "Request berhasil disetujui..."
   - Error: "Error: ..." or "Gagal approve request..."

3. **Verify Status Changed**
   - Refresh page
   - Status should be "Approved" with green badge
   - Should show "by [username]"

4. **Check Attendance Record**
   ```sql
   SELECT * FROM attendance 
   WHERE employee_id = X 
   AND attendance_date = 'YYYY-MM-DD';
   ```

## Quick Fix SQL

If table doesn't exist, create it:

```sql
-- Create activity_logs (optional)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## Manual Approve (Emergency)

If approve button still doesn't work, manually update:

```sql
-- Update request status
UPDATE attendance_correction_requests 
SET status = 'Approved', 
    reviewed_by = YOUR_USER_ID,
    reviewed_at = NOW()
WHERE id = REQUEST_ID;

-- Create attendance record
INSERT INTO attendance 
(employee_id, attendance_date, check_in, check_out, status, notes, created_at)
VALUES 
(EMPLOYEE_ID, 'REQUEST_DATE', 'CHECK_IN_TIME', 'CHECK_OUT_TIME', 'Hadir', 'Manually approved', NOW());
```

## Files Modified

1. ✅ `process_approve.php` - Made activity_logs optional
2. ✅ Added detailed error logging
3. ✅ Moved commit before logging

## Next Steps

1. Test approve button
2. Check browser for error messages
3. Check PHP error log
4. Run SQL queries to verify
