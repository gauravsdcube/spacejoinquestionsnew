<?php

namespace humhub\modules\spaceJoinQuestions\permissions;

use Yii;
use humhub\modules\admin\components\BaseAdminPermission;

/**
 * ViewApplications Permission
 * 
 * Admin-only permission for viewing membership applications
 */
class ViewApplications extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'space-join-questions';

    /**
     * @inheritdoc
     */
    protected $title = 'View Membership Applications';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows viewing membership applications and their answers to join questions';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('SpaceJoinQuestionsModule.base', 'View Membership Applications');
        $this->description = Yii::t('SpaceJoinQuestionsModule.base', 'Allows viewing membership applications and their answers to join questions');
    }
} 