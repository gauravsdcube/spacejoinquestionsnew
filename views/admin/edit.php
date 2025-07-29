<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\widgets\Button;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $model humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Edit Question');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id' => 'question-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => false,
        ]); ?>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'question_text')->textarea([
                    'rows' => 3,
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your question here...'),
                    'maxlength' => 500
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'field_type')->dropDownList(
                    SpaceJoinQuestion::getFieldTypeOptions(),
                    [
                        'id' => 'field-type-select',
                    ]
                ) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'sort_order')->textInput([
                    'type' => 'number',
                    'min' => 0,
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter sort order (0 = first)'),
                ])->hint(Yii::t('SpaceJoinQuestionsModule.base', 'Lower numbers appear first.')) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'is_required')->checkbox([
                    'label' => Yii::t('SpaceJoinQuestionsModule.base', 'This question is required'),
                ]) ?>
            </div>
        </div>

        <div class="row" id="field-options-row">
            <div class="col-md-12">
                <?= $form->field($model, 'field_options')->textarea([
                    'rows' => 4,
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter options (one per line)'),
                ])->hint(Yii::t('SpaceJoinQuestionsModule.base', 'For dropdown and radio button fields, enter each option on a new line.')) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Update Question'))
                ->submit()
                ->icon('save') ?>
            
            <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Cancel'))
                ->link($space->createUrl('/space-join-questions/admin/index'))
                ->icon('times') ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide field options based on field type
    function toggleFieldOptions() {
        var fieldType = $('#field-type-select').val();
        if (fieldType === 'select' || fieldType === 'radio') {
            $('#field-options-row').show();
        } else {
            $('#field-options-row').hide();
        }
    }

    $('#field-type-select').change(toggleFieldOptions);
    toggleFieldOptions(); // Run on page load
});
</script>
