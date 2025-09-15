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
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Email notifications are sent to all space administrators. You can customize email templates in the Email Templates section.') ?>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Notification Recipients') ?></h4>
            <p class="text-muted">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Email notifications are sent to all space administrators.') ?>
            </p>

            <?php
            $admins = $space->getAdmins();
            if (!empty($admins)):
            ?>
                <div class="well">
                    <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Current Space Administrators:') ?></h5>
                    <ul class="list-unstyled">
                        <?php foreach ($admins as $admin): ?>
                            <li>
                                <i class="fa fa-user"></i>
                                <?= Html::encode($admin->displayName) ?>
                                <small class="text-muted">(<?= Html::encode($admin->email) ?>)</small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= Yii::t('SpaceJoinQuestionsModule.base', 'No space administrators found. Notifications will not be sent.') ?>
                </div>
            <?php endif; ?>
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
