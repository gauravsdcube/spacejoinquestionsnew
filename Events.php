<?php

namespace humhub\modules\spaceJoinQuestions;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\controllers\MemberController;
use humhub\modules\space\widgets\MembershipButton;
use humhub\modules\space\widgets\Menu;
use Yii;
use yii\web\Application as WebApplication;

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

        // Count pending applications
        $pendingApplicationsCount = \humhub\modules\space\models\Membership::find()
            ->where([
                'space_id' => $space->id, 
                'status' => \humhub\modules\space\models\Membership::STATUS_APPLICANT
            ])
            ->count();

        // Add Manage Membership menu item with badge if there are pending applications
        $membershipLabel = Yii::t('SpaceJoinQuestionsModule.base', 'Manage Membership');
        if ($pendingApplicationsCount > 0) {
            $membershipLabel .= '&nbsp;&nbsp;<span class="label label-warning">' . $pendingApplicationsCount . '</span>';
        }

        $menu->addItem([
            'label' => $membershipLabel,
            'url' => $space->createUrl('/space-join-questions/membership/index'),
            'icon' => '<i class="fa fa-users"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id === 'space-join-questions' && Yii::$app->controller->id === 'membership'),
            'sortOrder' => 20000,
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

        // Allow other types of membership requests
        if ($space->join_policy !== Space::JOIN_POLICY_APPLICATION) {
            return;
        }

        // Get required questions
        $requiredQuestions = \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id, 'is_required' => 1])
            ->all();

        // Validate required questions
        foreach ($requiredQuestions as $question) {
            $answer = Yii::$app->request->post('question_' . $question->id);
            if (empty($answer)) {
                $membership->addError('request_message', Yii::t('SpaceJoinQuestionsModule.base', 'Please answer all required questions.'));
                $event->isValid = false;
                return;
            }
        }
    }

    /**
     * Save custom question answers after membership insertion
     */
    public static function onMembershipAfterInsert($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        // Only handle membership requests (not invites)
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

        // Note: Answers are now saved in the controller's actionRequest method
        // This prevents double-saving of answers

        // Email notification is now sent from the controller after answers are saved
        // to prevent race condition where answers are not available yet
    }

    /**
     * Clean up answers when membership is deleted
     * This handles scenarios like:
     * - User cancels application and resubmits
     * - User leaves space and rejoins
     * - Application is declined and user reapplies
     */
    public static function onMembershipBeforeDelete($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        // Clean up any existing answers for this membership
        \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer::deleteAll(['membership_id' => $membership->id]);
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
    public static function notifyAdminsAboutNewApplication($membership)
    {
        $space = $membership->space;
        
        // Check if email notifications are enabled for this space
        // Try both with and without module name to handle migration issues
        $settings = $space->getSettings();
        $emailNotifications = $settings->get('emailNotifications', 'spaceJoinQuestions', true);
        
        // If not found with module name, try without module name
        if ($emailNotifications === true) {
            $emailNotifications = $settings->get('emailNotifications', true);
        }
        
        if (!$emailNotifications) {
            return;
        }
        
        // Get custom notification recipients
        $recipients = \humhub\modules\spaceJoinQuestions\models\SpaceJoinNotificationRecipient::getRecipientsForSpace($space->id);
        
        if (empty($recipients)) {
            // Fallback to all space administrators if no custom recipients
            $recipients = $space->getAdmins();
        } else {
            // Convert to user objects
            $recipients = array_map(function($recipient) {
                return $recipient->user;
            }, $recipients);
        }

        // Get custom email template if available
        $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
            $space->id,
            \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_RECEIVED
        );

        foreach ($recipients as $admin) {
            try {
                if ($template && $template->is_active) {
                    // Use custom template
                    static::sendCustomEmail($membership, $admin, $template, 'application_received');
                } else {
                    // Use default notification
                    $notification = new notifications\ApplicationReceived();
                    $notification->source = $membership;
                    $notification->originator = $membership->user;
                    
                    // Send directly without queue
                    $notification->sendDirect($admin);
                }
            } catch (\Exception $e) {
                Yii::error('Error sending notification to admin: ' . $e->getMessage());
                // Continue with other admins even if one fails
            }
        }
    }

    /**
     * Send custom email notifications
     *
     * @param Membership $membership
     * @param User $recipient
     * @param string $template
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
