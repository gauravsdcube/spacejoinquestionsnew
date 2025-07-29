<?php

namespace humhub\modules\spaceJoinQuestions;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;

/**
 * Space Join Questions Module
 * 
 * Allows space administrators to create custom questions for membership requests
 * with proper approval workflow and notifications.
 * 
 * @author D Cube Consulting Ltd <info@dcubeconsulting.co.uk>
 * @version 2.0.0
 * @since 1.0.0
 * @copyright 2025 D Cube Consulting Ltd. All rights reserved.
 */
class Module extends ContentContainerModule
{
    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @var bool Enable email notifications for new applications
     */
    public $enableEmailNotifications = true;

    /**
     * @var bool Enable decline reasons
     */
    public $enableDeclineReasons = true;

    /**
     * @var int Maximum number of questions per space
     */
    public $maxQuestionsPerSpace = 20;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('SpaceJoinQuestionsModule.base', 'Space Join Questions');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceJoinQuestionsModule.base', 'Create custom questions for space membership requests with approval workflow.');
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof Space) {
            return [
                new permissions\ManageQuestions(),
                new permissions\ViewApplications(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            Space::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerName(ContentContainerActiveRecord $container)
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        if ($container instanceof Space) {
            return Yii::t('SpaceJoinQuestionsModule.base', 'Configure custom questions for membership requests in this space.');
        }
        
        return $this->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        if ($container instanceof Space) {
            return $container->createUrl('/space-join-questions/admin/index');
        }
        
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/space-join-questions/admin/global-settings']);
    }

    /**
     * @inheritdoc
     */
    public function getNotifications()
    {
        return [
            notifications\ApplicationReceived::class,
            notifications\ApplicationAccepted::class,
            notifications\ApplicationDeclined::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        // Clean up module data
        foreach (models\SpaceJoinQuestion::find()->all() as $question) {
            $question->delete();
        }
        
        foreach (models\SpaceJoinAnswer::find()->all() as $answer) {
            $answer->delete();
        }
        
        foreach (models\DeclineReason::find()->all() as $reason) {
            $reason->delete();
        }

        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * Check if space has custom questions configured
     * 
     * @param Space $space
     * @return bool
     */
    public function hasQuestionsConfigured(Space $space)
    {
        return models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->exists();
    }

    /**
     * Get questions count for space
     * 
     * @param Space $space
     * @return int
     */
    public function getQuestionsCount(Space $space)
    {
        return models\SpaceJoinQuestion::find()
            ->where(['space_id' => $space->id])
            ->count();
    }
} 