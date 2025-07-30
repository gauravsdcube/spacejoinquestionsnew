<?php

use humhub\libs\Html;
use humhub\modules\user\widgets\Image;
use humhub\widgets\Button;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Membership Applications');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
        <div class="pull-right">
            <small class="text-muted">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Total: {count}', ['count' => $dataProvider->totalCount]) ?>
            </small>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="panel-body">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php if ($dataProvider->totalCount == 0): ?>
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle fa-2x"></i>
                <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'No Pending Applications') ?></h4>
                <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'There are currently no pending membership applications for this space.') ?></p>

                <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Manage Questions'))
                    ->link($space->createUrl('/space-join-questions/admin/index'))
                    ->icon('question-circle') ?>
            </div>
        <?php else: ?>
            <?php Pjax::begin(['id' => 'applications-pjax']); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => '{items}{pager}',
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'attribute' => 'user.profile.firstname',
                        'label' => Yii::t('SpaceJoinQuestionsModule.base', 'Applicant'),
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'width: 250px; white-space: nowrap;'],
                        'value' => function ($model) {
                            $html = '<div class="media">';
                            $html .= '<div class="media-left">';
                            $html .= Image::widget([
                                'user' => $model->user,
                                'width' => 40,
                                'showTooltip' => true,
                                'link' => true
                            ]);
                            $html .= '</div>';
                            $html .= '<div class="media-body">';
                            $html .= '<strong>' . Html::encode($model->user->displayName) . '</strong><br>';
                            $html .= '<small class="text-muted">' . Html::encode($model->user->email) . '</small>';
                            $html .= '</div>';
                            $html .= '</div>';
                            return $html;
                        },
                    ],

                    [
                        'attribute' => 'created_at',
                        'label' => Yii::t('SpaceJoinQuestionsModule.base', 'Applied'),
                        'format' => 'relativeTime',
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => Yii::t('SpaceJoinQuestionsModule.base', 'Actions'),
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) use ($space) {
                                return Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'View'))
                                    ->link($space->createUrl('/space-join-questions/admin/application-detail', ['membershipId' => $model->id]))
                                    ->icon('eye')
                                    ->xs();
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        <?php endif; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Manage Questions'))
                    ->link($space->createUrl('/space-join-questions/admin/index'))
                    ->icon('question-circle') ?>
            </div>
            <div class="col-md-6 text-right">
                <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Settings'))
                    ->link($space->createUrl('/space-join-questions/admin/settings'))
                    ->icon('cog') ?>
            </div>
        </div>
    </div>
</div>
