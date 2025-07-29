<?php

namespace humhub\modules\spaceJoinQuestions\migrations;

use yii\db\Migration;

/**
 * Initial migration for Space Join Questions module
 * Creates the main tables for questions and answers
 */
class m001_initial_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Create space_join_question table
        $this->createTable('space_join_question', [
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
        $this->createTable('space_join_answer', [
            'id' => $this->primaryKey(),
            'membership_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
            'answer_text' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk_space_join_question_space',
            'space_join_question',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_question_created_by',
            'space_join_question',
            'created_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_question_updated_by',
            'space_join_question',
            'updated_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_answer_membership',
            'space_join_answer',
            'membership_id',
            'space_membership',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_answer_question',
            'space_join_answer',
            'question_id',
            'space_join_question',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add indexes for better performance
        $this->createIndex('idx_space_join_question_space', 'space_join_question', 'space_id');
        $this->createIndex('idx_space_join_question_sort', 'space_join_question', ['space_id', 'sort_order']);
        $this->createIndex('idx_space_join_answer_membership', 'space_join_answer', 'membership_id');
        $this->createIndex('idx_space_join_answer_question', 'space_join_answer', 'question_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk_space_join_answer_question', 'space_join_answer');
        $this->dropForeignKey('fk_space_join_answer_membership', 'space_join_answer');
        $this->dropForeignKey('fk_space_join_question_updated_by', 'space_join_question');
        $this->dropForeignKey('fk_space_join_question_created_by', 'space_join_question');
        $this->dropForeignKey('fk_space_join_question_space', 'space_join_question');

        // Drop tables
        $this->dropTable('space_join_answer');
        $this->dropTable('space_join_question');
    }
}