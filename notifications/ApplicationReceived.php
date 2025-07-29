<?php

namespace humhub\modules\spaceJoinQuestions\notifications;

use Yii;
use yii\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Membership;

/**
 * ApplicationReceived Notification
 * 
 * Notifies space administrators when a new membership application is received
 */
class ApplicationReceived extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'space-join-questions';

    /**
     * @inheritdoc
     */
    public $viewName = 'applicationReceived';

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
        /** @var Membership $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return Yii::t('SpaceJoinQuestionsModule.base', 'New membership application');
        }

        return Yii::t('SpaceJoinQuestionsModule.base', 'New membership application for {spaceName}', [
            'spaceName' => $membership->space->name
        ]);
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        /** @var Membership $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space || !$this->originator) {
            return '';
        }

        return Yii::t('SpaceJoinQuestionsModule.base', '{userName} requested membership in {spaceName}', [
            'userName' => Html::tag('strong', Html::encode($this->originator->displayName)),
            'spaceName' => Html::tag('strong', Html::encode($membership->space->name))
        ]);
    }

    /**
     * @inheritdoc
     */
    public function text()
    {
        /** @var Membership $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space || !$this->originator) {
            return '';
        }

        return Yii::t('SpaceJoinQuestionsModule.base', '{userName} requested membership in {spaceName}', [
            'userName' => $this->originator->displayName,
            'spaceName' => $membership->space->name
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        /** @var Membership $membership */
        $membership = $this->source;
        
        if (!$membership || !$membership->space) {
            return null;
        }

        return $membership->space->createUrl('/space-join-questions/admin/applications');
    }

    /**
     * @inheritdoc
     */
    public function getSpaceId()
    {
        /** @var Membership $membership */
        $membership = $this->source;
        
        return $membership && $membership->space ? $membership->space->id : null;
    }
}