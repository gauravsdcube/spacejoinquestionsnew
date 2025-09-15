<?php

namespace humhub\modules\spaceJoinQuestions\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinAnswer;

/**
 * ApplicationAnswers Widget
 * 
 * Displays user answers to join questions
 */
class ApplicationAnswers extends Widget
{
    /**
     * @var \humhub\modules\space\models\Membership
     */
    public $membership;

    /**
     * @var SpaceJoinAnswer[]
     */
    public $answers;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->answers)) {
            return $this->render('applicationAnswers', [
                'membership' => $this->membership,
                'answers' => [],
                'hasAnswers' => false,
            ]);
        }

        return $this->render('applicationAnswers', [
            'membership' => $this->membership,
            'answers' => $this->answers,
            'hasAnswers' => true,
        ]);
    }
}