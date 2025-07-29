<?php

namespace humhub\modules\spaceJoinQuestions\notifications;

use Yii;
use yii\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Membership;
use humhub\modules\spaceJoinQuestions\models\DeclineReason;

/**
 * ApplicationDeclined Notification
 * 
 * Notifies users when their membership application is declined
 */
class ApplicationDeclined extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'space-join-questions';

    /**
     * @inheritdoc
     */
    public $viewName = 'applicationDeclined';

    /**
     * Custom decline reason stored as property (avoids serialization issues)
     * @var string
     */
    public $declineReason;

    /**
     * Constructor to handle decline reason
     * 
     * @param Membership|null $membership
     * @param string|null $declineReason
     */
    public function __construct($membership = null, $declineReason = null)
    {
        if ($membership) {
            $this->source = $membership;
        }
        
        $this->declineReason = $declineReason;
        
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new \humhub\modules\space\notifications\SpaceMemberNotificationCategory();
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject()
    {
        /** @var Membership|object $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return Yii::t('SpaceJoinQuestionsModule.base', 'Membership application declined');
        }

        return Yii::t('SpaceJoinQuestionsModule.base', 'Membership application for {spaceName} declined', [
            'spaceName' => $membership->space->name
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        /** @var Membership|object $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return '';
        }

        // Get the user's first name
        $firstName = $membership->user ? $membership->user->profile->firstname : 'User';

        $reasonText = '';
        if ($this->declineReason) {
            $reasonText = '<br><br><strong>' . Yii::t('SpaceJoinQuestionsModule.base', 'Reason:') . '</strong> ' . Html::encode($this->declineReason);
        }

        return Yii::t('SpaceJoinQuestionsModule.base', 'Hello {firstName}, your membership application for {spaceName} has been declined.{reason}', [
            'firstName' => Html::encode($firstName),
            'spaceName' => Html::tag('strong', Html::encode($membership->space->name)),
            'reason' => $reasonText
        ]);
    }

    /**
     * @inheritdoc
     */
    public function text()
    {
        /** @var Membership|object $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return '';
        }

        // Get the user's first name
        $firstName = $membership->user ? $membership->user->profile->firstname : 'User';

        $reasonText = '';
        if ($this->declineReason) {
            $reasonText = "\n\n" . Yii::t('SpaceJoinQuestionsModule.base', 'Reason:') . ' ' . $this->declineReason;
        }

        return Yii::t('SpaceJoinQuestionsModule.base', 'Hello {firstName}, your membership application for {spaceName} has been declined.{reason}', [
            'firstName' => $firstName,
            'spaceName' => $membership->space->name,
            'reason' => $reasonText
        ]);
    }

    /**
     * Set the decline reason
     * 
     * @param string $reason
     * @return $this
     */
    public function setDeclineReason($reason)
    {
        $this->declineReason = $reason;
        return $this;
    }

    /**
     * Get the decline reason
     * 
     * @return string|null
     */
    public function getDeclineReason()
    {
        return $this->declineReason;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        /** @var Membership|object $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return null;
        }

        return $membership->space->createUrl('/space/space');
    }

    /**
     * @inheritdoc
     */
    public function getSpaceId()
    {
        /** @var Membership|object $membership */
        $membership = $this->source;
        
        return $membership && $membership->space ? $membership->space->id : null;
    }

    /**
     * Send notification directly without using the queue system
     * This bypasses the PHP 8.4 compatibility issues with the queue
     * 
     * @param \humhub\modules\user\models\User $user
     * @return bool
     */
    public function sendDirect($user)
    {
        try {
            // Save the notification record for web notifications
            if (!$this->saveRecord($user)) {
                Yii::error('Failed to save notification record for user ' . $user->id);
                return false;
            }

            // Send web notifications
            foreach (Yii::$app->notification->getTargets($user) as $target) {
                if ($target->id !== 'email') { // Skip the default email target
                    $target->send($this, $user);
                }
            }

            // Send email directly without notification system
            $this->sendDirectEmail($user);

            return true;
        } catch (\Exception $e) {
            Yii::error('Error sending direct notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email directly without notification system
     * 
     * @param \humhub\modules\user\models\User $user
     * @return bool
     */
    protected function sendDirectEmail($user)
    {
        try {
            // Create a simple HTML email with basic styling
            $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . Html::encode(Yii::$app->name) . '</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #708fa0; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #fff; }
                    .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>' . Html::encode(Yii::$app->name) . '</h1>
                    </div>
                    <div class="content">
                        ' . $this->html() . '
                    </div>
                    <div class="footer">
                        This is an automated notification from ' . Html::encode(Yii::$app->name) . '
                    </div>
                </div>
            </body>
            </html>';

            $mail = Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setSubject($this->getMailSubject())
                ->setHtmlBody($htmlContent)
                ->setTextBody($this->text());

            if ($replyTo = Yii::$app->settings->get('mailer.systemEmailReplyTo')) {
                $mail->setReplyTo($replyTo);
            }

            return $mail->send();
        } catch (\Exception $e) {
            Yii::error('Error sending direct email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Static method to create and send notification
     * 
     * @param Membership $membership
     * @param string $declineReason
     * @param \humhub\modules\user\models\User $recipient
     */
    public static function createAndSend($membership, $declineReason, $recipient)
    {
        // Store reason in database for persistence
        DeclineReason::createForMembership($membership->id, $declineReason);
        
        // Create and send notification
        $notification = new static($membership, $declineReason);
        $notification->send($recipient);
    }
}