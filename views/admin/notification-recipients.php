<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use humhub\modules\user\widgets\Image as UserImage;

$this->title = 'Email Notification Recipients';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Html::encode($this->title) ?></strong>
    </div>
    <div class="panel-body">
        <p>Select which space administrators should receive email notifications when new membership applications are submitted.</p>
        
        <?php $form = ActiveForm::begin([
            'action' => $space->createUrl('/space-join-questions/admin/save-recipients'),
            'method' => 'post',
        ]); ?>
        
        <div class="form-group">
            <label>Space Administrators:</label>
            <?php if (empty($admins)): ?>
                <p class="text-muted">No administrators found for this space.</p>
            <?php else: ?>
                <div class="checkbox-list">
                    <?php foreach ($admins as $admin): ?>
                        <div class="checkbox" style="margin-bottom: 15px; padding: 10px; border: 1px solid #eee; border-radius: 4px;">
                            <label style="display: flex; align-items: center; margin-bottom: 0;">
                                <?= Html::checkbox('recipients[]', in_array($admin->id, $selectedUserIds), [
                                    'value' => $admin->id,
                                    'id' => 'recipient_' . $admin->id,
                                    'style' => 'margin-right: 10px;'
                                ]) ?>
                                <div style="flex: 1;">
                                    <?= UserImage::widget(['user' => $admin, 'width' => 32, 'htmlOptions' => ['class' => 'img-rounded', 'style' => 'margin-right: 10px;']]) ?>
                                    <strong><?= Html::encode($admin->displayName) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= Html::encode($admin->email) ?></small>
                                    <?php if ($admin->id === $space->created_by): ?>
                                        <span class="label label-primary" style="margin-left: 5px;">Owner</span>
                                    <?php else: ?>
                                        <span class="label label-info" style="margin-left: 5px;">Admin</span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <?= Html::submitButton('Save Recipients', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Back to Settings', $space->createUrl('/space-join-questions/admin/settings'), ['class' => 'btn btn-default']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>