#!/bin/bash

# Rollback script for email template link fix
# This script restores the original files if the link fix causes issues

echo "Starting rollback of email template link fix..."

# Get the most recent backup files
EMAIL_TEMPLATE_BACKUP=$(ls -t /var/www/humhub/protected/modules/space-join-questions/models/EmailTemplate.php.backup.* | head -1)
CONTROLLER_BACKUP=$(ls -t /var/www/humhub/protected/modules/space-join-questions/controllers/EmailTemplateController.php.backup.* | head -1)
EVENTS_BACKUP=$(ls -t /var/www/humhub/protected/modules/space-join-questions/Events.php.backup.* | head -1)

echo "Found backup files:"
echo "EmailTemplate: $EMAIL_TEMPLATE_BACKUP"
echo "Controller: $CONTROLLER_BACKUP"
echo "Events: $EVENTS_BACKUP"

# Restore files
if [ -f "$EMAIL_TEMPLATE_BACKUP" ]; then
    echo "Restoring EmailTemplate.php..."
    sudo cp "$EMAIL_TEMPLATE_BACKUP" /var/www/humhub/protected/modules/space-join-questions/models/EmailTemplate.php
    sudo chown www-data:www-data /var/www/humhub/protected/modules/space-join-questions/models/EmailTemplate.php
    echo "EmailTemplate.php restored"
else
    echo "ERROR: EmailTemplate backup not found!"
    exit 1
fi

if [ -f "$CONTROLLER_BACKUP" ]; then
    echo "Restoring EmailTemplateController.php..."
    sudo cp "$CONTROLLER_BACKUP" /var/www/humhub/protected/modules/space-join-questions/controllers/EmailTemplateController.php
    sudo chown www-data:www-data /var/www/humhub/protected/modules/space-join-questions/controllers/EmailTemplateController.php
    echo "EmailTemplateController.php restored"
fi

if [ -f "$EVENTS_BACKUP" ]; then
    echo "Restoring Events.php..."
    sudo cp "$EVENTS_BACKUP" /var/www/humhub/protected/modules/space-join-questions/Events.php
    sudo chown www-data:www-data /var/www/humhub/protected/modules/space-join-questions/Events.php
    echo "Events.php restored"
fi

# Clear any caches
echo "Clearing caches..."
sudo rm -rf /var/www/humhub/protected/runtime/cache/*
sudo rm -rf /var/www/humhub/protected/runtime/logs/*

echo "Rollback completed successfully!"
echo "The email template link fix has been reverted to the original state."
echo "You can now test the original functionality."
