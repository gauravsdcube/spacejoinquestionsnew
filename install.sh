#!/bin/bash

# Space Join Questions Module - Installation Script
# Version: 2.0.0
# Compatible with: HumHub 1.17.3+

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
HUMPATH="$(pwd)"
BACKUP_DIR="$HUMPATH/backups/spaceJoinQuestions_$(date +%Y%m%d_%H%M%S)"
MODULE_DIR="$HUMPATH/protected/modules/spaceJoinQuestions"

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}Space Join Questions Module${NC}"
echo -e "${BLUE}Installation Script v2.0.0${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Function to print status messages
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
print_status "Checking prerequisites..."

if ! command_exists php; then
    print_error "PHP is not installed or not in PATH"
    exit 1
fi

if ! command_exists mysql; then
    print_warning "MySQL client not found. Database operations may fail."
fi

# Check if we're in a HumHub directory
if [ ! -f "$HUMPATH/protected/yii" ]; then
    print_error "This doesn't appear to be a HumHub installation directory"
    print_error "Please run this script from your HumHub root directory"
    exit 1
fi

print_status "HumHub installation detected at: $HUMPATH"

# Create backup directory
print_status "Creating backup directory..."
mkdir -p "$BACKUP_DIR"

# Backup core files
print_status "Backing up core HumHub files..."

# Backup MembershipController.php
if [ -f "$HUMPATH/protected/humhub/modules/space/controllers/MembershipController.php" ]; then
    cp "$HUMPATH/protected/humhub/modules/space/controllers/MembershipController.php" "$BACKUP_DIR/"
    print_status "Backed up MembershipController.php"
else
    print_error "MembershipController.php not found!"
    exit 1
fi

# Backup space about view
if [ -f "$HUMPATH/protected/humhub/modules/space/views/space/about.php" ]; then
    cp "$HUMPATH/protected/humhub/modules/space/views/space/about.php" "$BACKUP_DIR/"
    print_status "Backed up space about view"
else
    print_error "Space about view not found!"
    exit 1
fi

# Create backup info file
cat > "$BACKUP_DIR/backup_info.txt" << EOF
Space Join Questions Module Backup
Created: $(date)
HumHub Version: $(php protected/yii --version 2>/dev/null || echo "Unknown")
Module Version: 2.0.0
Backup Directory: $BACKUP_DIR

Modified Files:
- protected/humhub/modules/space/controllers/MembershipController.php
- protected/humhub/modules/space/views/space/about.php

To restore from backup:
cp $BACKUP_DIR/MembershipController.php protected/humhub/modules/space/controllers/
cp $BACKUP_DIR/about.php protected/humhub/modules/space/views/space/
php protected/yii cache/flush-all
EOF

print_status "Backup completed: $BACKUP_DIR"

# Check if module directory exists
if [ -d "$MODULE_DIR" ]; then
    print_warning "Module directory already exists. Updating..."
    rm -rf "$MODULE_DIR"
fi

# Create module directory
print_status "Creating module directory..."
mkdir -p "$MODULE_DIR"

# Copy module files (assuming script is run from module directory)
print_status "Copying module files..."
cp -r . "$MODULE_DIR/"

# Set proper permissions
print_status "Setting file permissions..."
find "$MODULE_DIR" -type d -exec chmod 755 {} \;
find "$MODULE_DIR" -type f -exec chmod 644 {} \;
chmod +x "$MODULE_DIR/install.sh"

# Apply core file modifications
print_status "Applying core file modifications..."

# Modify MembershipController.php
if [ -f "$MODULE_DIR/core_patches/MembershipController.php" ]; then
    cp "$MODULE_DIR/core_patches/MembershipController.php" "$HUMPATH/protected/humhub/modules/space/controllers/"
    print_status "Applied MembershipController.php modifications"
else
    print_warning "Core patch file not found. Manual modification required."
fi

# Modify space about view
if [ -f "$MODULE_DIR/core_patches/about.php" ]; then
    cp "$MODULE_DIR/core_patches/about.php" "$HUMPATH/protected/humhub/modules/space/views/space/"
    print_status "Applied space about view modifications"
else
    print_warning "Core patch file not found. Manual modification required."
fi

# Clear HumHub cache
print_status "Clearing HumHub cache..."
php "$HUMPATH/protected/yii" cache/flush-all 2>/dev/null || print_warning "Could not clear cache"

# Run database migrations
print_status "Running database migrations..."
if php "$HUMPATH/protected/yii" migrate/up --migrationPath=@spaceJoinQuestions/migrations 2>/dev/null; then
    print_status "Database migrations completed successfully"
else
    print_warning "Database migrations failed. You may need to run them manually:"
    print_warning "php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations"
fi

# Check module installation
print_status "Checking module installation..."
if php "$HUMPATH/protected/yii" module/list 2>/dev/null | grep -q "space-join-questions"; then
    print_status "Module detected in HumHub"
else
    print_warning "Module not detected. You may need to enable it manually via the web interface."
fi

# Create installation summary
cat > "$MODULE_DIR/INSTALLATION_SUMMARY.txt" << EOF
Space Join Questions Module - Installation Summary
================================================

Installation Date: $(date)
HumHub Path: $HUMPATH
Module Path: $MODULE_DIR
Backup Location: $BACKUP_DIR

Installation Status: COMPLETED

Next Steps:
1. Log into your HumHub admin panel
2. Go to Administration > Modules
3. Find "Space Join Questions" and click "Enable"
4. Configure spaces to require applications:
   - Go to any space's Settings > General
   - Check "Require application for membership"
   - Add custom questions as needed

Email Configuration:
- Ensure your mailer is configured in protected/config/common.php
- Test email functionality by submitting an application

Troubleshooting:
- Check logs: protected/runtime/logs/
- Clear cache: php protected/yii cache/flush-all
- Restore from backup if needed: $BACKUP_DIR

Support:
- Documentation: $MODULE_DIR/README.md
- Core Changes: $MODULE_DIR/CORE_CHANGES.md
- Backup Location: $BACKUP_DIR

EOF

print_status "Installation completed successfully!"
echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}Installation Summary${NC}"
echo -e "${GREEN}================================${NC}"
echo "Module installed to: $MODULE_DIR"
echo "Backup created at: $BACKUP_DIR"
echo "Installation summary: $MODULE_DIR/INSTALLATION_SUMMARY.txt"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Enable the module via web interface (Administration > Modules)"
echo "2. Configure spaces to require applications"
echo "3. Test email functionality"
echo ""
echo -e "${BLUE}For detailed documentation, see:${NC}"
echo "- $MODULE_DIR/README.md"
echo "- $MODULE_DIR/CORE_CHANGES.md"
echo "" 