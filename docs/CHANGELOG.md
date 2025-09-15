# Changelog

All notable changes to the Space Join Questions module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2025-01-15

### ✨ Added
- **Simplified Notification Recipients**: New checkbox-based interface for selecting space administrators as email notification recipients
- **Space Admin Integration**: Automatic detection and listing of space administrators (including space owner)
- **Visual Role Indicators**: Clear labeling of space owners vs administrators in the recipient selection interface

### 🔧 Changed
- **Improved User Experience**: Replaced complex user search functionality with simple checkbox selection
- **Better Performance**: Removed AJAX-based user search that was causing database query issues
- **Cleaner Interface**: Streamlined notification recipient management with intuitive checkbox interface

### 🗑️ Removed
- **User Search Functionality**: Removed the problematic user search feature that was causing 404 errors
- **Add/Remove Recipient Actions**: Simplified to single save action for all recipient changes
- **Complex JavaScript**: Removed unnecessary JavaScript files and AJAX functionality
- **Debug Logging**: Cleaned up excessive debug logging from user search functionality

### 🐛 Fixed
- **404 Errors**: Fixed URL routing issues with notification recipient management
- **Content Security Policy**: Resolved CSP violations from inline JavaScript
- **Database Queries**: Fixed user search queries that were incorrectly joining tables
- **Asset Loading**: Resolved issues with missing CSS/JS files and incorrect asset paths

## [2.0.0] - 2025-01-XX

### 🎉 Complete Redesign
This version represents a complete rewrite of the module following HumHub best practices.

### ✨ Added
- **No Core File Modifications**: Complete removal of all core HumHub file modifications
- **ContentContainerModule**: Proper space-level module implementation
- **Enhanced Question Types**: Support for text, textarea, dropdown, radio buttons, and checkbox fields
- **Question Management**: Full CRUD operations for questions with drag-and-drop sorting
- **Application Review System**: Comprehensive application management with detailed answer views
- **Decline Reasons**: Custom decline reasons with predefined options
- **Advanced Notifications**: Proper notification system without serialization issues
- **Permission System**: Granular permissions for question management and application viewing
- **Email Notifications**: Configurable email notifications for new applications
- **Mobile Responsive**: Full mobile compatibility
- **Translation Support**: Complete internationalization framework
- **Database Integrity**: Proper foreign keys and relational database design

### 🔧 Technical Improvements
- **Event-Driven Architecture**: Uses HumHub's event system for seamless integration
- **Widget-Based UI**: Reusable components for forms and displays
- **Proper Model Behaviors**: TimestampBehavior and BlameableBehavior implementation
- **Migration System**: Safe database migrations with rollback support
- **Error Handling**: Comprehensive error handling and validation
- **Performance Optimized**: Efficient database queries and caching

### 🛡️ Security
- **Input Validation**: Comprehensive validation for all user inputs
- **Permission Checks**: Proper authorization checks throughout the module
- **SQL Injection Protection**: Parameterized queries and ActiveRecord usage
- **XSS Prevention**: HTML encoding and sanitization

### 📱 User Experience
- **Intuitive Interface**: Clean, modern admin interface
- **Real-time Validation**: Client-side validation for better user experience
- **Status Tracking**: Users can view their application status and answers
- **Responsive Design**: Works perfectly on all device sizes

### 🔄 Migration from v1.x
- **Automatic Data Migration**: Seamless upgrade from previous versions
- **Backward Compatibility**: Existing questions and answers are preserved
- **Configuration Migration**: Settings are automatically migrated

### 🐛 Fixed
- **Core File Dependencies**: Eliminated all core file modifications
- **Notification Issues**: Fixed notification serialization problems
- **Permission Problems**: Resolved access control issues
- **Database Consistency**: Fixed foreign key constraints and data integrity
- **Mobile Issues**: Resolved mobile compatibility problems

### ⚠️ Breaking Changes
- **Minimum HumHub Version**: Now requires HumHub 1.15.0+
- **Core File Changes**: All core file modifications have been removed
- **Database Schema**: New table structure (automatically migrated)
- **API Changes**: Internal API has been restructured

## [1.0.7] - 2024-XX-XX

### Fixed
- Fixed menu display issues
- Improved notification reliability

## [1.0.6] - 2024-XX-XX

### Added
- Email notification settings
- Enhanced decline reason functionality

### Fixed
- Notification payload serialization issues
- Menu item display problems

## [1.0.5] - 2024-XX-XX

### Added
- Comprehensive documentation for core file modifications
- Improved error handling
- Better PHP 8.1+ compatibility

### Changed
- Enhanced notification system
- Improved user interface

## [1.0.4] - 2024-XX-XX

### Fixed
- Menu display issues in space admin panel
- Notification rendering problems

## [1.0.3] - 2024-XX-XX

### Fixed
- Email notification settings not saving
- Template rendering issues

## [1.0.2] - 2024-XX-XX

### Added
- Email notification settings feature
- Improved admin interface

### Fixed
- Database migration issues
- Permission problems

## [1.0.1] - 2024-XX-XX

### Added
- Decline reasons in notifications (required core file changes)
- Enhanced email templates

### Fixed
- Notification payload issues
- Core file modification requirements

## [1.0.0] - 2024-XX-XX

### 🎉 Initial Release

### Added
- Basic custom question functionality
- Simple approval/decline workflow
- Email notifications
- Space admin integration

### Known Issues
- Required core HumHub file modifications
- Limited notification system
- Basic user interface

---

## Upgrade Guide

### From v1.x to v2.0.0

#### Before Upgrading
1. **Backup your installation** completely
2. **Document any core file changes** you made for v1.x
3. **Test in staging environment** first

#### Upgrade Steps
1. **Disable the old module**:
   ```bash
   php protected/yii module/disable space-join-questions
   ```

2. **Remove core file modifications**:
   - Revert any changes to `protected/humhub/modules/notification/components/BaseNotification.php`
   - Revert any changes to `protected/config/common.php`

3. **Install v2.0.0**:
   - Replace module files with v2.0.0
   - Enable the module
   - Run migrations automatically

4. **Verify functionality**:
   - Test question management
   - Test membership requests
   - Verify notifications work

#### What's Migrated Automatically
- ✅ Existing questions and their settings
- ✅ Historical answers (preserved for reference)
- ✅ Space-level module settings
- ✅ Permission configurations

#### Manual Steps Required
- 🔧 Remove any core file modifications
- 🔧 Re-enable module per space if needed
- 🔧 Update any custom templates or overrides

## Support

For upgrade assistance or issues:

1. **Check the documentation** in the `docs/` folder
2. **Review the troubleshooting guide** in `INSTALL.md`
3. **Create an issue** on GitHub with:
   - Current version information
   - Detailed error messages
   - Steps to reproduce the problem
   - System information (HumHub version, PHP version, etc.)

## Contributors

Thanks to all contributors who helped make this module better:

- **Version 2.0.0**: Complete rewrite following HumHub best practices
- **Version 1.x**: Initial implementation and feature development

---

**Note**: This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format. Each version includes clear categories (Added, Changed, Deprecated, Removed, Fixed, Security) to help users understand the impact of updates.