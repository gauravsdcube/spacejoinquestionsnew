<?php

namespace humhub\modules\spaceJoinQuestions\models;

use humhub\components\ActiveRecord;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * Durable membership application history.
 *
 * @property integer $id
 * @property integer $space_id
 * @property integer $user_id
 * @property integer|null $membership_id
 * @property string $status
 * @property string $source
 * @property string|null $request_message
 * @property integer $submitted_at
 * @property integer|null $decided_at
 * @property integer|null $decided_by
 * @property string|null $decline_reason
 *
 * @property Space $space
 * @property User $user
 * @property Membership|null $membership
 * @property User|null $decidedBy
 * @property SpaceJoinAnswer[] $answers
 */
class SpaceJoinApplication extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    public const SOURCE_REQUEST = 'request';
    public const SOURCE_INVITE = 'invite';

    public static function tableName()
    {
        return 'space_join_application';
    }

    public function rules()
    {
        return [
            [['space_id', 'user_id', 'status', 'source', 'submitted_at'], 'required'],
            [['space_id', 'user_id', 'membership_id', 'submitted_at', 'decided_at', 'decided_by'], 'integer'],
            [['request_message'], 'string'],
            [['decline_reason'], 'string', 'max' => 1000],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_DECLINED]],
            [['source'], 'in', 'range' => [self::SOURCE_REQUEST, self::SOURCE_INVITE]],
        ];
    }

    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getMembership()
    {
        return $this->hasOne(Membership::class, ['id' => 'membership_id']);
    }

    public function getDecidedBy()
    {
        return $this->hasOne(User::class, ['id' => 'decided_by']);
    }

    public function getAnswers()
    {
        return $this->hasMany(SpaceJoinAnswer::class, ['application_id' => 'id'])
            ->joinWith('question')
            ->orderBy(['space_join_question.sort_order' => SORT_ASC]);
    }

    /**
     * Create a pending application and attach answers from POST or a map.
     *
     * @param Membership $membership
     * @param SpaceJoinQuestion[] $questions
     * @param string $source
     * @param array|null $answersByQuestionId optional map questionId => answer text
     * @return static
     */
    public static function createPending(Membership $membership, array $questions, string $source = self::SOURCE_REQUEST, ?array $answersByQuestionId = null)
    {
        $application = new static();
        $application->space_id = $membership->space_id;
        $application->user_id = $membership->user_id;
        $application->membership_id = $membership->id;
        $application->status = self::STATUS_PENDING;
        $application->source = $source;
        $application->request_message = $membership->request_message;
        $application->submitted_at = time();

        if (!$application->save()) {
            throw new \RuntimeException('Failed to create application history record');
        }

        foreach ($questions as $question) {
            if ($answersByQuestionId !== null) {
                $answerText = trim((string) ($answersByQuestionId[$question->id] ?? ''));
            } else {
                $answerText = trim((string) Yii::$app->request->post('question_' . $question->id, ''));
            }

            if ($answerText === '') {
                continue;
            }

            $answer = new SpaceJoinAnswer();
            $answer->membership_id = $membership->id;
            $answer->application_id = $application->id;
            $answer->question_id = $question->id;
            $answer->answer_text = $answerText;

            if (!$answer->save()) {
                throw new \RuntimeException('Failed to save answer');
            }
        }

        return $application;
    }

    public function markApproved(?User $admin = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->decided_at = time();
        $this->decided_by = $admin ? $admin->id : (Yii::$app->user->isGuest ? null : Yii::$app->user->id);
        $this->decline_reason = null;

        return $this->save(false, ['status', 'decided_at', 'decided_by', 'decline_reason']);
    }

    public function markDeclined(string $reason, ?User $admin = null): bool
    {
        $this->status = self::STATUS_DECLINED;
        $this->decided_at = time();
        $this->decided_by = $admin ? $admin->id : (Yii::$app->user->isGuest ? null : Yii::$app->user->id);
        $this->decline_reason = $reason;
        $this->membership_id = null;

        return $this->save(false, ['status', 'decided_at', 'decided_by', 'decline_reason', 'membership_id']);
    }

    public static function findPendingByMembership(int $membershipId): ?self
    {
        return static::findOne([
            'membership_id' => $membershipId,
            'status' => self::STATUS_PENDING,
        ]);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => Yii::t('SpaceJoinQuestionsModule.base', 'Approved'),
            self::STATUS_DECLINED => Yii::t('SpaceJoinQuestionsModule.base', 'Declined'),
            default => Yii::t('SpaceJoinQuestionsModule.base', 'Pending'),
        };
    }

    public function getSourceLabel(): string
    {
        return match ($this->source) {
            self::SOURCE_INVITE => Yii::t('SpaceJoinQuestionsModule.base', 'Invite'),
            default => Yii::t('SpaceJoinQuestionsModule.base', 'Request'),
        };
    }
}
