<?php

use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\ui\view\components\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $template \humhub\modules\spaceJoinQuestions\models\EmailTemplate */
/* @var $space \humhub\modules\space\models\Space */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Edit Email Template');
$this->params['breadcrumbs'][] = ['label' => $space->name, 'url' => $space->createUrl('/space/space/home')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('SpaceJoinQuestionsModule.base', 'Space Join Questions'), 'url' => ['/space-join-questions/admin', 'cguid' => $space->guid]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('SpaceJoinQuestionsModule.base', 'Email Templates'), 'url' => ['/space-join-questions/email-template', 'cguid' => $space->guid]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?= Html::encode($this->title) ?></h4>
        <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Template Type: {type}', ['type' => $template->getTemplateTypeLabel()]) ?></p>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(['id' => 'email-template-form-' . $template->template_type]); ?>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($template, 'subject')->textInput(['maxlength' => 255]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Header') ?></h5>
                <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Add a header with your logo, company name, or a colored banner. You can use plain text or rich text formatting.') ?></p>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($template, 'header')->widget(RichTextField::class, [
                            'id' => 'email_template_header_' . $space->id . '_' . $template->id . '_' . $template->template_type,
                            'layout' => RichTextField::LAYOUT_BLOCK,
                            'preset' => 'full', // Enable full functionality including links
                            'pluginOptions' => ['maxHeight' => '200px'],
                            'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your email header here... (optional)'),
                            'focus' => false,
                            'backupInterval' => 0
                        ])->label(false) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($template, 'header_bg_color')->input('color', [
                                    'class' => 'form-control color-picker',
                                    'style' => 'height: 40px;',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Choose header background color'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Header Background Color')) ?>

                                <?= $form->field($template, 'header_bg_color')->textInput([
                                    'class' => 'form-control hex-input',
                                    'placeholder' => '#f8f9fa',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter hex color code'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Or enter hex code')) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($template, 'header_font_color')->input('color', [
                                    'class' => 'form-control color-picker',
                                    'style' => 'height: 40px;',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Choose header font color'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Header Font Color')) ?>

                                <?= $form->field($template, 'header_font_color')->textInput([
                                    'class' => 'form-control hex-input',
                                    'placeholder' => '#495057',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter hex color code'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Or enter hex code')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Body') ?></h5>
                <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'This is the main content of your email. Use the rich text editor to format text, add images, tables, and more.') ?></p>

                <?= $form->field($template, 'body')->widget(RichTextField::class, [
                    'id' => 'email_template_body_' . $space->id . '_' . $template->id . '_' . $template->template_type,
                    'layout' => RichTextField::LAYOUT_BLOCK,
                    'preset' => 'full', // Enable full functionality including links
                    'pluginOptions' => ['maxHeight' => '400px'],
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your email content here...'),
                    'focus' => false,
                    'backupInterval' => 0
                ])->label(false) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Email Footer') ?></h5>
                <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Add a footer with contact information, social links, or a disclaimer. You can use plain text or rich text formatting.') ?></p>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($template, 'footer')->widget(RichTextField::class, [
                            'id' => 'email_template_footer_' . $space->id . '_' . $template->id . '_' . $template->template_type,
                            'layout' => RichTextField::LAYOUT_BLOCK,
                            'preset' => 'full', // Enable full functionality including links
                            'pluginOptions' => ['maxHeight' => '200px'],
                            'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your email footer here... (optional)'),
                            'focus' => false,
                            'backupInterval' => 0
                        ])->label(false) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($template, 'footer_bg_color')->input('color', [
                                    'class' => 'form-control color-picker',
                                    'style' => 'height: 40px;',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Choose footer background color'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Footer Background Color')) ?>

                                <?= $form->field($template, 'footer_bg_color')->textInput([
                                    'class' => 'form-control hex-input',
                                    'placeholder' => '#f8f9fa',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter hex color code'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Or enter hex code')) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($template, 'footer_font_color')->input('color', [
                                    'class' => 'form-control color-picker',
                                    'style' => 'height: 40px;',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Choose footer font color'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Footer Font Color')) ?>

                                <?= $form->field($template, 'footer_font_color')->textInput([
                                    'class' => 'form-control hex-input',
                                    'placeholder' => '#6c757d',
                                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter hex color code'),
                                ])->label(Yii::t('SpaceJoinQuestionsModule.base', 'Or enter hex code')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h5><?= Yii::t('SpaceJoinQuestionsModule.base', 'Available Variables') ?></h5>
                <div class="alert alert-info">
                    <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'You can use these variables in your email:') ?></strong></p>
                    <ul>
                        <li><code>{admin_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Name of the admin') ?></li>
                        <li><code>{user_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Name of the user') ?></li>
                        <li><code>{user_email}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Email of the user') ?></li>
                        <li><code>{space_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Name of the space') ?></li>
                        <li><code>{application_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Date when application was submitted') ?></li>
                        <li><code>{application_answers}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Answers provided by the user') ?></li>
                        <?php if ($template->template_type === \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_ACCEPTED): ?>
                        <li><code>{accepted_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Date when application was accepted') ?></li>
                        <li><code>{admin_notes}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Notes from the admin') ?></li>
                        <?php endif; ?>
                        <?php if ($template->template_type === \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_DECLINED): ?>
                        <li><code>{declined_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Date when application was declined') ?></li>
                        <li><code>{decline_reason}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Reason for declining the application') ?></li>
                        <li><code>{admin_notes}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Notes from the admin') ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('SpaceJoinQuestionsModule.base', 'Save Template'), ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('SpaceJoinQuestionsModule.base', 'Preview'), ['/space-join-questions/email-template/preview', 'type' => $template->template_type, 'cguid' => $space->guid], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
            <?= Html::a(Yii::t('SpaceJoinQuestionsModule.base', 'Cancel'), ['/space-join-questions/email-template', 'cguid' => $space->guid], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
.help-block {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 10px;
}

h5 {
    margin-top: 20px;
    margin-bottom: 10px;
    color: #495057;
    font-weight: 600;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-info ul {
    margin-bottom: 0;
}

.alert-info code {
    background-color: #fff;
    color: #e83e8c;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Color picker styling */
input[type="color"] {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 2px;
    cursor: pointer;
}

input[type="color"]:hover {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.hex-input {
    font-family: monospace;
    font-size: 12px;
}

.hex-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Color preview */
.color-preview {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    margin-right: 8px;
    border: 1px solid #ced4da;
}

/* Section styling */
.email-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.email-section h5 {
    margin-top: 0;
    color: #495057;
    border-bottom: 2px solid #007bff;
    padding-bottom: 8px;
}

/* Color scheme suggestions */
.color-suggestions {
    margin-top: 10px;
    font-size: 11px;
    color: #6c757d;
}

.color-suggestion {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 5px;
}

.color-suggestion span {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 2px;
    margin-right: 4px;
    border: 1px solid #ced4da;
}

/* Color field grouping */
.color-field-group {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 10px;
}

.color-field-group label {
    font-size: 12px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}
</style>

<script <?= Html::nonce() ?>>
$(document).ready(function() {
    // Sync color pickers with hex inputs
    $('input[type="color"]').on('change', function() {
        var hexInput = $(this).siblings('input[type="text"]');
        hexInput.val($(this).val());
    });

    $('.hex-input').on('input', function() {
        var colorPicker = $(this).siblings('input[type="color"]');
        var hexValue = $(this).val();

        // Validate hex color format
        if (/^#[0-9A-F]{6}$/i.test(hexValue)) {
            colorPicker.val(hexValue);
        }
    });

    // Initialize hex inputs with color picker values
    $('input[type="color"]').each(function() {
        var hexInput = $(this).siblings('input[type="text"]');
        hexInput.val($(this).val());
    });
    
    // Clear any existing backup data for this template to prevent content copying
    const spaceId = <?= $space->id ?>;
    const templateId = <?= $template->id ?>;
    const templateType = '<?= $template->template_type ?>';
    
    // Clear any existing backup data for this specific template
    if (typeof sessionStorage !== 'undefined') {
        sessionStorage.removeItem('email_template_header_' + spaceId + '_' + templateId + '_' + templateType);
        sessionStorage.removeItem('email_template_body_' + spaceId + '_' + templateId + '_' + templateType);
        sessionStorage.removeItem('email_template_footer_' + spaceId + '_' + templateId + '_' + templateType);
    }
    
    // Clear backup data for other template types to prevent cross-contamination
    const templateTypes = ['application_received', 'application_received_confirmation', 'application_accepted', 'application_declined'];
    templateTypes.forEach(function(type) {
        if (type !== templateType) {
            sessionStorage.removeItem('email_template_header_' + spaceId + '_' + templateId + '_' + type);
            sessionStorage.removeItem('email_template_body_' + spaceId + '_' + templateId + '_' + type);
            sessionStorage.removeItem('email_template_footer_' + spaceId + '_' + templateId + '_' + type);
        }
    });
});
</script>
