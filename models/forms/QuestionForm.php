<?php

namespace humhub\modules\spaceJoinQuestions\models\forms;

use Yii;
use yii\base\Model;
use humhub\modules\spaceJoinQuestions\models\SpaceJoinQuestion;

/**
 * QuestionForm Model
 * 
 * Handles form validation and data for creating/editing space join questions
 */
class QuestionForm extends Model
{
    /**
     * @var int
     */
    public $space_id;

    /**
     * @var string
     */
    public $question_text;

    /**
     * @var string
     */
    public $field_type;

    /**
     * @var string
     */
    public $field_options;

    /**
     * @var bool
     */
    public $is_required;

    /**
     * @var int
     */
    public $sort_order;

    /**
     * @var int|null
     */
    public $id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'question_text'], 'required'],
            [['space_id', 'sort_order'], 'integer'],
            [['question_text'], 'string', 'max' => 500],
            [['field_type'], 'string', 'max' => 50],
            [['field_options'], 'string'],
            [['is_required'], 'boolean'],
            [['field_type'], 'default', 'value' => SpaceJoinQuestion::FIELD_TYPE_TEXT],
            [['is_required'], 'default', 'value' => false],
            [['sort_order'], 'default', 'value' => 0],
            [['field_type'], 'in', 'range' => array_keys(SpaceJoinQuestion::getFieldTypeOptions())],
            [['field_options'], 'validateFieldOptions'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'space_id' => Yii::t('SpaceJoinQuestionsModule.base', 'Space'),
            'question_text' => Yii::t('SpaceJoinQuestionsModule.base', 'Question'),
            'field_type' => Yii::t('SpaceJoinQuestionsModule.base', 'Field Type'),
            'field_options' => Yii::t('SpaceJoinQuestionsModule.base', 'Options'),
            'is_required' => Yii::t('SpaceJoinQuestionsModule.base', 'Required'),
            'sort_order' => Yii::t('SpaceJoinQuestionsModule.base', 'Sort Order'),
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
        if (in_array($this->field_type, [SpaceJoinQuestion::FIELD_TYPE_SELECT, SpaceJoinQuestion::FIELD_TYPE_RADIO])) {
            if (empty($this->field_options)) {
                $this->addError($attribute, Yii::t('SpaceJoinQuestionsModule.base', 'Options are required for {fieldType} fields.', [
                    'fieldType' => $this->getFieldTypeLabel()
                ]));
            }
        }
    }

    /**
     * Get field type label
     * 
     * @return string
     */
    public function getFieldTypeLabel()
    {
        $options = SpaceJoinQuestion::getFieldTypeOptions();
        return isset($options[$this->field_type]) ? $options[$this->field_type] : $this->field_type;
    }

    /**
     * Load data from existing question
     * 
     * @param SpaceJoinQuestion $question
     */
    public function loadFromQuestion($question)
    {
        $this->id = $question->id;
        $this->space_id = $question->space_id;
        $this->question_text = $question->question_text;
        $this->field_type = $question->field_type;
        $this->field_options = $question->field_options;
        $this->is_required = $question->is_required;
        $this->sort_order = $question->sort_order;
    }

    /**
     * Save the question
     * 
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->id) {
            // Update existing question
            $question = SpaceJoinQuestion::findOne($this->id);
            if (!$question || $question->space_id != $this->space_id) {
                return false;
            }
        } else {
            // Create new question
            $question = new SpaceJoinQuestion();
            $question->space_id = $this->space_id;
        }

        $question->question_text = $this->question_text;
        $question->field_type = $this->field_type;
        $question->field_options = $this->field_options;
        $question->is_required = $this->is_required;
        $question->sort_order = $this->sort_order;

        return $question->save();
    }
} 