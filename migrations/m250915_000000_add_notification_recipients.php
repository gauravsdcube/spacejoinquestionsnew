<?php
use yii\db\Migration;

class m250915_000000_add_notification_recipients extends Migration
{
    public function safeUp()
    {
        $this->createTable('space_join_notification_recipients', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer(),
        ]);

        $this->createIndex('idx_space_join_notification_recipients_space_id', 'space_join_notification_recipients', 'space_id');
        $this->createIndex('idx_space_join_notification_recipients_user_id', 'space_join_notification_recipients', 'user_id');
        $this->createIndex('idx_space_join_notification_recipients_space_user', 'space_join_notification_recipients', ['space_id', 'user_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('space_join_notification_recipients');
    }
}
