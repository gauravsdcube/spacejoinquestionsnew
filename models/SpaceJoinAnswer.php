<?php

namespace humhub\modules\spaceJoinQuestions\models;

use humhub\components\ActiveRecord;
use humhub\modules\space\models\Membership;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * SpaceJoinAnswer Model
 *
 * @property integer $id
 * @property integer $membership_id
 * @property integer $question_id
 * @property string $answer_text
 * @property integer $created_at
 *
 * @property Membership $membership
 * @property SpaceJoinQuestion $question
 */
class SpaceJoinAnswer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_join_answer';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false, // Only track creation time
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['membership_id', 'question_id', 'answer_text'], 'required'],
            [['membership_id', 'question_id'], 'integer'],
            [['answer_text'], 'string', 'max' => 2000],
            [['membership_id'], 'exist', 'skipOnError' => true, 'targetClass' => Membership::class, 'targetAttribute' => ['membership_id' => 'id']],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => SpaceJoinQuestion::class, 'targetAttribute' => ['question_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('SpaceJoinQuestionsModule.base', 'ID'),
            'membership_id' => Yii::t('SpaceJoinQuestionsModule.base', 'Membership'),
            'question_id' => Yii::t('SpaceJoinQuestionsModule.base', 'Question'),
            'answer_text' => Yii::t('SpaceJoinQuestionsModule.base', 'Answer'),
            'created_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMembership()
    {
        return $this->hasOne(Membership::class, ['id' => 'membership_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(SpaceJoinQuestion::class, ['id' => 'question_id']);
    }

    /**
     * Get answers for a membership with questions
     *
     * @param int $membershipId
     * @return array
     */
    public static function getAnswersForMembership($membershipId)
    {
        return static::find()
            ->joinWith('question')
            ->where(['membership_id' => $membershipId])
            ->orderBy(['space_join_question.sort_order' => SORT_ASC])
            ->all();
    }

    /**
     * Get formatted answer text for display
     *
     * @return string
     */
    public function getFormattedAnswer()
    {
        if (empty($this->answer_text)) {
            return Yii::t('SpaceJoinQuestionsModule.base', 'No answer provided');
        }

        // Handle different field types
        if ($this->question) {
            switch ($this->question->field_type) {
                case SpaceJoinQuestion::FIELD_TYPE_CHECKBOX:
                    return $this->answer_text === '1' ?
                        Yii::t('SpaceJoinQuestionsModule.base', 'Yes') :
                        Yii::t('SpaceJoinQuestionsModule.base', 'No');

                case SpaceJoinQuestion::FIELD_TYPE_TEXTAREA:
                    return nl2br(\humhub\libs\Html::encode($this->answer_text));

                default:
                    return \humhub\libs\Html::encode($this->answer_text);
            }
        }

        return \humhub\libs\Html::encode($this->answer_text);
    }
}
