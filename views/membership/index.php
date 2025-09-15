<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use humhub\modules\ui\view\components\View;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this View */
/* @var $space Space */

// Get application statistics
$totalApplications = \humhub\modules\space\models\Membership::find()
    ->where([
        'space_id' => $space->id,
        'status' => Membership::STATUS_APPLICANT
    ])
    ->count();

$newApplications = \humhub\modules\space\models\Membership::find()
    ->where([
        'space_id' => $space->id,
        'status' => Membership::STATUS_APPLICANT
    ])
    ->andWhere(['>=', 'created_at', time() - (24 * 60 * 60)]) // Last 24 hours
    ->count();

$recentApplications = \humhub\modules\space\models\Membership::find()
    ->where([
        'space_id' => $space->id,
        'status' => Membership::STATUS_APPLICANT
    ])
    ->andWhere(['>=', 'created_at', time() - (7 * 24 * 60 * 60)]) // Last 7 days
    ->count();
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Manage Membership') ?></h4>
    </div>
    <div class="panel-body">
        
        <?php if ($totalApplications > 0): ?>
            <!-- Application Statistics -->
            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="fa fa-info-circle"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Summary') ?></h5>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h3 class="text-info"><?= $totalApplications ?></h3>
                                <p class="text-muted"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Total Pending') ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-warning"><?= $newApplications ?></h3>
                                <p class="text-muted"><?= Yii::t('SpaceJoinQuestionsModule.base', 'New (24h)') ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h3 class="text-primary"><?= $recentApplications ?></h3>
                                <p class="text-muted"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Recent (7d)') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="membership-management-options">
                    
                    <!-- Membership Applications -->
                    <div class="membership-option">
                        <div class="option-header">
                            <i class="fa fa-users"></i>
                            <h5>
                                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Membership Applications') ?>
                                <?php if ($totalApplications > 0): ?>
                                    <span class="label label-danger"><?= $totalApplications ?></span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'Review and manage membership applications from users who want to join this space.') ?></p>
                        <a href="<?= $space->createUrl('/space-join-questions/admin/applications') ?>" class="btn btn-primary">
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'View Applications') ?>
                        </a>
                    </div>

                    <hr>

                    <!-- Join Questions -->
                    <div class="membership-option">
                        <div class="option-header">
                            <i class="fa fa-question-circle"></i>
                            <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Join Questions') ?></h5>
                        </div>
                        <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'Configure custom questions that users must answer when requesting membership.') ?></p>
                        <a href="<?= $space->createUrl('/space-join-questions/admin/index') ?>" class="btn btn-primary">
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Manage Questions') ?>
                        </a>
                    </div>

                    <hr>

                    <!-- Email Templates -->
                    <div class="membership-option">
                        <div class="option-header">
                            <i class="fa fa-envelope"></i>
                            <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Templates') ?></h5>
                        </div>
                        <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'Customize email templates for membership notifications and communications.') ?></p>
                        <a href="<?= $space->createUrl('/space-join-questions/email-template') ?>" class="btn btn-primary">
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Manage Templates') ?>
                        </a>
                    </div>

                    <hr>

                    <!-- Email Notifications Setting -->
                    <div class="membership-option">
                        <div class="option-header">
                            <i class="fa fa-bell"></i>
                            <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Notifications') ?></h5>
                        </div>
                        <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'Configure email notification settings for membership applications and updates.') ?></p>
                        <a href="<?= $space->createUrl('/space-join-questions/admin/settings') ?>" class="btn btn-primary">
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Notification Settings') ?>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.membership-management-options {
    padding: 20px 0;
}

.membership-option {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.membership-option:hover {
    background-color: #f0f0f0;
    border-color: #d0d0d0;
}

.option-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.option-header i {
    font-size: 24px;
    margin-right: 10px;
    color: #21A1B3;
}

.option-header h5 {
    margin: 0;
    font-weight: bold;
    color: #333;
}

.membership-option p {
    color: #666;
    margin-bottom: 15px;
}

.membership-option .btn {
    margin-top: 10px;
}

hr {
    border-color: #e5e5e5;
    margin: 30px 0;
}
</style> 