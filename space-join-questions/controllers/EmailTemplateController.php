<?php

namespace humhub\modules\spaceJoinQuestions\controllers;

use Yii;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\spaceJoinQuestions\models\EmailTemplate;
use humhub\modules\spaceJoinQuestions\permissions\ManageQuestions;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\AccessControl;

/**
 * EmailTemplateController handles email template management
 */
class EmailTemplateController extends SpaceController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::class,
                'guestAllowedActions' => [],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if user is space admin
        if (!$this->contentContainer->isAdmin()) {
            throw new HttpException(403, Yii::t('SpaceJoinQuestionsModule.base', 'Access denied - You must be a space administrator'));
        }

        return true;
    }

    /**
     * Lists all email templates for the current space
     */
    public function actionIndex()
    {
        $space = $this->contentContainer;
        
        $templates = EmailTemplate::find()
            ->where(['space_id' => $space->id])
            ->orderBy(['template_type' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'space' => $space,
            'templates' => $templates,
        ]);
    }

    /**
     * Creates or updates an email template
     */
    public function actionEdit($type = null)
    {
        $space = $this->contentContainer;
        
        if ($type === null) {
            throw new NotFoundHttpException('Template type is required.');
        }

        $template = EmailTemplate::findBySpaceAndType($space->id, $type);
        
        if (!$template) {
            $template = new EmailTemplate();
            $template->space_id = $space->id;
            $template->template_type = $type;
            $template->is_active = 1;
            
            // Load default template
            $default = EmailTemplate::getDefaultTemplate($type);
            $template->subject = $default['subject'];
            $template->header = $default['header'];
            $template->body = $default['body'];
            $template->footer = $default['footer'];
            $template->header_bg_color = $default['header_bg_color'];
            $template->footer_bg_color = $default['footer_bg_color'];
            $template->header_font_color = $default['header_font_color'];
            $template->footer_font_color = $default['footer_font_color'];
        }

        if ($template->load(Yii::$app->request->post()) && $template->save()) {
            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Email template saved successfully.'));
            return $this->redirect($space->createUrl('/space-join-questions/email-template'));
        }

        return $this->render('edit', [
            'space' => $space,
            'template' => $template,
            'templateTypeOptions' => EmailTemplate::getTemplateTypeOptions(),
        ]);
    }

    /**
     * Preview an email template
     */
    public function actionPreview($type)
    {
        $space = $this->contentContainer;
        
        $template = EmailTemplate::findBySpaceAndType($space->id, $type);
        
        if (!$template) {
            // Use default template
            $default = EmailTemplate::getDefaultTemplate($type);
            $template = new EmailTemplate();
            $template->subject = $default['subject'];
            $template->body = $default['body'];
        }

        // Sample variables for preview
        $variables = [
            'admin_name' => Yii::$app->user->identity->displayName,
            'user_name' => 'John Doe',
            'user_email' => 'john.doe@example.com',
            'space_name' => $space->name,
            'application_date' => date('Y-m-d H:i:s'),
            'accepted_date' => date('Y-m-d H:i:s'),
            'declined_date' => date('Y-m-d H:i:s'),
            'application_answers' => "Q1: What is your experience?\nA1: I have 5 years of experience in this field.\n\nQ2: Why do you want to join?\nA2: I want to learn and contribute to the community.",
            'decline_reason' => 'Application does not meet our current requirements.',
            'admin_notes' => 'Thank you for your interest. Please review our guidelines and consider applying again.',
        ];

        $processed = $template->processTemplate($variables, Yii::$app->user->identity, true);

        return $this->render('preview', [
            'space' => $space,
            'template' => $template,
            'processed' => $processed,
            'variables' => $variables,
        ]);
    }

    /**
     * Toggle template active status
     */
    public function actionToggle($type)
    {
        $space = $this->contentContainer;
        
        $template = EmailTemplate::findBySpaceAndType($space->id, $type);
        
        if (!$template) {
            // Create template with default content
            $default = EmailTemplate::getDefaultTemplate($type);
            $template = new EmailTemplate();
            $template->space_id = $space->id;
            $template->template_type = $type;
            $template->subject = $default['subject'];
            $template->header = $default['header'];
            $template->body = $default['body'];
            $template->footer = $default['footer'];
            $template->header_bg_color = $default['header_bg_color'];
            $template->footer_bg_color = $default['footer_bg_color'];
            $template->header_font_color = $default['header_font_color'];
            $template->footer_font_color = $default['footer_font_color'];
            $template->is_active = 1;
        } else {
            $template->is_active = !$template->is_active;
        }

        if ($template->save()) {
            $status = $template->is_active ? 'enabled' : 'disabled';
            Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Email template {status}.', ['status' => $status]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to update email template.'));
        }

        return $this->redirect($space->createUrl('/space-join-questions/email-template'));
    }

    /**
     * Reset template to default
     */
    public function actionReset($type)
    {
        $space = $this->contentContainer;
        
        $template = EmailTemplate::findBySpaceAndType($space->id, $type);
        
        if ($template) {
            $default = EmailTemplate::getDefaultTemplate($type);
            $template->subject = $default['subject'];
            $template->header = $default['header'];
            $template->body = $default['body'];
            $template->footer = $default['footer'];
            $template->header_bg_color = $default['header_bg_color'];
            $template->footer_bg_color = $default['footer_bg_color'];
            $template->header_font_color = $default['header_font_color'];
            $template->footer_font_color = $default['footer_font_color'];
            
            if ($template->save()) {
                Yii::$app->session->setFlash('success', Yii::t('SpaceJoinQuestionsModule.base', 'Email template reset to default.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('SpaceJoinQuestionsModule.base', 'Failed to reset email template.'));
            }
        }

        return $this->redirect($space->createUrl('/space-join-questions/email-template'));
    }
} 