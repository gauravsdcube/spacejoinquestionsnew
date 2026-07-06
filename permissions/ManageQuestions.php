<?php

namespace humhub\modules\spaceJoinQuestions\permissions;

use Yii;
use humhub\modules\admin\components\BaseAdminPermission;

/**
 * ManageQuestions Permission
 * 
 * Admin-only permission for managing join questions
 */
class ManageQuestions extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'space-join-questions';

    /**
     * @inheritdoc
     */
    protected $title = 'Manage Join Questions';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows creating, editing and deleting custom questions for space membership requests';

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('SpaceJoinQuestionsModule.base', 'Manage Join Questions');
        $this->description = Yii::t('SpaceJoinQuestionsModule.base', 'Allows creating, editing and deleting custom questions for space membership requests');
    }
} 