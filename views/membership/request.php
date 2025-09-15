<?php

use humhub\libs\Html;
use humhub\modules\spaceJoinQuestions\widgets\JoinQuestionsForm;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use humhub\widgets\ModalDialog;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $questions humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion[] */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Request Membership');
?>

<?php ModalDialog::begin([
    'header' => Icon::get('users') . ' ' . Yii::t('SpaceJoinQuestionsModule.base', 'Request Membership: {spaceName}', [
        'spaceName' => Html::encode($space->name),
    ]),
]) ?>
        <?php $form = ActiveForm::begin([
            'id' => 'membership-request-form',
            'action' => $space->createUrl('/space-join-questions/membership/request'),
            'enableClientValidation' => true,
            'enableAjaxValidation' => false,
        ]); ?>

        <div class="modal-body">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Please complete the form below to request membership in this space. Your application will be reviewed by the space administrators.') ?>
            </div>

            <!-- Standard message field -->
            <div class="form-group">
                <?= Html::label(
                    Yii::t('SpaceJoinQuestionsModule.base', 'Message (Optional)'),
                    'request_message',
                    ['class' => 'control-label'],
                ) ?>
                <?= Html::textarea('request_message', '', [
                    'id' => 'request_message',
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Tell the administrators why you want to join this space...'),
                    'maxlength' => 1000,
                ]) ?>
                <div class="help-block">
                    <?= Yii::t('SpaceJoinQuestionsModule.base', 'Optional message to space administrators') ?>
                </div>
            </div>

            <hr>

            <!-- Custom Questions -->
            <?= JoinQuestionsForm::widget([
                'space' => $space,
                'questions' => $questions,
            ]) ?>
        </div>

        <div class="modal-footer">
            <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application'))
                ->submit()
                ->icon('paper-plane') ?>

            <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Cancel'))
                ->options(['data-dismiss' => 'modal'])
                ->icon('times') ?>
        </div>

        <?php ActiveForm::end(); ?>
<?php ModalDialog::end() ?>

<script <?= Html::nonce() ?>>
$(document).ready(function() {
    // Handle form submission via AJAX
    $('#membership-request-form').on('beforeSubmit', function(e) {
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');

        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Submitting...') ?>');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message and close modal
                    humhub.modules.ui.status.success(response.message);
                    $('#globalModal').modal('hide');
                    // Redirect to status page instead of reloading
                    setTimeout(function() {
                        window.location.href = '<?= $space->createUrl('/space-join-questions/membership/status') ?>';
                    }, 1000);
                } else {
                    // Show error messages
                    if (response.errors && response.errors.length > 0) {
                        response.errors.forEach(function(error) {
                            humhub.modules.ui.status.error(error);
                        });
                    } else {
                        humhub.modules.ui.status.error('<?= Yii::t('SpaceJoinQuestionsModule.base', 'An error occurred while submitting your application.') ?>');
                    }

                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application') ?>');
                }
            },
            error: function() {
                humhub.modules.ui.status.error('<?= Yii::t('SpaceJoinQuestionsModule.base', 'A network error occurred. Please try again.') ?>');

                // Re-enable submit button
                submitBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Submit Application') ?>');
            }
        });

        return false; // Prevent normal form submission
    });
});
</script>
