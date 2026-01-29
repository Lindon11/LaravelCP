# 🚨 Fixing 403 Forbidden Error

## Quick Fixes (try in order)

### 1. Re-upload updated .htaccess file
Upload the updated [laravel-api/public/.htaccess](laravel-api/public/.htaccess) file - I just added explicit permissions.

### 2. Run permission fix script via SSH
```bash
ssh your-username@new.criminal-empire.co.uk
cd ~/new.criminal-empire.co.uk
chmod +x fix-permissions.sh
./fix-permissions.sh
```

### 3. Manual Permission Fix (if no SSH access)
Via FTP/File Manager, set these permissions:

**Directories:** `755` (rwxr-xr-x)
- All folders in `laravel-api/`

**Files:** `644` (rw-r--r--)
- All files including `.htaccess`, `index.php`

**Writable Folders:** `775` (rwxrwxr-x)
- `laravel-api/storage/` and all subfolders
- `laravel-api/bootstrap/cache/`

### 4. Check Document Root Again
In your hosting panel, verify document root is EXACTLY:
```
new.criminal-empire.co.uk/laravel-api/public
```

NOT:
- ~~`/laravel-api/public`~~ (missing domain prefix)
- ~~`new.criminal-empire.co.uk/public`~~ (missing laravel-api)
- ~~`new.criminal-empire.co.uk/laravel-api`~~ (missing /public)

### 5. Verify index.php exists
Check that this file exists and is readable:
```
new.criminal-empire.co.uk/laravel-api/public/index.php
```

## If Still Getting 403

### Check with Hosting Support
Ask them to verify:

1. **PHP Version**: Must be 8.2 or higher
2. **Apache mod_rewrite**: Must be enabled
3. **AllowOverride**: Must be set to `All` for your directory
4. **File Ownership**: Files should be owned by web server user

### Alternative Document Root Setup
Some hosts require the path without domain name:
```
/laravel-api/public
```
or
```
/public_html/laravel-api/public
```

Try these variations in your hosting panel.

### Check Error Logs
In your hosting panel, check error logs for specific issues:
- PHP error log
- Apache/Web server error log

Look for messages like:
- "Directory index forbidden"
- "Permission denied"
- "AllowOverride None"

## Still Stuck?

### Create info.php for testing
Create `new.criminal-empire.co.uk/laravel-api/public/info.php`:
```php
<?php
phpinfo();
```

Visit: `https://new.criminal-empire.co.uk/info.php`

If this shows PHP info, the problem is Laravel-specific.
If this gives 403, it's a server permission issue.

**Delete info.php after testing!**

### Contact Hosting Support
Tell them:
- "Laravel application giving 403 error"
- "Document root set to: new.criminal-empire.co.uk/laravel-api/public"
- "Need mod_rewrite enabled and AllowOverride All"
- "PHP 8.2+ required"
