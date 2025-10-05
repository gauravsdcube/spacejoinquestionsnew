# Production Debug Checklist for Link Rendering Issues

## Step 1: Verify File Deployment
Run these commands on your production server:

```bash
# Navigate to the module directory
cd /path/to/humhub/protected/modules/space-join-questions

# Check if the updated files exist
ls -la models/EmailTemplate.php
ls -la views/email-template/preview.php
ls -la VERSION

# Check file modification dates
stat models/EmailTemplate.php
stat views/email-template/preview.php
```

## Step 2: Check File Content
```bash
# Verify the updated content is present
grep -n "processPlainUrls" models/EmailTemplate.php
grep -n "color: #dd0031" models/EmailTemplate.php
grep -n "Debug Information" views/email-template/preview.php
cat VERSION
```

## Step 3: Run Debug Scripts
```bash
# Run the comprehensive debug script
php debug_production_links.php

# Run the simple link test
php test_production_links.php
```

## Step 4: Check HumHub Cache
```bash
# Clear HumHub cache
cd /path/to/humhub
php protected/yii cache/flush-all

# Check if cache directory is writable
ls -la protected/runtime/cache/
```

## Step 5: Check Web Server Configuration
```bash
# Check if PHP is loading the updated files
# Look for any opcache or file caching
php -i | grep -i cache

# Check if there are any .htaccess rules affecting the module
cat .htaccess | grep -i module
```

## Step 6: Test Email Template Preview
1. Log into your HumHub admin panel
2. Go to a space with the module enabled
3. Navigate to Email Templates
4. Try to preview an email template
5. Check browser developer tools for any JavaScript errors
6. Check if the debug panel appears in the preview

## Step 7: Check Error Logs
```bash
# Check HumHub logs
tail -f protected/runtime/logs/app.log

# Check web server logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

## Step 8: Verify Database
```bash
# Check if the module is properly installed
mysql -u username -p database_name -e "SHOW TABLES LIKE 'space_join%';"

# Check if there are any email templates
mysql -u username -p database_name -e "SELECT * FROM space_join_email_template LIMIT 5;"
```

## Common Issues and Solutions

### Issue 1: Files Not Updated
**Symptoms**: Old file timestamps, missing new code
**Solution**: 
```bash
# Re-upload the files
# Make sure to overwrite existing files
# Check file permissions: chmod 644 for files, 755 for directories
```

### Issue 2: Cache Issues
**Symptoms**: Changes not visible despite file updates
**Solution**:
```bash
# Clear all caches
php protected/yii cache/flush-all
# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

### Issue 3: PHP OpCache
**Symptoms**: Code changes not taking effect
**Solution**:
```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
# or restart Apache/Nginx
```

### Issue 4: File Permissions
**Symptoms**: Files not readable by web server
**Solution**:
```bash
# Fix permissions
sudo chown -R www-data:www-data protected/modules/space-join-questions
sudo chmod -R 755 protected/modules/space-join-questions
```

### Issue 5: Module Not Enabled
**Symptoms**: Module functionality not available
**Solution**:
```bash
# Check if module is enabled
php protected/yii module/list
# Enable if needed
php protected/yii module/enable space-join-questions
```

## Expected Results

After running the debug scripts, you should see:

1. **File Check**: All files should exist with recent timestamps
2. **Content Check**: All new code should be present
3. **Link Test**: Should show "SUCCESS: Link processing is working!"
4. **Debug Script**: Should show all green checkmarks
5. **Preview**: Email template preview should show clickable red links

## If Issues Persist

1. **Check the debug output** and share the results
2. **Verify the exact error** you're seeing in production
3. **Check browser console** for JavaScript errors
4. **Test with a simple email template** first
5. **Try creating a new email template** to see if the issue persists

## Contact Information

If you need further assistance, please share:
- Output of `debug_production_links.php`
- Output of `test_production_links.php`
- Screenshots of the issue
- Browser console errors
- Any error messages from logs
