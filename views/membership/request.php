<?php

use humhub\libs\Html;
use humhub\modules\spaceJoinQuestions\widgets\JoinQuestionsForm;
use humhub\widgets\Button;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $questions humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion[] */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Request Membership');
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Request Membership: {spaceName}', [
        'spaceName' => Html::encode($space->name),
    ]),
    'footer' =>
        ModalButton::cancel() . ' ' .
        Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application'))->submit(),
    'form' => [
        'id' => 'membership-request-form',
        'action' => $space->createUrl('/space-join-questions/membership/request'),
        'enableClientValidation' => true,
        'enableAjaxValidation' => false,
    ],
]) ?>

        <div class="modal-body">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Please complete the form below to request membership in this space. Your application will be reviewed by the space administrators.') ?>
            </div>

            <!-- Custom Questions -->
            <?= JoinQuestionsForm::widget([
                'space' => $space,
                'questions' => $questions,
            ]) ?>
        </div>

<?php Modal::endFormDialog(); ?>
