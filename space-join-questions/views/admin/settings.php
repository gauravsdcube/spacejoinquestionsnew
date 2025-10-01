<?php

use humhub\libs\Html;
use humhub\widgets\Button;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $emailNotifications boolean */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Settings');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id' => 'settings-form',
        ]); ?>

        <div class="form-group">
            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Notifications') ?></h4>
            <p class="text-muted">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Configure when space administrators receive email notifications about membership applications.') ?>
            </p>

            <div class="checkbox">
                <label>
                    <?= Html::checkbox('settings[emailNotifications]', $emailNotifications, [
                        'value' => 1,
                        'id' => 'email-notifications-checkbox'
                    ]) ?>
                    <?= Yii::t('SpaceJoinQuestionsModule.base', 'Send email notifications when new membership applications are received') ?>
                </label>
            </div>

            <div class="help-block">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'When enabled, space administrators will receive an email notification each time someone submits a membership application with custom question answers.') ?>
            </div>

            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Note:') ?></strong>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'By default, email notifications are sent to all space administrators. You can customize who receives notifications using the Manage Recipients button below. You can also customize email templates in the Email Templates section.') ?>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Notification Recipients') ?></h4>
            <p class="text-muted">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Manage who receives email notifications when new membership applications are submitted.') ?>
            </p>

            <?php
            $recipients = \humhub\modules\spaceJoinQuestions\models\SpaceJoinNotificationRecipient::getRecipientsForSpace($space->id);
            $recipientCount = count($recipients);
            ?>
            
            <div class="form-group">
                <label><?= Yii::t('SpaceJoinQuestionsModule.base', 'Current Recipients:') ?></label>
                <p>
                    <?php if ($recipientCount > 0): ?>
                        <?= $recipientCount ?> custom recipient(s) configured
                    <?php else: ?>
                        All space administrators (default)
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="form-group">
                <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Manage Recipients'))
                    ->link($space->createUrl('/space-join-questions/admin/notification-recipients'))
                    ->icon('users') ?>
            </div>
        </div>




        <div class="form-group">
            <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Save Settings'))
                ->submit()
                ->icon('save') ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Go Back'))
                    ->link($space->createUrl('/space-join-questions/membership/index'))
                    ->icon('arrow-left') ?>
            </div>
        </div>
    </div>
</div>
