# Production Deployment Guide - Space Join Questions Module

This guide provides step-by-step instructions for deploying the Space Join Questions module to a production HumHub environment.

## 🚀 Pre-Deployment Checklist

### System Requirements
- [ ] **HumHub Version**: 1.17.3 or higher
- [ ] **PHP Version**: 8.0 or higher (tested with PHP 8.4)
- [ ] **Database**: MySQL 5.7+ or MariaDB 10.2+
- [ ] **Server Resources**: Minimum 512MB RAM, 1GB recommended
- [ ] **Disk Space**: At least 100MB free space
- [ ] **Backup**: Full backup of HumHub installation and database

### Environment Preparation
- [ ] **Maintenance Mode**: Enable maintenance mode if needed
- [ ] **Database Backup**: Create complete database backup
- [ ] **File Backup**: Backup all HumHub files
- [ ] **Email Configuration**: Verify mailer settings
- [ ] **SSL Certificate**: Ensure HTTPS is properly configured
- [ ] **File Permissions**: Verify proper file ownership and permissions

## 📋 Deployment Methods

### Method 1: Automated Installation (Recommended)

#### Step 1: Download and Prepare
```bash
# Navigate to your HumHub installation
cd /path/to/humhub

# Create a temporary directory for the module
mkdir -p temp/spaceJoinQuestions
cd temp/spaceJoinQuestions

# Download the module files (replace with actual download method)
# wget https://example.com/spaceJoinQuestions-2.0.0.zip
# unzip spaceJoinQuestions-2.0.0.zip
```

#### Step 2: Run Installation Script
```bash
# Make script executable
chmod +x install.sh

# Run the installation script
./install.sh
```

#### Step 3: Verify Installation
```bash
# Check module is detected
php protected/yii module/list | grep space-join-questions

# Verify database tables
php protected/yii migrate/history --migrationPath=@spaceJoinQuestions/migrations

# Test email functionality
php protected/yii test/email-configuration
```

### Method 2: Manual Installation

#### Step 1: Backup Core Files
```bash
# Create backup directory
mkdir -p backups/spaceJoinQuestions_$(date +%Y%m%d_%H%M%S)

# Backup core files
cp protected/humhub/modules/space/controllers/MembershipController.php backups/
cp protected/humhub/modules/space/views/space/about.php backups/
```

#### Step 2: Install Module Files
```bash
# Copy module to protected/modules
cp -r spaceJoinQuestions protected/modules/

# Set proper permissions
find protected/modules/spaceJoinQuestions -type d -exec chmod 755 {} \;
find protected/modules/spaceJoinQuestions -type f -exec chmod 644 {} \;
chown -R www-data:www-data protected/modules/spaceJoinQuestions
```

#### Step 3: Apply Core Modifications
```bash
# Apply modified MembershipController.php
cp spaceJoinQuestions/core_patches/MembershipController.php protected/humhub/modules/space/controllers/

# Apply modified about.php
cp spaceJoinQuestions/core_patches/about.php protected/humhub/modules/space/views/space/
```

#### Step 4: Run Database Migrations
```bash
# Run migrations
php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations

# Verify tables created
mysql -u username -p database_name -e "SHOW TABLES LIKE 'space_join%';"
```

#### Step 5: Clear Cache
```bash
# Clear all caches
php protected/yii cache/flush-all

# Clear runtime cache
rm -rf protected/runtime/cache/*
```

## ⚙️ Configuration

### Email Configuration

#### 1. SMTP Configuration
Edit `protected/config/common.php`:
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false, // IMPORTANT: Set to false for production
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'your-smtp-server.com',
        'username' => 'your-email@domain.com',
        'password' => 'your-password',
        'port' => '587',
        'encryption' => 'tls',
    ],
],
```

#### 2. Test Email Configuration
```bash
# Create test script
cat > test_email.php << 'EOF'
<?php
require_once 'protected/yii';
$app = new humhub\components\ConsoleApplication([]);

$mail = Yii::$app->mailer->compose()
    ->setTo('test@example.com')
    ->setSubject('Test Email')
    ->setTextBody('This is a test email from HumHub');

if ($mail->send()) {
    echo "Email sent successfully\n";
} else {
    echo "Email failed to send\n";
}
EOF

# Run test
php test_email.php
```

### Module Configuration

#### 1. Enable Module via Web Interface
1. Log into HumHub as administrator
2. Go to **Administration** → **Modules**
3. Find **Space Join Questions** and click **Enable**
4. Verify module appears in enabled modules list

#### 2. Configure Spaces
1. Go to any space's **Settings** → **General**
2. Check **"Require application for membership"**
3. Add custom questions as needed
4. Save settings

#### 3. Test Application Process
1. Create a test user account
2. Try to join a space with application requirement enabled
3. Verify application form appears
4. Submit test application
5. Check email notifications are sent

## 🔍 Post-Deployment Verification

### Functionality Tests

#### 1. Application Submission
- [ ] User can access application form
- [ ] Form saves responses correctly
- [ ] Email notification sent to space admins
- [ ] Application appears in admin dashboard

#### 2. Admin Interface
- [ ] Admin can view pending applications
- [ ] Admin can approve applications
- [ ] Admin can decline applications with custom reason
- [ ] Email notifications sent to applicants

#### 3. Email Notifications
- [ ] Application received emails sent
- [ ] Application accepted emails sent
- [ ] Application declined emails sent
- [ ] Email content is personalized
- [ ] Email design is professional (no marketing elements)

#### 4. User Experience
- [ ] Application status displays correctly
- [ ] Users can see their pending applications
- [ ] Direct membership blocked for spaces with applications
- [ ] Normal membership works for spaces without applications

### Performance Tests

#### 1. Database Performance
```bash
# Check database performance
mysql -u username -p database_name -e "
SELECT 
    table_name,
    table_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'database_name' 
AND table_name LIKE 'space_join%';
"
```

#### 2. Email Performance
```bash
# Monitor email sending
tail -f protected/runtime/logs/app.log | grep -i mail
```

#### 3. Memory Usage
```bash
# Monitor PHP memory usage
ps aux | grep php-fpm
```

## 🛡️ Security Considerations

### File Permissions
```bash
# Set secure file permissions
find protected/modules/spaceJoinQuestions -type d -exec chmod 755 {} \;
find protected/modules/spaceJoinQuestions -type f -exec chmod 644 {} \;
chmod 600 protected/config/common.php
chown -R www-data:www-data protected/modules/spaceJoinQuestions
```

### Database Security
```sql
-- Create dedicated database user for HumHub
CREATE USER 'humhub_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON humhub_database.* TO 'humhub_user'@'localhost';
FLUSH PRIVILEGES;
```

### Email Security
- Use SMTP with TLS/SSL encryption
- Avoid using `useFileTransport = true` in production
- Configure proper SPF and DKIM records
- Monitor email logs for security issues

## 📊 Monitoring and Maintenance

### Log Monitoring
```bash
# Monitor application logs
tail -f protected/runtime/logs/app.log

# Monitor email logs
tail -f protected/runtime/logs/mail.log

# Monitor error logs
tail -f protected/runtime/logs/error.log
```

### Database Maintenance
```sql
-- Regular database optimization
OPTIMIZE TABLE space_join_application;
OPTIMIZE TABLE space_join_question;
OPTIMIZE TABLE space_join_question_response;

-- Check for orphaned records
SELECT COUNT(*) FROM space_join_application 
WHERE user_id NOT IN (SELECT id FROM user);

-- Clean up old declined applications (optional)
DELETE FROM space_join_application 
WHERE status = 'declined' 
AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Backup Strategy
```bash
# Daily database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Weekly full backup
tar -czf humhub_backup_$(date +%Y%m%d).tar.gz . --exclude=protected/runtime

# Backup module configuration
cp protected/modules/spaceJoinQuestions/config.php backup_config_$(date +%Y%m%d).php
```

## 🔄 Upgrading

### From Previous Versions

#### 1. Pre-Upgrade Steps
```bash
# Backup current installation
tar -czf pre_upgrade_backup_$(date +%Y%m%d).tar.gz .
mysqldump -u username -p database_name > pre_upgrade_db_$(date +%Y%m%d).sql

# Check current version
php protected/yii module/list | grep space-join-questions
```

#### 2. Upgrade Process
```bash
# Disable module temporarily
php protected/yii module/disable space-join-questions

# Update module files
rm -rf protected/modules/spaceJoinQuestions
cp -r new_spaceJoinQuestions protected/modules/

# Run migrations
php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations

# Re-enable module
php protected/yii module/enable space-join-questions

# Clear cache
php protected/yii cache/flush-all
```

#### 3. Post-Upgrade Verification
- [ ] All functionality works as expected
- [ ] Email notifications still working
- [ ] No errors in logs
- [ ] Database integrity maintained

## 🚨 Troubleshooting

### Common Issues

#### 1. Module Not Appearing
**Symptoms**: Module not listed in admin panel
**Solutions**:
```bash
# Check file permissions
ls -la protected/modules/spaceJoinQuestions/

# Clear cache
php protected/yii cache/flush-all

# Check module configuration
php protected/yii module/list
```

#### 2. Email Not Sending
**Symptoms**: No email notifications received
**Solutions**:
```bash
# Check mailer configuration
grep -A 10 "mailer" protected/config/common.php

# Test email manually
php test_email.php

# Check mailer logs
tail -f protected/runtime/logs/app.log | grep -i mail
```

#### 3. Database Errors
**Symptoms**: Migration errors or missing tables
**Solutions**:
```bash
# Check database connection
php protected/yii database/check

# Run migrations manually
php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations

# Check table structure
mysql -u username -p database_name -e "DESCRIBE space_join_application;"
```

#### 4. Application Form Not Loading
**Symptoms**: 404 errors or blank pages
**Solutions**:
```bash
# Check URL rewriting
curl -I http://your-domain.com/space-join-questions/application/create

# Verify controller exists
ls -la protected/modules/spaceJoinQuestions/controllers/

# Check route configuration
php protected/yii route/list | grep space-join-questions
```

### Emergency Rollback

If critical issues occur, rollback to previous version:

```bash
# Restore from backup
cp backups/MembershipController.php protected/humhub/modules/space/controllers/
cp backups/about.php protected/humhub/modules/space/views/space/

# Disable module
php protected/yii module/disable space-join-questions

# Restore database if needed
mysql -u username -p database_name < backup_$(date +%Y%m%d).sql

# Clear cache
php protected/yii cache/flush-all
```

## 📞 Support

### Getting Help
1. **Check Documentation**: Review README.md and inline comments
2. **Review Logs**: Check `protected/runtime/logs/` for error messages
3. **Community Support**: Post issues on HumHub community forums
4. **Debug Mode**: Enable debug mode for detailed error information

### Reporting Issues
When reporting issues, include:
- HumHub version and PHP version
- Module version and installation method
- Error messages from logs
- Steps to reproduce the issue
- Screenshots if applicable

---

**Last Updated**: July 2025  
**Module Version**: 2.0.0  
**Compatible with**: HumHub 1.17.3+  
**Status**: Production Ready ✅ 