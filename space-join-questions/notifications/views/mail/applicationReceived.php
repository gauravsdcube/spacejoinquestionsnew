<?php

use humhub\libs\Html;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer;

/* @var $this yii\web\View */
/* @var $membership humhub\modules\space\models\Membership */
/* @var $originator humhub\modules\user\models\User */
/* @var $space humhub\modules\space\models\Space */

$membership = $this->context->source;
$originator = $this->context->originator;
$space = $membership->space;

// Get answers for this membership
$answers = SpaceJoinAnswer::find()
    ->where(['membership_id' => $membership->id])
    ->with(['question'])
    ->all();

?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #21A1B3; margin-bottom: 20px;">
        <?= Yii::t('SpaceJoinQuestionsModule.base', 'New Membership Application') ?>
    </h2>
    
    <p style="margin-bottom: 20px;">
        <?= Yii::t('SpaceJoinQuestionsModule.base', 'Hello,') ?>
    </p>
    
    <p style="margin-bottom: 20px;">
        <?= Yii::t('SpaceJoinQuestionsModule.base', '{userName} has submitted a membership application for the space "{spaceName}".', [
            'userName' => Html::encode($originator->displayName),
            'spaceName' => Html::encode($space->name)
        ]) ?>
    </p>
    
    <?php if (!empty($answers)): ?>
        <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #333;">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Answers') ?>
            </h3>
            
            <?php foreach ($answers as $answer): ?>
                <div style="margin-bottom: 15px;">
                    <strong style="color: #333;">
                        <?= Html::encode($answer->question->question_text) ?>
                    </strong>
                    <p style="margin: 5px 0 0 0; color: #666;">
                        <?= Html::encode($answer->answer_text) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($membership->request_message)): ?>
        <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #333;">
                <?= Yii::t('SpaceJoinQuestionsModule.base', 'Additional Message') ?>
            </h3>
            <p style="margin: 0; color: #666;">
                <?= Html::encode($membership->request_message) ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $space->createUrl('/space-join-questions/admin/applications') ?>" 
           style="background-color: #21A1B3; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Review Application') ?>
        </a>
    </div>
    
    <p style="margin-top: 30px; font-size: 12px; color: #999;">
        <?= Yii::t('SpaceJoinQuestionsModule.base', 'This email was sent to all space administrators.') ?>
    </p>
</div>
