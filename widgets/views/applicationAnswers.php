<?php

use humhub\libs\Html;

/* @var $this yii\web\View */
/* @var $membership humhub\modules\space\models\Membership */
/* @var $answers humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer[] */
/* @var $hasAnswers boolean */
?>

<div class="application-answers">
    <h4><?= Yii::t('SpaceJoinQuestionsModule.base', 'Answers to Join Questions') ?></h4>

    <?php if (!$hasAnswers): ?>
        <div class="alert alert-info">
            <?= Yii::t('SpaceJoinQuestionsModule.base', 'No custom questions were configured when this application was submitted.') ?>
        </div>
    <?php else: ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?php foreach ($answers as $answer): ?>
                    <div class="answer-item" style="margin-bottom: 20px;">
                        <strong><?= Html::encode($answer->question->question_text) ?></strong>
                        <?php if ($answer->question->is_required): ?>
                            <span class="label label-danger"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Required') ?></span>
                        <?php endif; ?>

                        <div style="margin-top: 5px; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #ddd;">
                            <?= $answer->getFormattedAnswer() ?>
                        </div>

                        <small class="text-muted">
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Field Type: {type}', [
                                'type' => $answer->question->getFieldTypeLabel()
                            ]) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
