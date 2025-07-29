<?php

use yii\helpers\Html;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $questions humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion[] */

if (empty($questions)) {
    return;
}
?>

<div class="custom-questions-section">
    <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Please answer the following questions:') ?></h4>
    
    <?php foreach ($questions as $question): ?>
        <div class="form-group">
            <?= Html::label(
                Html::encode($question->question_text) . 
                ($question->is_required ? ' <span class="required">*</span>' : ''),
                "question_{$question->id}",
                ['class' => 'control-label']
            ) ?>
            
            <?php if ($question->field_type === SpaceJoinQuestion::FIELD_TYPE_TEXT): ?>
                <?= Html::textarea("question_{$question->id}", '', [
                    'id' => "question_{$question->id}",
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your answer here...'),
                    'required' => $question->is_required,
                ]) ?>
                
            <?php elseif ($question->field_type === SpaceJoinQuestion::FIELD_TYPE_SELECT): ?>
                <?php 
                $options = [];
                $options[''] = Yii::t('SpaceJoinQuestionsModule.base', '-- Select an option --');
                
                if (!empty($question->field_options)) {
                    $optionLines = explode("\n", trim($question->field_options));
                    foreach ($optionLines as $option) {
                        $option = trim($option);
                        if (!empty($option)) {
                            $options[$option] = Html::encode($option);
                        }
                    }
                }
                ?>
                <?= Html::dropDownList("question_{$question->id}", '', $options, [
                    'id' => "question_{$question->id}",
                    'class' => 'form-control',
                    'required' => $question->is_required,
                ]) ?>
                
            <?php elseif ($question->field_type === SpaceJoinQuestion::FIELD_TYPE_RADIO): ?>
                <?php 
                $options = [];
                if (!empty($question->field_options)) {
                    $optionLines = explode("\n", trim($question->field_options));
                    foreach ($optionLines as $option) {
                        $option = trim($option);
                        if (!empty($option)) {
                            $options[$option] = Html::encode($option);
                        }
                    }
                }
                ?>
                <?php foreach ($options as $value => $label): ?>
                    <div class="radio">
                        <label>
                            <?= Html::radio("question_{$question->id}", false, [
                                'value' => $value,
                                'required' => $question->is_required,
                            ]) ?>
                            <?= $label ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <?= Html::textInput("question_{$question->id}", '', [
                    'id' => "question_{$question->id}",
                    'class' => 'form-control',
                    'placeholder' => Yii::t('SpaceJoinQuestionsModule.base', 'Enter your answer here...'),
                    'required' => $question->is_required,
                ]) ?>
            <?php endif; ?>
            
            <?php if ($question->is_required): ?>
                <div class="help-block">
                    <span class="text-danger"><?= Yii::t('SpaceJoinQuestionsModule.base', 'This question is required.') ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>