<?php

namespace humhub\modules\spaceJoinQuestions\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;

/**
 * JoinQuestionsForm Widget
 * 
 * Renders custom questions in the membership request form
 */
class JoinQuestionsForm extends Widget
{
    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @var SpaceJoinQuestion[]
     */
    public $questions;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->questions)) {
            return '';
        }

        return $this->render('joinQuestionsForm', [
            'space' => $this->space,
            'questions' => $this->questions,
        ]);
    }
}