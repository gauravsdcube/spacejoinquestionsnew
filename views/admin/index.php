<?php

use humhub\libs\Html;
use humhub\widgets\Button;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $questions humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion[] */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Join Questions');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
        <div class="pull-right">
            <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Add Question'))
                ->link($space->createUrl('/space-join-questions/admin/create'))
                ->icon('plus') ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="panel-body">
        <?php if (empty($questions)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'No custom questions configured yet.') ?>
                <br><br>
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Create custom questions that users must answer when requesting to join this space.') ?>
                <br><br>
                <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Create Your First Question'))
                    ->link($space->createUrl('/space-join-questions/admin/create'))
                    ->icon('plus') ?>
            </div>
        <?php else: ?>
            <p class="text-muted">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Manage custom questions for membership requests. Drag and drop to reorder.') ?>
            </p>

            <div class="question-list" id="sortable-questions">
                <?php foreach ($questions as $question): ?>
                    <div class="question-item" data-question-id="<?= $question->id ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-1">
                                        <span class="badge badge-info" style="font-size: 10px; padding: 2px 6px;"><?= $question->sort_order ?></span>
                                    </div>
                                    <div class="col-md-11">
                                        <strong><?= Html::encode($question->question_text) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= $question->getFieldTypeLabel() ?>
                                            <?php if ($question->is_required): ?>
                                                <span class="label label-danger"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Required') ?></span>
                                            <?php else: ?>
                                                <span class="label label-default"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Optional') ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="question-actions">
                                    <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Edit'))
                                        ->link($space->createUrl('/space-join-questions/admin/edit', ['id' => $question->id]))
                                        ->icon('edit')
                                        ->sm() ?>

                                    <?= Html::beginForm($space->createUrl('/space-join-questions/admin/delete', ['id' => $question->id]), 'POST', ['style' => 'display: inline;']) ?>
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                        <?= Button::danger(Yii::t('SpaceJoinQuestionsModule.base', 'Delete'))
                                            ->submit()
                                            ->icon('trash')
                                            ->confirm(Yii::t('SpaceJoinQuestionsModule.base', 'Are you sure you want to delete this question?'))
                                            ->sm() ?>
                                    <?= Html::endForm() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center" style="margin-top: 20px;">
                <?= Button::primary(Yii::t('SpaceJoinQuestionsModule.base', 'Add Another Question'))
                    ->link($space->createUrl('/space-join-questions/admin/create'))
                    ->icon('plus') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?= Button::defaultType(Yii::t('SpaceJoinQuestionsModule.base', 'Go Back'))
                    ->link($space->createUrl('/space-join-questions/membership/index'))
                    ->icon('arrow-left') ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($questions)): ?>
<script <?= Html::nonce() ?>>
// Make questions sortable
$(document).ready(function() {
    $("#sortable-questions").sortable({
        handle: '.question-item',
        cursor: 'move',
        update: function(event, ui) {
            var questionIds = [];
            $('#sortable-questions .question-item').each(function() {
                questionIds.push($(this).data('question-id'));
            });

            $.post('<?= $space->createUrl('/space-join-questions/admin/sort') ?>', {
                questions: questionIds
            });
        }
    });
});
</script>
<?php endif; ?>
