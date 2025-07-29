<?php

namespace humhub\modules\spaceJoinQuestions\migrations;

use yii\db\Migration;

/**
 * Uninstall migration for Space Join Questions module
 * Cleans up all module data when module is disabled/uninstalled
 */
class uninstall extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Remove all module-related data
        $this->delete('notification', ['class' => [
            'humhub\\modules\\spaceJoinQuestions\\notifications\\ApplicationReceived',
            'humhub\\modules\\spaceJoinQuestions\\notifications\\ApplicationAccepted',
            'humhub\\modules\\spaceJoinQuestions\\notifications\\ApplicationDeclined',
        ]]);

        // Remove module settings
        $this->delete('setting', ['name' => 'spaceJoinQuestions.emailNotifications']);
        $this->delete('contentcontainer_setting', ['name' => 'spaceJoinQuestions.emailNotifications']);

        // Drop tables (with proper foreign key handling)
        if ($this->db->getTableSchema('space_join_email_template') !== null) {
            // Drop foreign keys first
            try {
                $this->dropForeignKey('fk_space_join_email_template_space', 'space_join_email_template');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            $this->dropTable('space_join_email_template');
        }

        if ($this->db->getTableSchema('space_join_decline_reason') !== null) {
            // Drop foreign keys first
            try {
                $this->dropForeignKey('fk_space_join_decline_reason_created_by', 'space_join_decline_reason');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                $this->dropForeignKey('fk_space_join_decline_reason_membership', 'space_join_decline_reason');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            $this->dropTable('space_join_decline_reason');
        }

        if ($this->db->getTableSchema('space_join_answer') !== null) {
            // Drop foreign keys first
            try {
                $this->dropForeignKey('fk_space_join_answer_question', 'space_join_answer');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                $this->dropForeignKey('fk_space_join_answer_membership', 'space_join_answer');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            $this->dropTable('space_join_answer');
        }

        if ($this->db->getTableSchema('space_join_question') !== null) {
            // Drop foreign keys first
            try {
                $this->dropForeignKey('fk_space_join_question_updated_by', 'space_join_question');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                $this->dropForeignKey('fk_space_join_question_created_by', 'space_join_question');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            try {
                $this->dropForeignKey('fk_space_join_question_space', 'space_join_question');
            } catch (Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            
            $this->dropTable('space_join_question');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "uninstall migration cannot be reverted.\n";
        return false;
    }
}