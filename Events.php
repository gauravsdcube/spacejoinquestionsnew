<?php

namespace humhub\modules\spaceJoinQuestions;

use Yii;
use yii\helpers\Html;
use yii\web\Application as WebApplication;
use humhub\modules\space\widgets\Menu;
use humhub\modules\space\widgets\MembershipButton;
use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\controllers\MemberController;

/**
 * Event handlers for Space Join Questions module
 */
class Events
{
    /**
     * Add menu items to space admin menu
     */
    public static function onSpaceMenuInit($event)
    {
        /** @var Menu $menu */
        $menu = $event->sender;
        $space = $menu->space;

        if (!$space->isAdmin()) {
            return;
        }

        if (!$space->moduleManager->isEnabled('space-join-questions')) {
            return;
        }

        // Add Questions Management menu item
        $menu->addItem([
            'label' => Yii::t('SpaceJoinQuestionsModule.base', 'Join Questions'),
            'url' => $space->createUrl('/space-join-questions/admin/index'),
            'icon' => '<i class="fa fa-question-circle"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id === 'space-join-questions'),
            'sortOrder' => 500,
        ]);

        // Add Applications menu item
        $menu->addItem([
            'label' => Yii::t('SpaceJoinQuestionsModule.base', 'Membership Applications'),
            'url' => $space->createUrl('/space-join-questions/admin/applications'),
            'icon' => '<i class="fa fa-users"></i>',
            'isActive' => (Yii::$app->controller->id === 'admin' && Yii::$app->controller->action->id === 'applications'),
            'sortOrder' => 501,
        ]);

        // Add Email Templates menu item
        $menu->addItem([
            'label' => Yii::t('SpaceJoinQuestionsModule.base', 'Email Templates'),
            'url' => $space->createUrl('/space-join-questions/email-template'),
            'icon' => '<i class="fa fa-envelope"></i>',
            'isActive' => (Yii::$app->controller->id === 'email-template'),
            'sortOrder' => 502,
        ]);


    }

    /**
     * Extend membership button to include custom form when questions exist
     */
    public static function onMembershipButtonInit($event)
    {
        /** @var MembershipButton $widget */
        $widget = $event->sender;
        $space = $widget->space;

        if (!$space->moduleManager->isEnabled('space-join-questions')) {
            return;
        }

        // Check if space has custom questions
        $hasQuestions = \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->exists();

        if ($hasQuestions) {
            // Replace default membership request URL with our custom one
            $widget->options['requestMembership']['url'] = $space->createUrl('/space-join-questions/membership/request');
        }
    }

    /**
     * Validate custom questions before membership insertion
     */
    public static function onMembershipBeforeInsert($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        // Only validate for membership requests (not invites)
        if ($membership->status !== Membership::STATUS_APPLICANT) {
            return;
        }

        if (!Yii::$app instanceof WebApplication) {
            return;
        }

        $space = $membership->space;
        if (!$space || !$space->moduleManager->isEnabled('space-join-questions')) {
            return;
        }

        // Get required questions
        $requiredQuestions = \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id, 'is_required' => 1])
            ->all();

        // Validate required questions are answered
        foreach ($requiredQuestions as $question) {
            $answer = Yii::$app->request->post('question_' . $question->id);
            
            if (empty($answer)) {
                $membership->addError('questions', 
                    Yii::t('SpaceJoinQuestionsModule.base', 'Please answer the required question: {question}', [
                        'question' => $question->question_text
                    ])
                );
                $event->isValid = false;
            }
        }
    }

    /**
     * Save question answers after membership insertion
     */
    public static function onMembershipAfterInsert($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        // Only save answers for membership requests
        if ($membership->status !== Membership::STATUS_APPLICANT) {
            return;
        }

        if (!Yii::$app instanceof WebApplication) {
            return;
        }

        $space = $membership->space;
        if (!$space || !$space->moduleManager->isEnabled('space-join-questions')) {
            return;
        }

        // Get all questions for this space
        $questions = \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->all();

        // Save answers
        foreach ($questions as $question) {
            $answer = Yii::$app->request->post('question_' . $question->id);
            
            if (!empty($answer)) {
                $answerModel = new \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer();
                $answerModel->membership_id = $membership->id;
                $answerModel->question_id = $question->id;
                $answerModel->answer_text = is_array($answer) ? implode(', ', $answer) : $answer;
                $answerModel->save();
            }
        }

        // Notify space administrators about new application
        static::notifyAdminsAboutNewApplication($membership);
    }

    /**
     * Handle custom actions in member controller
     */
    public static function onMemberControllerBeforeAction($event)
    {
        /** @var MemberController $controller */
        $controller = $event->sender;
        
        // Add custom actions for approve/decline with reasons
        if (in_array($event->action->id, ['approve-with-questions', 'decline-with-reason'])) {
            // These actions will be handled by our custom controller
            return;
        }
    }

    /**
     * Notify space administrators about new membership application
     * 
     * @param Membership $membership
     */
    protected static function notifyAdminsAboutNewApplication($membership)
    {
        $space = $membership->space;
        
        // Check if email notifications are enabled for this space
        if (!$space->getSettings()->get('emailNotifications', 'spaceJoinQuestions', true)) {
            return;
        }
        
        // Get space administrators
        $admins = $space->getAdmins();
        
        // Get custom email template if available
        $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
            $space->id, 
            \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_RECEIVED
        );
        
        foreach ($admins as $admin) {
            if ($template && $template->is_active) {
                // Use custom template
                static::sendCustomEmail($membership, $admin, $template, 'application_received');
            } else {
                // Use default notification
                $notification = new notifications\ApplicationReceived();
                $notification->source = $membership;
                $notification->originator = $membership->user;
                $notification->send($admin);
            }
        }
    }

    /**
     * Send custom email using template
     * 
     * @param Membership $membership
     * @param User $recipient
     * @param EmailTemplate $template
     * @param string $type
     */
    protected static function sendCustomEmail($membership, $recipient, $template, $type)
    {
        $space = $membership->space;
        $user = $membership->user;
        
        // Prepare variables
        $variables = [
            'space_name' => $space->name,
            'admin_name' => $recipient->displayName,
            'user_name' => $user->displayName,
            'user_email' => $user->email,
            'application_date' => $membership->created_at, // Already in correct format
        ];
        
        // Add application answers if available
        $answers = \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer::find()
            ->where(['membership_id' => $membership->id])
            ->with('question')
            ->all();
            
        if (!empty($answers)) {
            $answersText = '';
            foreach ($answers as $answer) {
                $answersText .= "Q: " . $answer->question->question_text . "\n";
                $answersText .= "A: " . $answer->answer_text . "\n\n";
            }
            $variables['application_answers'] = trim($answersText);
        } else {
            $variables['application_answers'] = Yii::t('SpaceJoinQuestionsModule.base', 'No answers provided.');
        }
        
        // Process template with recipient user for proper file token generation
        $processed = $template->processTemplate($variables, $recipient);
        
        // Send email
        $mail = Yii::$app->mailer->compose()
            ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')])
            ->setTo($recipient->email)
            ->setSubject($processed['subject'])
            ->setHtmlBody($processed['body']);
            
        $mail->send();
    }
}