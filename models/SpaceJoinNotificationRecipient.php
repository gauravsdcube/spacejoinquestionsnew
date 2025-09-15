<?php
namespace humhub\modules\spaceJoinQuestions\models;

use Yii;
use yii\db\ActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class SpaceJoinNotificationRecipient extends ActiveRecord
{
    public static function tableName()
    {
        return 'space_join_notification_recipients';
    }

    public function rules()
    {
        return [
            [['space_id', 'user_id'], 'required'],
            [['space_id', 'user_id', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['space_id', 'user_id'], 'unique', 'targetAttribute' => ['space_id', 'user_id']],
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

    public static function getRecipientsForSpace($spaceId)
    {
        return static::find()
            ->where(['space_id' => $spaceId])
            ->with('user')
            ->all();
    }

    public static function addRecipient($spaceId, $userId)
    {
        // Check if already exists
        $existing = static::find()
            ->where(['space_id' => $spaceId, 'user_id' => $userId])
            ->one();
            
        if ($existing) {
            return false; // Already exists
        }

        $recipient = new static();
        $recipient->space_id = $spaceId;
        $recipient->user_id = $userId;
        $recipient->created_at = date('Y-m-d H:i:s');
        $recipient->created_by = Yii::$app->user->id;
        
        return $recipient->save();
    }

    public static function removeRecipient($spaceId, $userId)
    {
        return static::deleteAll(['space_id' => $spaceId, 'user_id' => $userId]);
    }

    public static function clearRecipientsForSpace($spaceId)
    {
        return static::deleteAll(['space_id' => $spaceId]);
    }
}
