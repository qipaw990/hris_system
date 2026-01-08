<?php
/**
 * Generate Password Hash
 * Run this file to generate correct password hashes
 */

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

// Generate SQL statements
echo "-- SQL Statements to update users:\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'hr_manager';\n";
