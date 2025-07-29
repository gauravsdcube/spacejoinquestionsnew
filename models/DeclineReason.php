<?php

namespace humhub\modules\spaceJoinQuestions\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use humhub\components\ActiveRecord;
use humhub\modules\space\models\Membership;

/**
 * DeclineReason Model
 * 
 * Stores decline reasons for membership applications
 *
 * @property integer $id
 * @property integer $membership_id
 * @property string $reason_text
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Membership $membership
 */
class DeclineReason extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_join_decline_reason';
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
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            [
                'class' => \yii\behaviors\BlameableBehavior::class,
                'updatedByAttribute' => false, // Only track creator
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['membership_id', 'reason_text'], 'required'],
            [['membership_id', 'created_by'], 'integer'],
            [['reason_text'], 'string', 'max' => 1000],
            [['membership_id'], 'exist', 'skipOnError' => true, 'targetClass' => Membership::class, 'targetAttribute' => ['membership_id' => 'id']],
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
            'reason_text' => Yii::t('SpaceJoinQuestionsModule.base', 'Decline Reason'),
            'created_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Created At'),
            'created_by' => Yii::t('SpaceJoinQuestionsModule.base', 'Created By'),
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
     * Get decline reason for membership
     * 
     * @param int $membershipId
     * @return static|null
     */
    public static function findByMembership($membershipId)
    {
        return static::findOne(['membership_id' => $membershipId]);
    }

    /**
     * Create or update decline reason for membership
     * 
     * @param int $membershipId
     * @param string $reason
     * @return static
     */
    public static function createForMembership($membershipId, $reason)
    {
        $model = static::findByMembership($membershipId);
        
        if (!$model) {
            $model = new static();
            $model->membership_id = $membershipId;
        }
        
        $model->reason_text = $reason;
        $model->save();
        
        return $model;
    }

    /**
     * Get predefined decline reasons
     * 
     * @return array
     */
    public static function getPredefinedReasons()
    {
        return [
            Yii::t('SpaceJoinQuestionsModule.base', 'Incomplete application information'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Does not meet space requirements'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Inappropriate content in application'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Space is currently at capacity'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Application does not align with space purpose'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Previous violations of community guidelines'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Insufficient experience or qualifications'),
            Yii::t('SpaceJoinQuestionsModule.base', 'Other (custom reason)'),
        ];
    }
}