<?php

namespace humhub\modules\spaceJoinQuestions\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use humhub\components\ActiveRecord;
use humhub\modules\space\models\Space;

/**
 * SpaceJoinQuestion Model
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $question_text
 * @property string $field_type
 * @property string $field_options
 * @property integer $is_required
 * @property integer $sort_order
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Space $space
 * @property SpaceJoinAnswer[] $answers
 */
class SpaceJoinQuestion extends ActiveRecord
{
    // Field types
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTAREA = 'textarea';
    const FIELD_TYPE_SELECT = 'select';
    const FIELD_TYPE_RADIO = 'radio';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_join_question';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'question_text'], 'required'],
            [['space_id', 'is_required', 'sort_order'], 'integer'],
            [['question_text'], 'string', 'max' => 500],
            [['field_type'], 'string', 'max' => 50],
            [['field_options'], 'string'],
            [['is_required'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
            [['field_type'], 'default', 'value' => self::FIELD_TYPE_TEXT],
            [['field_type'], 'in', 'range' => array_keys(self::getFieldTypeOptions())],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::class, 'targetAttribute' => ['space_id' => 'id']],
            [['field_options'], 'validateFieldOptions'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('SpaceJoinQuestionsModule.base', 'ID'),
            'space_id' => Yii::t('SpaceJoinQuestionsModule.base', 'Space'),
            'question_text' => Yii::t('SpaceJoinQuestionsModule.base', 'Question'),
            'field_type' => Yii::t('SpaceJoinQuestionsModule.base', 'Field Type'),
            'field_options' => Yii::t('SpaceJoinQuestionsModule.base', 'Options'),
            'is_required' => Yii::t('SpaceJoinQuestionsModule.base', 'Required'),
            'sort_order' => Yii::t('SpaceJoinQuestionsModule.base', 'Sort Order'),
            'created_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Created At'),
            'created_by' => Yii::t('SpaceJoinQuestionsModule.base', 'Created By'),
            'updated_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Updated At'),
            'updated_by' => Yii::t('SpaceJoinQuestionsModule.base', 'Updated By'),
        ];
    }

    /**
     * Validate field options for select/radio types
     * 
     * @param string $attribute
     * @param array $params
     */
    public function validateFieldOptions($attribute, $params)
    {
        if (in_array($this->field_type, [self::FIELD_TYPE_SELECT, self::FIELD_TYPE_RADIO])) {
            if (empty($this->field_options)) {
                $this->addError($attribute, Yii::t('SpaceJoinQuestionsModule.base', 'Options are required for {fieldType} fields.', [
                    'fieldType' => $this->getFieldTypeLabel()
                ]));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(SpaceJoinAnswer::class, ['question_id' => 'id']);
    }

    /**
     * Get available field type options
     * 
     * @return array
     */
    public static function getFieldTypeOptions()
    {
        return [
            self::FIELD_TYPE_TEXT => Yii::t('SpaceJoinQuestionsModule.base', 'Text Input'),
            self::FIELD_TYPE_TEXTAREA => Yii::t('SpaceJoinQuestionsModule.base', 'Text Area'),
            self::FIELD_TYPE_SELECT => Yii::t('SpaceJoinQuestionsModule.base', 'Dropdown'),
            self::FIELD_TYPE_RADIO => Yii::t('SpaceJoinQuestionsModule.base', 'Radio Buttons'),
        ];
    }

    /**
     * Get field type label
     * 
     * @return string
     */
    public function getFieldTypeLabel()
    {
        $options = self::getFieldTypeOptions();
        return isset($options[$this->field_type]) ? $options[$this->field_type] : $this->field_type;
    }

    /**
     * Get parsed field options as array
     * 
     * @return array
     */
    public function getFieldOptionsArray()
    {
        if (empty($this->field_options)) {
            return [];
        }
        
        return array_filter(array_map('trim', explode("\n", $this->field_options)));
    }

    /**
     * Check if field type supports options
     * 
     * @return bool
     */
    public function supportsOptions()
    {
        return in_array($this->field_type, [self::FIELD_TYPE_SELECT, self::FIELD_TYPE_RADIO]);
    }

    /**
     * Get questions for a space ordered by sort_order
     * 
     * @param int $spaceId
     * @return \yii\db\ActiveQuery
     */
    public static function findBySpace($spaceId)
    {
        return static::find()
            ->where(['space_id' => $spaceId])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Get next sort order for space
     * 
     * @param int $spaceId
     * @return int
     */
    public static function getNextSortOrder($spaceId)
    {
        $maxOrder = static::find()
            ->where(['space_id' => $spaceId])
            ->max('sort_order');
            
        return ($maxOrder !== null) ? $maxOrder + 1 : 1;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Set default sort order for new questions
            if ($insert && $this->sort_order == 0) {
                $this->sort_order = self::getNextSortOrder($this->space_id);
            }
            
            return true;
        }
        
        return false;
    }
}