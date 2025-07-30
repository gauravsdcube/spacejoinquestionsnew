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
        </div>

        <hr>

        <div class="form-group">
            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Module Information') ?></h4>

            <dl class="dl-horizontal">
                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Module Version:') ?></dt>
                <dd>2.0.0</dd>

                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Questions Created:') ?></dt>
                <dd>
                    <?php
                    $questionCount = \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
                        ->where(['space_id' => $space->id])
                        ->count();
                    echo $questionCount;
                    ?>
                </dd>

                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Total Applications:') ?></dt>
                <dd>
                    <?php
                    $applicationCount = \humhub\modules\space\models\Membership::find()
                        ->where(['space_id' => $space->id, 'status' => \humhub\modules\space\models\Membership::STATUS_APPLICANT])
                        ->count();
                    echo $applicationCount;
                    ?>
                </dd>
            </dl>
        </div>

        <div class="form-group">
            <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Save Settings'))
                ->submit()
                ->icon('save') ?>

            <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Back to Questions'))
                ->link($space->createUrl('/space-join-questions/admin/index'))
                ->icon('arrow-left') ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
