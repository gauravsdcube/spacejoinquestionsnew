# Space Join Questions Module

A comprehensive HumHub module that allows space administrators to create custom questions for membership requests with a complete approval workflow and notification system.

## Features

### For Space Administrators
- **Custom Questions Management**: Create, edit, and delete custom questions for membership requests
- **Question Types**: Support for text input, textarea, dropdown, radio buttons, and checkbox fields
- **Question Ordering**: Drag-and-drop sorting of questions
- **Required/Optional**: Mark questions as mandatory or optional
- **Application Review**: View all membership applications with submitted answers
- **Approval Workflow**: Accept or decline applications with custom decline reasons
- **Email Notifications**: Configurable email notifications for new applications

### For Users
- **Enhanced Join Form**: Custom questions are seamlessly integrated into the membership request process
- **Validation**: Real-time validation ensures required questions are answered
- **Status Tracking**: View application status and submitted answers
- **Notifications**: Receive notifications when applications are accepted or declined with reasons

### Technical Features
- **No Core Modifications**: Built using HumHub's event system and extension points
- **Database-Driven**: All data stored in proper relational database tables
- **Permission-Based**: Granular permissions for question management and application viewing
- **Translation Ready**: Full internationalization support
- **Mobile Responsive**: Works perfectly on all device sizes

## Requirements

- HumHub 1.15.0 or higher
- PHP 7.4 or higher
- MySQL/MariaDB database

## Installation

1. **Download the module** and extract it to your HumHub modules directory:
   ```
   /protected/modules/spaceJoinQuestions/
   ```

2. **Enable the module** in the HumHub admin panel:
   - Go to `Administration → Modules`
   - Find "Space Join Questions" and click "Enable"

3. **Enable per space**: The module must be enabled individually for each space where you want to use custom questions:
   - Go to the space admin panel
   - Navigate to `Modules`
   - Enable "Space Join Questions"

## Usage

### Setting Up Questions

1. **Access Question Management**:
   - Go to your space admin panel
   - Click on "Join Questions" in the admin menu

2. **Create Questions**:
   - Click "Add Question"
   - Enter your question text
   - Choose the field type (text, textarea, dropdown, etc.)
   - Set whether the question is required
   - Add options for dropdown/radio button fields
   - Save the question

3. **Organize Questions**:
   - Drag and drop questions to change their order
   - Edit or delete questions as needed

### Managing Applications

1. **View Applications**:
   - Go to "Membership Applications" in the space admin menu
   - See all pending applications with submitted answers

2. **Review Applications**:
   - Click on an application to view detailed answers
   - Review the user's responses to your custom questions

3. **Make Decisions**:
   - **Accept**: Click "Approve" to grant membership
   - **Decline**: Click "Decline" and provide a reason for the rejection

### User Experience

1. **Joining a Space**:
   - Users click "Join" on spaces with custom questions
   - A form appears with the custom questions
   - Required questions must be answered before submission

2. **Application Status**:
   - Users can view their application status and submitted answers
   - Notifications are sent when applications are accepted or declined

## Configuration

### Email Notifications

Administrators can configure email notifications:

1. Go to `Space Admin → Join Questions → Settings`
2. Enable/disable email notifications for new applications
3. Configure notification preferences per space

### Permissions

The module includes two permissions:

- **Manage Join Questions**: Create, edit, and delete questions (Admin only)
- **View Membership Applications**: View and approve/decline applications (Admin/Moderator)

## Field Types

### Text Input
Simple single-line text input for short answers.

### Text Area
Multi-line text input for longer responses.

### Dropdown
Select one option from a predefined list.
- Requires options to be configured
- One option per line in the options field

### Radio Buttons
Choose one option from multiple choices displayed as radio buttons.
- Requires options to be configured
- One option per line in the options field

### Checkbox
Simple yes/no or agreement checkbox.

## Database Schema

The module creates three main tables:

### space_join_question
Stores the custom questions for each space.

### space_join_answer
Stores user responses to questions.

### space_join_decline_reason
Stores decline reasons for rejected applications.

## Troubleshooting

### Questions Don't Appear
- Ensure the module is enabled for the specific space
- Check that questions are created and saved properly
- Verify the user has permission to join the space

### Notifications Not Working
- Check email notification settings in the space configuration
- Ensure HumHub's queue system is running for background notifications
- Verify user notification preferences

### Permission Issues
- Ensure proper permissions are assigned to user groups
- Check space-level permissions for the module

## Support

For support, bug reports, or feature requests:

- Check the [GitHub repository](https://github.com/yourusername/humhub-space-join-questions)
- Review the [documentation](docs/)
- Contact the module developer

## License

This module is released under the MIT License. See LICENSE file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.