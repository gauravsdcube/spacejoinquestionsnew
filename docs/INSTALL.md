# Installation Guide - Space Join Questions Module

This guide provides detailed installation instructions for the Space Join Questions module for HumHub.

## Prerequisites

Before installing the module, ensure your system meets the following requirements:

### System Requirements
- **HumHub Version**: 1.15.0 or higher
- **PHP Version**: 7.4 or higher (8.1+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache or Nginx with mod_rewrite enabled
- **PHP Extensions**: All standard HumHub requirements

### HumHub Requirements
- Functioning HumHub installation
- Administrative access to HumHub
- Queue system configured (recommended for notifications)

## Installation Methods

### Method 1: Manual Installation (Recommended)

1. **Download the Module**
   - Download the latest release from the repository
   - Extract the archive to get the `spaceJoinQuestions` folder

2. **Upload Module Files**
   ```bash
   # Navigate to your HumHub installation
   cd /path/to/humhub
   
   # Create modules directory if it doesn't exist
   mkdir -p protected/modules
   
   # Upload the module folder
   cp -r /path/to/spaceJoinQuestions protected/modules/
   ```

3. **Set File Permissions**
   ```bash
   # Set correct ownership (replace www-data with your web server user)
   sudo chown -R www-data:www-data protected/modules/spaceJoinQuestions/
   
   # Set proper file permissions
   sudo find protected/modules/spaceJoinQuestions/ -type f -exec chmod 644 {} \;
   sudo find protected/modules/spaceJoinQuestions/ -type d -exec chmod 755 {} \;
   ```

4. **Enable the Module**
   - Log in to HumHub as an administrator
   - Go to `Administration → Modules`
   - Find "Space Join Questions" in the available modules list
   - Click "Enable" to activate the module

5. **Verify Installation**
   - Check that database tables were created successfully
   - Ensure no error messages appear in the logs
   - Test basic functionality in a space

### Method 2: Git Installation (For Developers)

1. **Clone Repository**
   ```bash
   cd /path/to/humhub/protected/modules
   git clone https://github.com/yourusername/humhub-space-join-questions.git spaceJoinQuestions
   ```

2. **Set Permissions and Enable**
   Follow steps 3-5 from Method 1

## Post-Installation Configuration

### 1. Enable Module Per Space

The module must be enabled individually for each space:

1. **Navigate to Space**
   - Go to the space where you want to use custom questions
   - Access the space admin panel

2. **Enable Module**
   - Click on "Modules" in the space admin menu
   - Find "Space Join Questions"
   - Click "Enable" for this space

3. **Configure Permissions** (Optional)
   - Go to `Space Admin → Permissions`
   - Adjust permissions for "Manage Join Questions" and "View Membership Applications"
   - By default, only space administrators can manage questions

### 2. Configure Email Notifications

1. **Space-Level Settings**
   - Go to `Space Admin → Join Questions → Settings`
   - Enable/disable email notifications for new applications
   - Configure per your space's needs

2. **Global HumHub Settings**
   - Ensure HumHub's email system is configured
   - Check that the queue system is running for background notifications

### 3. Test the Installation

1. **Create Test Questions**
   - Go to `Space Admin → Join Questions`
   - Click "Add Question"
   - Create a few test questions with different field types
   - Set some as required, others as optional

2. **Test User Experience**
   - Log in with a different user account
   - Try to join the space
   - Verify that custom questions appear in the join form
   - Submit the form and check validation

3. **Test Admin Workflow**
   - Check that the application appears in "Membership Applications"
   - Review submitted answers
   - Test approval and decline functionality
   - Verify notifications are sent

## Troubleshooting Installation Issues

### Common Issues

#### 1. Module Not Appearing in Admin Panel

**Symptoms**: Module doesn't show up in Administration → Modules

**Solutions**:
```bash
# Check file permissions
ls -la protected/modules/spaceJoinQuestions/

# Verify module.json exists and is valid
cat protected/modules/spaceJoinQuestions/module.json

# Clear HumHub cache
php protected/yii cache/flush-all
```

#### 2. Database Migration Errors

**Symptoms**: Error messages during module enabling about database tables

**Solutions**:
```bash
# Check database connection
php protected/yii db/check

# Run migrations manually
php protected/yii migrate/up --migrationPath=protected/modules/spaceJoinQuestions/migrations

# Check migration status
php protected/yii migrate/history --migrationPath=protected/modules/spaceJoinQuestions/migrations
```

#### 3. Permission Errors

**Symptoms**: "Access denied" or 403 errors when accessing module

**Solutions**:
```bash
# Fix file ownership
sudo chown -R www-data:www-data protected/modules/spaceJoinQuestions/

# Fix directory permissions
sudo find protected/modules/spaceJoinQuestions/ -type d -exec chmod 755 {} \;

# Fix file permissions
sudo find protected/modules/spaceJoinQuestions/ -type f -exec chmod 644 {} \;
```

#### 4. Module Conflicts

**Symptoms**: Module causes errors with other modules or HumHub core

**Solutions**:
- Check HumHub logs: `protected/runtime/logs/app.log`
- Disable conflicting modules temporarily
- Ensure HumHub version compatibility
- Check PHP error logs

### Verification Commands

```bash
# Check module is installed
php protected/yii module/list | grep space-join-questions

# Check database tables exist
php protected/yii db/query "SHOW TABLES LIKE 'space_join_%'"

# Check permissions
php protected/yii module/info space-join-questions

# Test database connection
php protected/yii db/check
```

## Uninstallation

If you need to remove the module:

### 1. Disable Module
```bash
# Disable via command line
php protected/yii module/disable space-join-questions

# Or disable via web interface:
# Administration → Modules → Space Join Questions → Disable
```

### 2. Clean Up Data (Optional)
```bash
# Run uninstall migration to clean up data
php protected/yii migrate/up --migrationPath=protected/modules/spaceJoinQuestions/migrations
```

### 3. Remove Files
```bash
# Remove module directory
rm -rf protected/modules/spaceJoinQuestions/
```

**Warning**: Uninstalling will permanently delete all custom questions, answers, and related data.

## Advanced Configuration

### Custom Module Path

If you need to install the module in a custom location:

1. **Configure Module Path**
   ```php
   // In protected/config/common.php
   return [
       'params' => [
           'moduleAutoloadPaths' => [
               '/custom/path/to/modules',
           ],
       ],
   ];
   ```

2. **Install Module**
   ```bash
   cp -r spaceJoinQuestions /custom/path/to/modules/
   ```

### Production Deployment

For production environments:

1. **Test in Staging First**
   - Always test the module in a staging environment
   - Verify all functionality works as expected
   - Test with real user scenarios

2. **Backup Before Installation**
   ```bash
   # Backup database
   mysqldump -u [username] -p [database] > backup-$(date +%Y%m%d).sql
   
   # Backup HumHub files
   tar -czf humhub-backup-$(date +%Y%m%d).tar.gz /path/to/humhub
   ```

3. **Monitor After Installation**
   - Check error logs regularly
   - Monitor performance impact
   - Gather user feedback

## Support

If you encounter issues during installation:

1. **Check Documentation**
   - Review this installation guide
   - Check the main README.md
   - Look at troubleshooting sections

2. **Check Logs**
   - HumHub application logs: `protected/runtime/logs/app.log`
   - Web server error logs
   - PHP error logs

3. **Get Help**
   - Create an issue on the GitHub repository
   - Provide detailed error messages and system information
   - Include relevant log entries

## Next Steps

After successful installation:

1. **Read the User Guide**: Familiarize yourself with all features
2. **Configure Permissions**: Set up appropriate user permissions
3. **Create Questions**: Start creating custom questions for your spaces
4. **Train Users**: Help space administrators understand the new features
5. **Monitor Usage**: Keep track of how the module is being used

The module is now ready for use! Proceed to create your first custom questions and start enhancing your space membership process.