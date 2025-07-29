<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\spaceJoinQuestions\models\EmailTemplate;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $templates EmailTemplate[] */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Email Templates');

// Helper function to get template descriptions
function getTemplateDescription($type) {
    switch ($type) {
        case EmailTemplate::TYPE_APPLICATION_RECEIVED:
            return Yii::t('SpaceJoinQuestionsModule.base', 'a new application is received (sent to admins)');
        case EmailTemplate::TYPE_APPLICATION_ACCEPTED:
            return Yii::t('SpaceJoinQuestionsModule.base', 'an application is accepted (sent to user)');
        case EmailTemplate::TYPE_APPLICATION_DECLINED:
            return Yii::t('SpaceJoinQuestionsModule.base', 'an application is declined (sent to user)');
        default:
            return '';
    }
}
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?= $this->title ?></h4>
        <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Customize email templates for membership applications. Use the variables below in your templates.') ?></p>
    </div>
    
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= Yii::t('SpaceJoinQuestionsModule.base', 'Template Type') ?></th>
                                <th><?= Yii::t('SpaceJoinQuestionsModule.base', 'Status') ?></th>
                                <th><?= Yii::t('SpaceJoinQuestionsModule.base', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (EmailTemplate::getTemplateTypeOptions() as $type => $label): ?>
                                <?php $template = EmailTemplate::findBySpaceAndType($space->id, $type); ?>
                                <tr>
                                    <td>
                                        <strong><?= Html::encode($label) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'Email sent when: {description}', [
                                                'description' => getTemplateDescription($type)
                                            ]) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($template && $template->is_active): ?>
                                            <span class="label label-success"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Active') ?></span>
                                        <?php elseif ($template): ?>
                                            <span class="label label-warning"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Inactive') ?></span>
                                        <?php else: ?>
                                            <span class="label label-default"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Default') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?= Html::a(
                                                '<i class="fa fa-edit"></i> ' . Yii::t('SpaceJoinQuestionsModule.base', 'Edit'),
                                                $space->createUrl('/space-join-questions/email-template/edit', ['type' => $type]),
                                                ['class' => 'btn btn-sm btn-primary']
                                            ) ?>
                                            
                                            <?= Html::a(
                                                '<i class="fa fa-eye"></i> ' . Yii::t('SpaceJoinQuestionsModule.base', 'Preview'),
                                                $space->createUrl('/space-join-questions/email-template/preview', ['type' => $type]),
                                                ['class' => 'btn btn-sm btn-info', 'target' => '_blank']
                                            ) ?>
                                            
                                            <?php if ($template): ?>
                                                <?= Html::a(
                                                    '<i class="fa fa-toggle-' . ($template->is_active ? 'on' : 'off') . '"></i> ' . 
                                                    ($template->is_active ? Yii::t('SpaceJoinQuestionsModule.base', 'Disable') : Yii::t('SpaceJoinQuestionsModule.base', 'Enable')),
                                                    $space->createUrl('/space-join-questions/email-template/toggle', ['type' => $type]),
                                                    [
                                                        'class' => 'btn btn-sm ' . ($template->is_active ? 'btn-warning' : 'btn-success'),
                                                        'data-method' => 'post',
                                                        'data-confirm' => Yii::t('SpaceJoinQuestionsModule.base', 'Are you sure?')
                                                    ]
                                                ) ?>
                                                
                                                <?= Html::a(
                                                    '<i class="fa fa-refresh"></i> ' . Yii::t('SpaceJoinQuestionsModule.base', 'Reset'),
                                                    $space->createUrl('/space-join-questions/email-template/reset', ['type' => $type]),
                                                    [
                                                        'class' => 'btn btn-sm btn-default',
                                                        'data-method' => 'post',
                                                        'data-confirm' => Yii::t('SpaceJoinQuestionsModule.base', 'Reset to default template?')
                                                    ]
                                                ) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h5><i class="fa fa-info-circle"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Available Variables') ?></h5>
                    </div>
                    <div class="panel-body">
                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Use these variables in your templates:') ?></strong></p>
                        
                        <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'General Variables') ?></h6>
                        <ul class="list-unstyled">
                            <li><code>{space_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Space name') ?></li>
                            <li><code>{admin_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Admin name') ?></li>
                            <li><code>{user_name}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'User name') ?></li>
                            <li><code>{user_email}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'User email') ?></li>
                        </ul>
                        
                        <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'Date Variables') ?></h6>
                        <ul class="list-unstyled">
                            <li><code>{application_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Application submission date') ?></li>
                            <li><code>{accepted_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Acceptance date') ?></li>
                            <li><code>{declined_date}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Decline date') ?></li>
                        </ul>
                        
                        <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'Content Variables') ?></h6>
                        <ul class="list-unstyled">
                            <li><code>{application_answers}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'User answers to questions') ?></li>
                            <li><code>{decline_reason}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Reason for decline') ?></li>
                            <li><code>{admin_notes}</code> - <?= Yii::t('SpaceJoinQuestionsModule.base', 'Admin notes') ?></li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Note:') ?></strong>
                            <?= Yii::t('SpaceJoinQuestionsModule.base', 'HTML is supported in email templates.') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 