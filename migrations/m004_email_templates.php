<?php

namespace humhub\modules\spaceJoinQuestions\migrations;

use yii\db\Migration;

/**
 * Migration for email templates functionality
 */
class m004_email_templates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Create space_join_email_template table
        $this->createTable('space_join_email_template', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'template_type' => $this->string(50)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'is_active' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk_space_join_email_template_space',
            'space_join_email_template',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->createIndex('idx_space_join_email_template_space', 'space_join_email_template', 'space_id');
        $this->createIndex('idx_space_join_email_template_type', 'space_join_email_template', 'template_type');
        $this->createIndex('idx_space_join_email_template_active', 'space_join_email_template', 'is_active');
        $this->createIndex('idx_space_join_email_template_space_type', 'space_join_email_template', ['space_id', 'template_type'], true);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk_space_join_email_template_space', 'space_join_email_template');
        
        // Drop table
        $this->dropTable('space_join_email_template');

        return true;
    }
} 