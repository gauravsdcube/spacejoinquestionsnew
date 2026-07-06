<?php


use humhub\components\Migration;

class m250730_073132_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        // Create space_join_question table
        $this->safeCreateTable('space_join_question', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'question_text' => $this->string(500)->notNull(),
            'field_type' => $this->string(50)->defaultValue('text'),
            'field_options' => $this->text()->null(),
            'is_required' => $this->boolean()->defaultValue(false),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Create space_join_answer table
        $this->safeCreateTable('space_join_answer', [
            'id' => $this->primaryKey(),
            'membership_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
            'answer_text' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->safeAddForeignKey(
            'fk_space_join_question_space',
            'space_join_question',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->safeAddForeignKey(
            'fk_space_join_question_created_by',
            'space_join_question',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->safeAddForeignKey(
            'fk_space_join_question_updated_by',
            'space_join_question',
            'updated_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->safeAddForeignKey(
            'fk_space_join_answer_membership',
            'space_join_answer',
            'membership_id',
            'space_membership',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->safeAddForeignKey(
            'fk_space_join_answer_question',
            'space_join_answer',
            'question_id',
            'space_join_question',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes for better performance
        $this->safeCreateIndex('idx_space_join_question_space', 'space_join_question', 'space_id');
        $this->safeCreateIndex('idx_space_join_question_sort', 'space_join_question', ['space_id', 'sort_order']);
        $this->safeCreateIndex('idx_space_join_answer_membership', 'space_join_answer', 'membership_id');
        $this->safeCreateIndex('idx_space_join_answer_question', 'space_join_answer', 'question_id');

        // Create space_join_decline_reason table
        $this->safeCreateTable('space_join_decline_reason', [
            'id' => $this->primaryKey(),
            'membership_id' => $this->integer()->notNull(),
            'reason_text' => $this->string(1000)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->safeAddForeignKey(
            'fk_space_join_decline_reason_membership',
            'space_join_decline_reason',
            'membership_id',
            'space_membership',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->safeAddForeignKey(
            'fk_space_join_decline_reason_created_by',
            'space_join_decline_reason',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->safeCreateIndex('idx_space_join_decline_reason_membership', 'space_join_decline_reason', 'membership_id');
        $this->safeCreateIndex('idx_space_join_decline_reason_created_at', 'space_join_decline_reason', 'created_at');

        // Create space_join_email_template table
        $this->safeCreateTable('space_join_email_template', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'template_type' => $this->string(50)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'header' => $this->text(),
            'body' => $this->text()->notNull(),
            'footer' => $this->text(),
            'header_bg_color' => $this->string(7)->defaultValue('#f8f9fa'),
            'footer_bg_color' => $this->string(7)->defaultValue('#f8f9fa'),
            'header_font_color' => $this->string(7)->defaultValue('#495057'),
            'footer_font_color' => $this->string(7)->defaultValue('#6c757d'),
            'is_active' => $this->boolean()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Add foreign key constraints
        $this->safeAddForeignKey(
            'fk_space_join_email_template_space',
            'space_join_email_template',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes
        $this->safeCreateIndex('idx_space_join_email_template_space', 'space_join_email_template', 'space_id');
        $this->safeCreateIndex('idx_space_join_email_template_type', 'space_join_email_template', 'template_type');
        $this->safeCreateIndex('idx_space_join_email_template_active', 'space_join_email_template', 'is_active');
        $this->safeCreateIndex('idx_space_join_email_template_space_type', 'space_join_email_template', ['space_id', 'template_type'], true);


        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250730_073132_initial cannot be reverted.\n";

        return false;
    }
}
