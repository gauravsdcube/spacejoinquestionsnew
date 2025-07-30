<?php

use humhub\libs\Html;
use humhub\modules\user\widgets\Image;
use humhub\modules\user\widgets\UserTag;
use humhub\widgets\Button;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $application humhub\modules\space\models\Membership */
/* @var $isDeclined boolean */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Application Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('SpaceJoinQuestionsModule.base', 'Applications'), 'url' => $space->createUrl('/space-join-questions/admin/applications')];
$this->params['breadcrumbs'][] = $this->title;

// Determine if this is a declined application
$isDeclined = isset($isDeclined) ? $isDeclined : false;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
        <div class="pull-right">
            <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Back to Applications'))
                ->link($space->createUrl('/space-join-questions/admin/applications'))
                ->icon('arrow-left') ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <!-- Applicant Information -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Applicant Information') ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-left">
                                <?php if ($application->user): ?>
                                    <?= Image::widget([
                                        'user' => $application->user,
                                        'width' => 80,
                                        'showTooltip' => true,
                                        'link' => true
                                    ]); ?>
                                <?php else: ?>
                                    <div class="img-rounded" style="width: 80px; height: 80px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-user" style="font-size: 40px; color: #999;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="media-body">
                                <?php if ($application->user): ?>
                                    <h4><?= Html::encode($application->user->displayName) ?></h4>
                                    <p class="text-muted">
                                        <i class="fa fa-envelope"></i> <?= Html::encode($application->user->email) ?><br>
                                        <i class="fa fa-calendar"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Member since: {date}', ['date' => Yii::$app->formatter->asDate($application->user->created_at)]) ?>
                                    </p>
                                    <?php if ($application->user->profile && $application->user->profile->title): ?>
                                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Title:') ?></strong> <?= Html::encode($application->user->profile->title) ?></p>
                                    <?php endif; ?>
                                    <?php if ($application->user->profile && $application->user->profile->about): ?>
                                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'About:') ?></strong> <?= Html::encode($application->user->profile->about) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'User Information Not Available') ?></h4>
                                    <p class="text-muted">
                                        <i class="fa fa-exclamation-triangle"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'User information is not available for this application.') ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Message -->
                <?php if (!empty($application->request_message)): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Message') ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="well">
                                <?= nl2br(Html::encode($application->request_message)) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Application Answers -->
                <?php if (!empty($answers)): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Question Answers') ?></h4>
                        </div>
                        <div class="panel-body">
                            <?php
                            // Debug: Log the number of answers to help identify duplication
                            Yii::error('Number of answers: ' . count($answers));

                            // Use array_unique to remove duplicates based on question_id and answer_text
                            $uniqueAnswers = [];
                            $seen = [];

                            foreach ($answers as $answer) {
                                $key = $answer->question_id . '_' . $answer->answer_text;
                                if (!isset($seen[$key])) {
                                    $seen[$key] = true;
                                    $uniqueAnswers[] = $answer;
                                }
                            }

                            foreach ($uniqueAnswers as $answer): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong><?= Html::encode($answer->question->question_text) ?></strong>
                                        <div class="well well-sm">
                                            <?= nl2br(Html::encode($answer->answer_text)) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Actions') ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php if ($isDeclined): ?>
                            <!-- Declined Application Status -->
                            <div class="alert alert-danger">
                                <i class="fa fa-times-circle"></i>
                                <strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Declined') ?></strong><br>
                                <?= Yii::t('SpaceJoinQuestionsModule.base', 'This application was declined on {date}', [
                                    'date' => Yii::$app->formatter->asDatetime($application->processed_at)
                                ]) ?>
                            </div>

                            <?php if ($application->decline_reason): ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Decline Reason') ?></h5>
                                    </div>
                                    <div class="panel-body">
                                        <div class="well">
                                            <?= nl2br(Html::encode($application->decline_reason)) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($application->processedBy): ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Processed By') ?></h5>
                                    </div>
                                    <div class="panel-body">
                                        <div class="media">
                                            <div class="media-left">
                                                <?= Image::widget([
                                                    'user' => $application->processedBy,
                                                    'width' => 40,
                                                    'showTooltip' => true,
                                                    'link' => true
                                                ]); ?>
                                            </div>
                                            <div class="media-body">
                                                <strong><?= Html::encode($application->processedBy->displayName) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($application->status == \humhub\modules\space\models\Membership::STATUS_APPLICANT): ?>
                            <!-- Pending Application Actions -->
                            <div class="btn-group-vertical btn-block">
                                <form method="post" action="<?= $space->createUrl('/space-join-questions/admin/approve', ['membershipId' => $application->id]) ?>" style="display: inline;">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                    <button type="submit" class="btn btn-success btn-block" onclick="return confirm('<?= Yii::t('SpaceJoinQuestionsModule.base', 'Are you sure you want to approve this application?') ?>')">
                                        <i class="fa fa-check"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Approve Application') ?>
                                    </button>
                                </form>

                                <div style="height: 20px; margin: 10px 0;"></div>

                                <form method="post" action="<?= $space->createUrl('/space-join-questions/admin/decline', ['membershipId' => $application->id]) ?>" style="display: inline;" id="decline-form">
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                    <div class="form-group">
                                        <label for="decline-reason"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Reason for declining (required):') ?> <span class="text-danger">*</span></label>
                                        <textarea id="decline-reason" name="decline_reason" class="form-control" rows="3" placeholder="<?= Yii::t('SpaceJoinQuestionsModule.base', 'Please provide a reason for declining this application...') ?>" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-block" onclick="return validateDeclineForm()">
                                        <i class="fa fa-times"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Decline Application') ?>
                                    </button>
                                </form>

                                <script <?= Html::nonce() ?>>
                                function validateDeclineForm() {
                                    var reason = document.getElementById('decline-reason').value.trim();
                                    if (reason === '') {
                                        alert('<?= Yii::t('SpaceJoinQuestionsModule.base', 'Please provide a reason for declining this application.') ?>');
                                        document.getElementById('decline-reason').focus();
                                        return false;
                                    }
                                    return confirm('<?= Yii::t('SpaceJoinQuestionsModule.base', 'Are you sure you want to decline this application?') ?>');
                                }
                                </script>
                            </div>
                        <?php else: ?>
                            <!-- Other Status -->
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <?= Yii::t('SpaceJoinQuestionsModule.base', 'This application has already been processed.') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
