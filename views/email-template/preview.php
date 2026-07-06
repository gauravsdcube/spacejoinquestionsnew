<?php

use humhub\libs\Html;

/* @var $this yii\web\View */
/* @var $space humhub\modules\space\models\Space */
/* @var $template EmailTemplate */
/* @var $processed array */
/* @var $variables array */

$this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Email Template Preview');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?= $this->title ?>: <?= Html::encode($template->getTemplateTypeLabel()) ?></h4>
        <p class="help-block"><?= Yii::t('SpaceJoinQuestionsModule.base', 'This is how your email will look with sample data.') ?></p>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <div class="email-preview">
                    <div class="email-header">
                        <strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Subject:') ?></strong>
                        <span class="email-subject"><?= Html::encode($processed['subject']) ?></span>
                    </div>

                    <hr>

                    <div class="email-body">
                        <?= $processed['body'] ?>
                    </div>
                </div>

                <div class="text-center" style="margin-top: 20px;">
                    <?= Html::a(
                        '<i class="fa fa-edit"></i> ' . Yii::t('SpaceJoinQuestionsModule.base', 'Edit Template'),
                        $space->createUrl('/space-join-questions/email-template/edit', ['type' => $template->template_type]),
                        ['class' => 'btn btn-primary']
                    ) ?>

                    <?= Html::a(
                        '<i class="fa fa-arrow-left"></i> ' . Yii::t('SpaceJoinQuestionsModule.base', 'Back to Templates'),
                        $space->createUrl('/space-join-questions/email-template'),
                        ['class' => 'btn btn-default']
                    ) ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h5><i class="fa fa-info-circle"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Sample Data Used') ?></h5>
                    </div>
                    <div class="panel-body">
                        <p><?= Yii::t('SpaceJoinQuestionsModule.base', 'The following sample data was used for this preview:') ?></p>

                        <dl class="dl-horizontal">
                            <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Space Name:') ?></dt>
                            <dd><?= Html::encode($variables['space_name']) ?></dd>

                            <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Admin Name:') ?></dt>
                            <dd><?= Html::encode($variables['admin_name']) ?></dd>

                            <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'User Name:') ?></dt>
                            <dd><?= Html::encode($variables['user_name']) ?></dd>

                            <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'User Email:') ?></dt>
                            <dd><?= Html::encode($variables['user_email']) ?></dd>

                            <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Date:') ?></dt>
                            <dd><?= Html::encode($variables['application_date']) ?></dd>

                            <?php if (isset($variables['accepted_date'])): ?>
                                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Accepted Date:') ?></dt>
                                <dd><?= Html::encode($variables['accepted_date']) ?></dd>
                            <?php endif; ?>

                            <?php if (isset($variables['declined_date'])): ?>
                                <dt><?= Yii::t('SpaceJoinQuestionsModule.base', 'Declined Date:') ?></dt>
                                <dd><?= Html::encode($variables['declined_date']) ?></dd>
                            <?php endif; ?>
                        </dl>

                        <?php if (isset($variables['application_answers'])): ?>
                            <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'Application Answers:') ?></h6>
                            <pre style="font-size: 12px; background: #f5f5f5; padding: 10px;"><?= Html::encode($variables['application_answers']) ?></pre>
                        <?php endif; ?>

                        <?php if (isset($variables['decline_reason'])): ?>
                            <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'Decline Reason:') ?></h6>
                            <p><?= Html::encode($variables['decline_reason']) ?></p>
                        <?php endif; ?>

                        <?php if (isset($variables['admin_notes'])): ?>
                            <h6><?= Yii::t('SpaceJoinQuestionsModule.base', 'Admin Notes:') ?></h6>
                            <p><?= Html::encode($variables['admin_notes']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h5><i class="fa fa-exclamation-triangle"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Important Notes') ?></h5>
                    </div>
                    <div class="panel-body">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-check"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'HTML formatting is supported') ?></li>
                            <li><i class="fa fa-check"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Variables will be replaced with real data') ?></li>
                            <li><i class="fa fa-check"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Emails are sent in HTML format') ?></li>
                        </ul>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5><i class="fa fa-code"></i> <?= Yii::t('SpaceJoinQuestionsModule.base', 'Debug Information') ?></h5>
                    </div>
                    <div class="panel-body">
                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Raw HTML Content:') ?></strong></p>
                        <pre style="font-size: 11px; background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;"><?= Html::encode($processed['body']) ?></pre>
                        
                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Link Count:') ?></strong> 
                        <?php 
                        $linkCount = substr_count($processed['body'], '<a ');
                        echo $linkCount > 0 ? $linkCount . ' ' . Yii::t('SpaceJoinQuestionsModule.base', 'links found') : Yii::t('SpaceJoinQuestionsModule.base', 'No links found');
                        ?>
                        </p>
                        
                        <p><strong><?= Yii::t('SpaceJoinQuestionsModule.base', 'Visible Links in Preview:') ?></strong> 
                        <span id="visible-link-count"><?= Yii::t('SpaceJoinQuestionsModule.base', 'Calculating...') ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.email-preview {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    background: #f9f9f9;
    margin-bottom: 20px;
}

.email-header {
    background: #fff;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.email-subject {
    color: #333;
    font-weight: bold;
    margin-left: 10px;
}

.email-body {
    background: #fff;
    padding: 20px;
    border-radius: 4px;
    line-height: 1.6;
}

/* Ensure links are visible and properly styled in preview */
.email-body a {
    color: #dd0031 !important;
    text-decoration: underline !important;
    cursor: pointer;
}

.email-body a:hover {
    color: #b30026 !important;
    text-decoration: underline !important;
}

.email-body a:visited {
    color: #8b0022 !important;
}

/* Ensure links in header and footer are also visible */
.email-body a[href^="http"] {
    color: #dd0031 !important;
    text-decoration: underline !important;
}

/* Additional styling to ensure links are visible */
.email-body a {
    display: inline !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Force link styling even if overridden by other CSS */
.email-body a:link {
    color: #dd0031 !important;
    text-decoration: underline !important;
}

.email-body a:active {
    color: #ff0000 !important;
}

/* Ensure links are clickable */
.email-body a {
    pointer-events: auto !important;
    cursor: pointer !important;
}
</style>

<script>
$(document).ready(function() {
    // Count visible links in the email preview
    var visibleLinks = $('.email-body a').length;
    $('#visible-link-count').text(visibleLinks + ' links visible');
    
    // Add click handlers to test links
    $('.email-body a').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        alert('Link clicked: ' + url + '\n\nThis would open in a new tab in the actual email.');
    });
    
    // Highlight links on hover for debugging
    $('.email-body a').hover(
        function() {
            $(this).css('background-color', '#ffffcc');
        },
        function() {
            $(this).css('background-color', '');
        }
    );
});
</script>
