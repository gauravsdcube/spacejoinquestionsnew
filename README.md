# Space Join Questions Module v2.3.0

A HumHub module that allows space administrators to create custom questions for users joining their spaces.

## Features

- **Custom Join Questions**: Create personalized questions for space join requests
- **Email Templates**: Rich text email templates with link support
- **Notification Recipients**: Configure who receives join request notifications
- **Admin Interface**: Easy-to-use admin panel for configuration

## Installation

1. Download the module files
2. Place the `space-join-questions` folder in your HumHub `protected/modules/` directory
3. Enable the module in HumHub admin panel
4. Configure your questions and email templates

## Version 2.3.0 Changes

### 🔧 Fixed
- **Email Link Rendering**: Fixed links not appearing in email templates by using RichTextToEmailHtmlConverter
- **Rich Text Editor Links**: Enabled link button in rich text editor for email template editing
- **Email Template Processing**: Improved with proper token generation for secure file access
- **Code Quality**: Removed redundant file processing methods and debug logging

### ✨ Improved
- **Email Client Compatibility**: Better link handling for various email clients
- **Template Editor**: Enhanced functionality for email template editing
- **Code Maintainability**: Cleaner, more maintainable codebase

## Requirements

- HumHub 1.8+
- PHP 7.4+

## License

This module is licensed under the MIT License.

## Support

For support and bug reports, please create an issue in the repository.
