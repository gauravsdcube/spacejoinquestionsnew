<?php

namespace humhub\modules\spaceJoinQuestions\migrations;

use yii\db\Migration;

/**
 * Migration for decline reasons table
 * Stores reasons when membership applications are declined
 */
class m002_decline_reasons extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Create space_join_decline_reason table
        $this->createTable('space_join_decline_reason', [
            'id' => $this->primaryKey(),
            'membership_id' => $this->integer()->notNull(),
            'reason_text' => $this->string(1000)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk_space_join_decline_reason_membership',
            'space_join_decline_reason',
            'membership_id',
            'space_membership',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_decline_reason_created_by',
            'space_join_decline_reason',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->createIndex('idx_space_join_decline_reason_membership', 'space_join_decline_reason', 'membership_id');
        $this->createIndex('idx_space_join_decline_reason_created_at', 'space_join_decline_reason', 'created_at');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk_space_join_decline_reason_created_by', 'space_join_decline_reason');
        $this->dropForeignKey('fk_space_join_decline_reason_membership', 'space_join_decline_reason');

        // Drop table
        $this->dropTable('space_join_decline_reason');
    }
}