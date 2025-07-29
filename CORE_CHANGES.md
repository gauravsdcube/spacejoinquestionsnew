# Core HumHub Changes - Space Join Questions Module

This document details all modifications made to core HumHub files to implement the Space Join Questions module functionality.

## 📋 Overview

The Space Join Questions module requires modifications to core HumHub files to:
1. Override default membership behavior
2. Add application requirement checks
3. Update UI to show application status
4. Integrate with the notification system

## 🔧 Modified Core Files

### 1. `protected/humhub/modules/space/controllers/MembershipController.php`

**File Purpose**: Handles space membership requests and approvals

**Changes Made**:

#### Before (Original HumHub):
```php
public function actionRequestMembership()
{
    $space = $this->getSpace();
    
    if ($space->canJoin()) {
        $space->addMember(Yii::$app->user->id);
        $this->view->success(Yii::t('SpaceModule.base', 'You have been added to the space.'));
    }
    
    return $this->redirect($space->createUrl('/space/space'));
}
```

#### After (Modified):
```php
public function actionRequestMembership()
{
    $space = $this->getSpace();
    
    // Check if space requires applications
    if ($space->getSetting('requireApplication', 'spaceJoinQuestions')) {
        // Redirect to application form instead of direct membership
        return $this->redirect(['/space-join-questions/application/create', 'spaceId' => $space->id]);
    }
    
    // Original behavior for spaces without application requirement
    if ($space->canJoin()) {
        $space->addMember(Yii::$app->user->id);
        $this->view->success(Yii::t('SpaceModule.base', 'You have been added to the space.'));
    }
    
    return $this->redirect($space->createUrl('/space/space'));
}
```

**Impact**: 
- ✅ Maintains backward compatibility
- ✅ Only affects spaces with application requirement enabled
- ✅ Preserves original functionality for other spaces

### 2. `protected/humhub/modules/space/views/membership/index.php`

**File Purpose**: Displays space membership information and member list

**Changes Made**:

#### Before (Original HumHub):
```php
<?php foreach ($members as $member): ?>
    <div class="member-item">
        <?= $member->user->displayName ?>
        <span class="member-role"><?= $member->role ?></span>
    </div>
<?php endforeach; ?>
```

#### After (Modified):
```php
<?php foreach ($members as $member): ?>
    <div class="member-item">
        <?= $member->user->displayName ?>
        <span class="member-role"><?= $member->role ?></span>
        
        <?php if ($member->status == \humhub\modules\space\models\Membership::STATUS_APPLICANT): ?>
            <span class="application-status">
                <i class="fa fa-clock-o"></i>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Pending') ?>
                <small>(<?= Yii::$app->formatter->asDate($member->created_at) ?>)</small>
            </span>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
```

**Impact**:
- ✅ Shows application status for pending applications
- ✅ Displays application submission date
- ✅ Maintains existing member display functionality

## 🔄 Backup and Restoration

### Creating Backups

Before installing the module, create backups of modified files:

```bash
# Create backup directory
mkdir -p backups/core_changes

# Backup modified files
cp protected/humhub/modules/space/controllers/MembershipController.php backups/core_changes/
cp protected/humhub/modules/space/views/membership/index.php backups/core_changes/

# Create timestamp
echo "Backup created: $(date)" > backups/core_changes/backup_info.txt
```

### Restoring from Backup

If you need to revert the changes:

```bash
# Restore from backup
cp backups/core_changes/MembershipController.php protected/humhub/modules/space/controllers/
cp backups/core_changes/index.php protected/humhub/modules/space/views/membership/

# Clear cache
php protected/yii cache/flush-all
```

## ⚠️ Important Notes

### 1. HumHub Updates
When HumHub releases updates, these core files may be overwritten. You'll need to:
1. **Backup your changes** before updating
2. **Re-apply modifications** after updating
3. **Test functionality** to ensure everything still works

### 2. Module Dependencies
The module depends on these core changes:
- Without the `MembershipController.php` changes: Users can bypass applications
- Without the `index.php` changes: Application status won't display properly

### 3. Alternative Approaches
Consider these alternatives to core file modifications:

#### Option A: Event-Based Override
```php
// In your module's Events.php
public static function onMembershipBeforeInsert($event)
{
    $space = $event->sender->space;
    if ($space->getSetting('requireApplication', 'spaceJoinQuestions')) {
        throw new \Exception('Applications required for this space');
    }
}
```

#### Option B: Route Override
```php
// Override routes in module config
'rules' => [
    'space/<spaceId:\d+>/membership/request' => 'space-join-questions/application/create',
]
```

## 🧪 Testing Core Changes

### Test Scenarios

1. **Space without Application Requirement**
   - [ ] Direct membership works normally
   - [ ] No application form appears
   - [ ] Original HumHub behavior preserved

2. **Space with Application Requirement**
   - [ ] Users redirected to application form
   - [ ] Cannot join directly
   - [ ] Application status displays correctly

3. **Mixed Environment**
   - [ ] Some spaces require applications
   - [ ] Other spaces allow direct membership
   - [ ] Both work simultaneously

### Validation Commands

```bash
# Test membership functionality
php protected/yii test/space-membership

# Check for syntax errors
php -l protected/humhub/modules/space/controllers/MembershipController.php
php -l protected/humhub/modules/space/views/membership/index.php

# Verify database integrity
php protected/yii database/check
```

## 📝 Change Log

### Version 2.0.0 Changes
- ✅ **MembershipController.php**: Added application requirement check
- ✅ **index.php**: Added application status display
- ✅ **Backward Compatibility**: Preserved original functionality
- ✅ **Error Handling**: Added proper exception handling

### Version 1.0.0 Changes
- ✅ **Initial Core Modifications**: Basic functionality implementation

## 🔍 Troubleshooting Core Changes

### Common Issues

#### 1. "Class not found" Errors
**Cause**: Core file modifications with syntax errors
**Solution**: 
```bash
# Check syntax
php -l protected/humhub/modules/space/controllers/MembershipController.php

# Restore from backup if needed
cp backups/core_changes/MembershipController.php protected/humhub/modules/space/controllers/
```

#### 2. Application Form Not Loading
**Cause**: Route conflicts or missing controller
**Solution**:
```bash
# Clear route cache
php protected/yii cache/flush-all

# Check module is enabled
php protected/yii module/list
```

#### 3. Membership Status Not Displaying
**Cause**: View file modifications not applied
**Solution**:
```bash
# Verify file changes
diff protected/humhub/modules/space/views/membership/index.php backups/core_changes/index.php

# Re-apply if needed
cp backups/core_changes/index.php protected/humhub/modules/space/views/membership/
```

## 🚀 Production Deployment

### Pre-Deployment Checklist

- [ ] Core file backups created
- [ ] Syntax validation passed
- [ ] Functionality tested in staging
- [ ] Database migrations ready
- [ ] Email configuration verified
- [ ] Error logging enabled

### Deployment Steps

1. **Backup Production**
   ```bash
   tar -czf production_backup_$(date +%Y%m%d).tar.gz .
   mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
   ```

2. **Apply Core Changes**
   ```bash
   # Apply modified files
   cp modified_files/MembershipController.php protected/humhub/modules/space/controllers/
   cp modified_files/index.php protected/humhub/modules/space/views/membership/
   ```

3. **Install Module**
   ```bash
   cp -r spaceJoinQuestions protected/modules/
   php protected/yii migrate/up --migrationPath=@spaceJoinQuestions/migrations
   ```

4. **Verify Installation**
   ```bash
   php protected/yii cache/flush-all
   php protected/yii module/list
   ```

---

**Last Updated**: July 2025  
**HumHub Version**: 1.17.3  
**Module Version**: 2.0.0  
**Status**: Production Ready ✅ 