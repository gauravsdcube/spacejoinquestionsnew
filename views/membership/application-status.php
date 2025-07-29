<?php

use yii\helpers\Html;
use humhub\widgets\Button;
use humhub\modules\spaceJoinQuestions\widgets\ApplicationAnswers;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $membership humhub\modules\space\models\Membership */
/* @var $answers humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer[] */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Application Status');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-clock-o"></i>
                    <?= Html::encode($this->title) ?>
                </div>
                
                <div class="panel-body">
                    <div class="alert alert-warning">
                        <i class="fa fa-hourglass-half"></i>
                        <strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Pending') ?></strong>
                        <br>
                        <?= Yii::t('SpaceJoinQuestionsModule.base', 'Your membership application for "{spaceName}" is currently under review by the space administrators.', [
                            'spaceName' => Html::encode($space->name)
                        ]) ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Details') ?></h4>
                            <dl class="dl-horizontal">
                                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Space:') ?></dt>
                                <dd><?= Html::encode($space->name) ?></dd>
                                
                                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Submitted:') ?></dt>
                                <dd>
                                    <?= Yii::$app->formatter->asRelativeTime($membership->created_at) ?>
                                    <br><small class="text-muted"><?= Yii::$app->formatter->asDatetime($membership->created_at) ?></small>
                                </dd>
                                
                                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Status:') ?></dt>
                                <dd>
                                    <span class="label label-warning">
                                        <?= Yii::t('SpaceJoinQuestionsModule.base', 'Pending Review') ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'What Happens Next?') ?></h4>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check text-success"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Application submitted successfully') ?></li>
                                <li><i class="fa fa-clock-o text-warning"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Administrators are reviewing your application') ?></li>
                                <li><i class="fa fa-envelope text-info"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'You will receive a notification with their decision') ?></li>
                            </ul>
                        </div>
                    </div>

                    <?php if (!empty($membership->request_message)): ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Your Message') ?></h4>
                                <div class="well">
                                    <?= nl2br(Html::encode($membership->request_message)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Show submitted answers -->
                    <?= ApplicationAnswers::widget([
                        'membership' => $membership,
                        'answers' => $answers,
                    ]) ?>

                    <hr>

                    <div class="text-center">
                        <?= Button::danger(Yii::t('SpaceJoinQuestionsModule.base', 'Cancel Application'))
                            ->link($space->createUrl('/space-join-questions/membership/cancel'))
                            ->icon('times')
                            ->confirm(Yii::t('SpaceJoinQuestionsModule.base', 'Are you sure you want to cancel your membership application?'))
                            ->options(['data-method' => 'POST']) ?>
                        
                        <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Back to Space'))
                            ->link($space->createUrl('/space/space'))
                            ->icon('arrow-left') ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div> 