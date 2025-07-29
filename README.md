# Space Join Questions Module

A production-ready HumHub module that adds a comprehensive space membership application system with advanced email templates and rich text editing capabilities.

**Copyright © 2025 D Cube Consulting Ltd. All rights reserved.**

## 🚀 Version: 2.0.0 (Production Ready)

### Overview

This module enhances HumHub's space functionality by adding a formal application process for space membership. Users must submit an application with custom questions before joining a space, and space administrators can approve or decline applications with personalized email notifications featuring rich text editing and professional email templates.

## ✨ Features

### Core Functionality
- **Custom Application Questions**: Space administrators can define specific questions for membership applications
- **Application Management**: Admin interface to review, approve, and decline applications
- **Advanced Email Notifications**: Professional email templates with rich text editing
- **Personalized Content**: Emails include recipient's first name and custom decline reasons
- **Professional Email Design**: Clean, branded emails without marketing elements

### 🎨 Rich Text Email Editor
- **Advanced Rich Text Editing**: Full-featured rich text editor for email templates
- **Header & Footer Customization**: Separate rich text editors for email headers and footers
- **Color Customization**: Custom background and font colors for headers and footers
- **Image Support**: Upload and embed images directly in email templates
- **Table Support**: Create and edit tables within email content
- **Formatting Options**: Bold, italic, lists, links, and other rich text features
- **Variable Substitution**: Dynamic content replacement for personalized emails
- **Live Preview**: Real-time preview of email templates with sample data

### Email Template Features
- **Three Template Types**:
  - Application Received (to space administrators)
  - Application Accepted (to applicants)
  - Application Declined (to applicants with custom reasons)
- **Professional Design**: Clean, responsive email layouts
- **Branding Support**: Custom colors and styling options
- **Mobile Responsive**: Optimized for all device types
- **File Handling**: Automatic image processing and public URL generation
- **Token Security**: Secure file access with temporary tokens

### Admin Features
- **Application Dashboard**: Centralized view of all pending applications
- **Individual Application Review**: Detailed view of each application with approve/decline actions
- **Custom Decline Reasons**: Ability to provide specific feedback when declining applications
- **Bulk Actions**: Process multiple applications efficiently
- **Email Template Management**: Full control over email content and styling
- **Template Preview**: Live preview of email templates before sending

### Email Template Management
- **Template Editor**: Rich text editor for email content
- **Color Customization**: Background and font color pickers
- **Variable System**: Dynamic content replacement
- **Template Reset**: Restore default templates when needed
- **Active/Inactive Toggle**: Enable/disable specific email templates
- **Professional Layout**: Clean, branded email design

## 📋 Requirements

- **HumHub Version**: 1.17.3 or higher
- **PHP Version**: 8.0 or higher (tested with PHP 8.4)
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Permissions**: Admin access to install and configure the module
- **Browser Support**: Modern browsers with JavaScript enabled for rich text editing

## 🛠️ Installation

### Method 1: Manual Installation (Recommended)

1. **Download the Module**
   ```bash
   # Navigate to your HumHub installation
   cd /path/to/humhub
   
   # Create the modules directory if it doesn't exist
   mkdir -p protected/modules
   
   # Copy the module files
   cp -r spaceJoinQuestions protected/modules/
   ```

2. **Set Proper Permissions**
   ```bash
   # Set correct ownership and permissions
   chown -R www-data:www-data protected/modules/spaceJoinQuestions
   chmod -R 755 protected/modules/spaceJoinQuestions
   ```

3. **Enable the Module**
   ```bash
   # Via CLI (if available)
   php protected/yii module/enable space-join-questions
   
   # Or via web interface
   # Go to Administration > Modules > Space Join Questions > Enable
   ```

4. **Run Database Migrations**
   ```bash
   php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations
   ```

### Method 2: Web Interface Installation

1. **Upload Module Files**
   - Upload the `spaceJoinQuestions` folder to `protected/modules/`
   - Ensure proper file permissions (755 for directories, 644 for files)

2. **Enable via Admin Panel**
   - Log in as administrator
   - Go to **Administration** → **Modules**
   - Find **Space Join Questions** and click **Enable**

3. **Configure Space Settings**
   - Go to any space's **Settings** → **General**
   - Enable **"Require application for membership"**
   - Add custom questions as needed

## ⚙️ Configuration

### Space-Level Configuration

1. **Enable Application Requirement**
   - Navigate to Space Settings → General
   - Check "Require application for membership"
   - Save settings

2. **Configure Application Questions**
   - Add custom questions that applicants must answer
   - Questions can be required or optional
   - Support for multiple question types (text, textarea, etc.)

3. **Email Template Configuration**
   - Access Email Templates via Space Settings
   - Customize header, body, and footer content
   - Set custom colors for headers and footers
   - Preview templates before saving

### Email Configuration

The module uses HumHub's default mailer configuration. Ensure your mailer is properly configured in:

```
protected/config/common.php
```

Example mailer configuration:
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'your-smtp-host.com',
        'username' => 'your-email@domain.com',
        'password' => 'your-password',
        'port' => '587',
        'encryption' => 'tls',
    ],
],
```

## 🎨 Rich Text Editor Features

### Email Template Editor
- **Rich Text Formatting**: Bold, italic, underline, strikethrough
- **Text Alignment**: Left, center, right, justify
- **Lists**: Ordered and unordered lists with nesting
- **Links**: Internal and external link management
- **Images**: Upload and embed images with alt text
- **Tables**: Create and edit tables with custom styling
- **Code Blocks**: Syntax highlighting for code snippets
- **Quotes**: Blockquote formatting
- **Headings**: Multiple heading levels (H1-H6)

### Color Customization
- **Header Colors**: Background and font color pickers
- **Footer Colors**: Background and font color pickers
- **Hex Code Input**: Direct hex color code entry
- **Color Preview**: Live preview of color changes
- **Default Colors**: Professional default color schemes

### Variable System
Available variables for dynamic content:
- `{admin_name}` - Name of the space administrator
- `{user_name}` - Name of the applicant
- `{user_email}` - Email of the applicant
- `{space_name}` - Name of the space
- `{application_date}` - Date when application was submitted
- `{application_answers}` - Answers provided by the applicant
- `{accepted_date}` - Date when application was accepted
- `{declined_date}` - Date when application was declined
- `{decline_reason}` - Custom reason for declining
- `{admin_notes}` - Additional notes from administrator

## 📧 Email Template System

### Template Types

1. **Application Received** (to space admins)
   - Subject: "New membership application for {spaceName}"
   - Content: Notification about new application with applicant details
   - Variables: admin_name, user_name, user_email, space_name, application_date, application_answers

2. **Application Accepted** (to applicants)
   - Subject: "Your membership application for {spaceName} has been accepted"
   - Content: Personalized acceptance message with first name
   - Variables: user_name, space_name, accepted_date, admin_notes

3. **Application Declined** (to applicants)
   - Subject: "Your membership application for {spaceName} has been declined"
   - Content: Personalized decline message with custom reason
   - Variables: user_name, space_name, declined_date, decline_reason, admin_notes

### Professional Email Design
- **Clean Layout**: Professional email structure
- **Responsive Design**: Optimized for all devices
- **Brand Consistency**: Uses HumHub's color scheme
- **No Marketing Elements**: Removes default HumHub marketing content
- **Custom Styling**: Header and footer color customization
- **Image Support**: Proper image handling and display

### File Handling
- **Image Upload**: Direct upload through rich text editor
- **Public URLs**: Automatic generation of public image URLs
- **Token Security**: Secure file access with temporary tokens
- **Email Optimization**: Images optimized for email delivery
- **Fallback Support**: Graceful handling of missing images

## 🔧 Core HumHub Changes

### Modified Files

#### 1. `protected/humhub/modules/space/controllers/MembershipController.php`
**Purpose**: Override default membership behavior to require applications

**Changes Made**:
- Added application requirement check before allowing direct membership
- Redirect users to application form when applications are required
- Maintain backward compatibility with existing spaces

**Backup Recommendation**: 
```bash
cp protected/humhub/modules/space/controllers/MembershipController.php protected/humhub/modules/space/controllers/MembershipController.php.backup
```

#### 2. `protected/humhub/modules/space/views/membership/index.php`
**Purpose**: Update membership view to show application status

**Changes Made**:
- Added application status display for pending applications
- Show different UI for applicants vs. members
- Display application submission date and status

**Backup Recommendation**:
```bash
cp protected/humhub/modules/space/views/membership/index.php protected/humhub/modules/space/views/membership/index.php.backup
```

### Database Changes

The module creates the following database tables:

- `space_join_application`: Stores application data and responses
- `space_join_question`: Stores custom questions for each space
- `space_join_question_response`: Stores individual question responses
- `space_join_email_template`: Stores email templates with rich text content
- `space_join_decline_reason`: Stores custom decline reasons

## 🔍 Troubleshooting

### Known Issues

#### 1. Membership Request Form Modal Experience
**Issue**: When accessing the membership request form, it appears as a modal overlay with the space UI visible behind it, which provides a poor user experience.

**Current Status**: This is a known limitation in the current implementation.

**Workaround**: 
- Users can close the modal and access the form through the space membership page
- The form is also accessible via direct URL: `/space-join-questions/membership/apply?cguid={space_guid}`

**Future Enhancement**: This will be addressed in a future version to provide a better full-page experience.

### Common Issues

#### 2. Rich Text Editor Not Loading
**Symptoms**: Rich text editor doesn't appear or shows as plain text
**Solutions**:
- Check JavaScript console for errors
- Ensure browser supports modern JavaScript features
- Clear browser cache and reload page
- Check if any browser extensions are blocking JavaScript

#### 3. Images Not Displaying in Emails
**Symptoms**: Images appear broken in email templates
**Solutions**:
- Check file permissions on uploads directory
- Verify public URL generation is working
- Check email client image blocking settings
- Ensure proper image format (JPG, PNG, GIF)

#### 4. Emails Not Sending
**Symptoms**: No email notifications received
**Solutions**:
- Check mailer configuration in `protected/config/common.php`
- Verify SMTP settings and credentials
- Check server logs for mailer errors
- Ensure `useFileTransport` is set to `false`

#### 5. Module Not Appearing
**Symptoms**: Module not listed in admin panel
**Solutions**:
- Verify file permissions (755 for directories, 644 for files)
- Check module directory structure
- Clear HumHub cache: `php protected/yii cache/flush-all`
- Restart web server

#### 6. Database Migration Errors
**Symptoms**: Error during module installation
**Solutions**:
- Check database permissions
- Verify MySQL/MariaDB version compatibility
- Run migrations manually: `php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations`

#### 7. Application Form Not Loading
**Symptoms**: 404 error or blank page when accessing application form
**Solutions**:
- Check URL rewriting configuration
- Verify controller file permissions
- Clear browser cache and HumHub cache

### Debug Mode

Enable debug mode to see detailed error messages:

```php
// In protected/config/common.php
'components' => [
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning', 'info'],
                'logVars' => [],
            ],
        ],
    ],
],
```

## 🔄 Upgrading

### From Previous Versions

1. **Backup Current Installation**
   ```bash
   # Backup database
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
   
   # Backup files
   tar -czf humhub_backup_$(date +%Y%m%d).tar.gz .
   ```

2. **Update Module Files**
   ```bash
   # Replace module directory
   rm -rf protected/modules/spaceJoinQuestions
   cp -r new_spaceJoinQuestions protected/modules/
   ```

3. **Run Migrations**
   ```bash
   php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations
   ```

4. **Clear Cache**
   ```bash
   php protected/yii cache/flush-all
   ```

## 🧪 Testing

### Manual Testing Checklist

- [ ] Module installs without errors
- [ ] Application form displays correctly (note: currently shows as modal)
- [ ] Applications are saved to database
- [ ] Admin can view pending applications
- [ ] Admin can approve applications
- [ ] Admin can decline applications with custom reason
- [ ] Email notifications are sent for all events
- [ ] Email content is personalized
- [ ] Rich text editor loads and functions properly
- [ ] Email templates can be edited with rich text
- [ ] Color customization works correctly
- [ ] Image upload and display works in emails
- [ ] Variable substitution works in templates
- [ ] Email design is professional (no marketing elements)
- [ ] Application status updates correctly
- [ ] Users can see their application status
- [ ] Template preview shows correct formatting
- [ ] File handling works with secure tokens

### Automated Testing

Run the module's test suite:
```bash
php protected/vendor/bin/codecept run --config protected/tests/codeception.yml unit SpaceJoinQuestions
```

## 📝 Changelog

### Version 2.0.0 (Production Ready)
- ✅ **Added**: Advanced rich text editor for email templates
- ✅ **Added**: Header and footer customization with color pickers
- ✅ **Added**: Image upload and embedding in email templates
- ✅ **Added**: Table support in rich text editor
- ✅ **Added**: Variable substitution system for dynamic content
- ✅ **Added**: Live preview functionality for email templates
- ✅ **Added**: Professional email design with custom styling
- ✅ **Added**: File handling with secure token system
- ✅ **Fixed**: Email notification system with professional design
- ✅ **Added**: Personalized email content with first names
- ✅ **Added**: Custom decline reasons in email notifications
- ✅ **Fixed**: Membership deletion issue during decline process
- ✅ **Improved**: Error handling and logging
- ✅ **Added**: Comprehensive documentation
- ✅ **Tested**: PHP 8.4 compatibility

### Version 1.0.0 (Initial Release)
- ✅ Basic application system
- ✅ Custom questions support
- ✅ Admin interface
- ✅ Basic email notifications

## 🤝 Support

### Getting Help

1. **Check Documentation**: Review this README and inline code comments
2. **Review Logs**: Check `protected/runtime/logs/` for error messages
3. **Community Support**: Post issues on HumHub community forums
4. **Debug Mode**: Enable debug mode for detailed error information

### Reporting Issues

When reporting issues, please include:
- HumHub version
- PHP version
- Module version
- Browser type and version
- Error messages from logs
- Steps to reproduce the issue
- Screenshots if applicable
- JavaScript console errors (for rich text editor issues)

## 📄 License

This module is released under the same license as HumHub (Apache License 2.0).

## 🙏 Acknowledgments

- HumHub development team for the excellent framework
- Community contributors for testing and feedback
- All users who provided feedback during development
- ProseMirror team for the rich text editor foundation

---

**Copyright © 2025 D Cube Consulting Ltd. All rights reserved.**

**Last Updated**: July 2025  
**Compatible with**: HumHub 1.17.3+  
**PHP Version**: 8.0+  
**Status**: Production Ready ✅  
**Rich Text Editor**: ProseMirror-based ✅ 