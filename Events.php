<?php

namespace humhub\modules\spaceJoinQuestions;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\controllers\MemberController;
use humhub\modules\space\widgets\MembershipButton;
use humhub\modules\space\widgets\Menu;
use humhub\modules\user\models\GroupUser;
use Yii;
use yii\helpers\Json;
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

        // Add Manage Membership menu item with count when pending applications exist
        $membershipLabel = Yii::t('SpaceJoinQuestionsModule.base', 'Manage Membership');
        if ($pendingApplicationsCount > 0) {
            $membershipLabel .= ' (' . $pendingApplicationsCount . ')';
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

        $user = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
        if (static::shouldAskQuestionsForUser($space, $user)) {
            // Replace default membership request URL with our custom one
            $widget->options['requestMembership']['url'] = $space->createUrl('/space-join-questions/membership/request');
        } else {
            // For non-targeted groups, bypass approval and join directly.
            // Use a non-AJAX POST to avoid injecting membershipResult HTML.
            $widget->options['requestMembership']['url'] = $space->createUrl('/space/membership/request-membership');
            $widget->options['requestMembership']['attrs'] = array_merge(
                $widget->options['requestMembership']['attrs'] ?? [],
                [
                    'data-method' => 'POST',
                ],
            );
        }

        if (static::shouldRequireQuestionsOnInvite($space, $user)) {
            // Same modal pattern as Join / requestMembership (href + #globalModal).
            // Null out default invite-accept action attrs so ArrayHelper::merge does not
            // keep data-action-click="content.container.relationship" (full-page POST).
            $widget->options['acceptInvite']['url'] = $space->createUrl('/space-join-questions/membership/accept-invite');
            $widget->options['acceptInvite']['attrs'] = [
                'class' => 'btn btn-accent',
                'data-bs-target' => '#globalModal',
                'data-action-click' => null,
                'data-action-url' => null,
                'data-button-options' => null,
                'data-ui-loader' => null,
            ];
        }
    }

    /**
     * Get selected group IDs for a space.
     *
     * @param Space $space
     * @return int[]
     */
    public static function getSelectedGroupIds(Space $space)
    {
        $settings = $space->getSettings();
        $selectedGroupIds = $settings->get('questionGroupIds', 'spaceJoinQuestions', '[]');

        if (is_string($selectedGroupIds)) {
            $decoded = json_decode($selectedGroupIds, true);
            $selectedGroupIds = is_array($decoded) ? $decoded : [];
        } elseif (is_array($selectedGroupIds)) {
            $selectedGroupIds = $selectedGroupIds;
        } else {
            $selectedGroupIds = [];
        }

        $selectedGroupIds = array_values(array_filter(array_map('intval', $selectedGroupIds)));

        return $selectedGroupIds;
    }

    /**
     * Check if the given user should be asked join questions for this space.
     *
     * @param Space $space
     * @param \humhub\modules\user\models\User|null $user
     * @return bool
     */
    public static function shouldAskQuestionsForUser(Space $space, $user)
    {
        if (!$user) {
            return false;
        }

        $selectedGroupIds = static::getSelectedGroupIds($space);
        if (empty($selectedGroupIds)) {
            return false;
        }

        $isInSelectedGroup = GroupUser::find()
            ->where(['user_id' => $user->id, 'group_id' => $selectedGroupIds])
            ->exists();

        if (!$isInSelectedGroup) {
            return false;
        }

        return \humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->exists();
    }

    /**
     * Whether invitees must answer questions and wait for approval.
     *
     * @param Space $space
     * @param \humhub\modules\user\models\User|null $user
     * @return bool
     */
    public static function shouldRequireQuestionsOnInvite(Space $space, $user)
    {
        $enabled = $space->getSettings()->get('requireQuestionsOnInvite', 'spaceJoinQuestions', false);
        if (!$enabled || $enabled === '0' || $enabled === 0) {
            return false;
        }

        return static::shouldAskQuestionsForUser($space, $user);
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

        if (!static::shouldAskQuestionsForUser($space, $membership->user)) {
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
     * After membership insert: handle invite-link auto-join demotion when questions required,
     * and optional AVID notifications for applicants.
     */
    public static function onMembershipAfterInsert($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        if (!Yii::$app instanceof WebApplication) {
            return;
        }

        $space = $membership->space;
        if (!$space || !$space->moduleManager->isEnabled('space-join-questions')) {
            return;
        }

        // Invite-link / email-invite registration inserts MEMBER directly.
        // When the setting is on, demote to applicant so they must answer questions.
        if ($membership->status === Membership::STATUS_MEMBER) {
            static::demoteInviteMemberToApplicantIfNeeded($membership, $space);
            return;
        }

        if ($membership->status !== Membership::STATUS_APPLICANT) {
            return;
        }

        // Note: Answers are saved in the membership controller.
        // Trigger AVID membership notification if applicable
        if (Yii::$app->getModule('avid-membership-notifications')) {
            \humhub\modules\avidMembershipNotifications\Events::onMembershipApplicationReceived($membership);
        }
    }

    /**
     * Convert invite-created MEMBER row to APPLICANT when questions are required on invite.
     */
    protected static function demoteInviteMemberToApplicantIfNeeded(Membership $membership, Space $space)
    {
        $user = $membership->user;
        if (!$user || !static::shouldRequireQuestionsOnInvite($space, $user)) {
            return;
        }

        $invite = \humhub\modules\user\models\Invite::find()
            ->where(['email' => $user->email, 'space_invite_id' => $space->id])
            ->andWhere(['source' => [
                \humhub\modules\user\models\Invite::SOURCE_INVITE,
                \humhub\modules\user\models\Invite::SOURCE_INVITE_BY_LINK,
            ]])
            ->one();

        if ($invite === null) {
            return;
        }

        $membership->status = Membership::STATUS_APPLICANT;
        $membership->save(false, ['status']);

        try {
            Yii::$app->user->setReturnUrl($space->createUrl('/space-join-questions/membership/request'));
        } catch (\Throwable $e) {
            // Ignore return URL failures outside web login context.
        }
    }

    /**
     * Keep answers that belong to durable application history; delete only orphans.
     */
    public static function onMembershipBeforeDelete($event)
    {
        /** @var Membership $membership */
        $membership = $event->sender;

        // Preserve answers tied to an application history row.
        \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer::updateAll(
            ['membership_id' => null],
            [
                'and',
                ['membership_id' => $membership->id],
                ['not', ['application_id' => null]],
            ]
        );

        // Remove answers with no application history (e.g. cancelled drafts).
        \humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer::deleteAll([
            'and',
            ['membership_id' => $membership->id],
            ['application_id' => null],
        ]);
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

    /**
     * Send application received confirmation email to applicant
     */
    public static function sendApplicationReceivedConfirmation($membership)
    {
        $space = $membership->space;
        $user = $membership->user;
        
        // Get the application received confirmation template for this space
        $template = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::findBySpaceAndType(
            $space->id,
            \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_RECEIVED_CONFIRMATION
        );
        
        
        // If no custom template exists or template is not active, use the default template structure
        if (!$template || !$template->is_active) {
            $defaultTemplate = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::getDefaultTemplate(
                \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_RECEIVED_CONFIRMATION
            );
            
            // Create a temporary template object with default values
            $template = new \humhub\modules\spaceJoinQuestions\models\EmailTemplate();
            $template->space_id = $space->id;
            $template->template_type = \humhub\modules\spaceJoinQuestions\models\EmailTemplate::TYPE_APPLICATION_RECEIVED_CONFIRMATION;
            $template->subject = $defaultTemplate['subject'];
            $template->header = $defaultTemplate['header'];
            $template->body = $defaultTemplate['body'];
            $template->footer = $defaultTemplate['footer'];
            $template->header_bg_color = $defaultTemplate['header_bg_color'];
            $template->footer_bg_color = $defaultTemplate['footer_bg_color'];
            $template->header_font_color = $defaultTemplate['header_font_color'];
            $template->footer_font_color = $defaultTemplate['footer_font_color'];
            $template->is_active = 1;
            // Don't save this - it's just for processing
        }
        
        // Prepare template variables (same as Application Received but for applicant)
        $variables = [
            'space_name' => $space->name,
            'admin_name' => $space->getOwnerUser()->one()->displayName,
            'user_name' => $user->displayName,
            'user_email' => $user->email,
            'application_date' => $membership->created_at,
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
        $processed = $template->processTemplate($variables, $user);
        
        
        try {
            // Send email to applicant
            $mail = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')])
                ->setTo($user->email)
                ->setSubject($processed['subject'])
                ->setHtmlBody($processed['body']);
            
            $mail->send();
            
            Yii::info("Application received confirmation sent to {$user->email} for space {$space->name}", 'spaceJoinQuestions');
            
        } catch (\Exception $e) {
            Yii::error("Failed to send application received confirmation to {$user->email}: " . $e->getMessage(), 'spaceJoinQuestions');
        }
    }

}
