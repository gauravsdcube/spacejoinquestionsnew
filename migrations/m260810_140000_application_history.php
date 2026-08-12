<?php

use yii\db\Migration;

/**
 * Durable application history so approved/declined applications remain visible.
 */
class m260810_140000_application_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('space_join_application', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'membership_id' => $this->integer()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'source' => $this->string(20)->notNull()->defaultValue('request'),
            'request_message' => $this->text()->null(),
            'submitted_at' => $this->integer()->notNull(),
            'decided_at' => $this->integer()->null(),
            'decided_by' => $this->integer()->null(),
            'decline_reason' => $this->string(1000)->null(),
        ]);

        $this->createIndex('idx_space_join_application_space', 'space_join_application', 'space_id');
        $this->createIndex('idx_space_join_application_user', 'space_join_application', 'user_id');
        $this->createIndex('idx_space_join_application_status', 'space_join_application', ['space_id', 'status']);
        $this->createIndex('idx_space_join_application_membership', 'space_join_application', 'membership_id');

        $this->addForeignKey(
            'fk_space_join_application_space',
            'space_join_application',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_space_join_application_user',
            'space_join_application',
            'user_id',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('space_join_answer', 'application_id', $this->integer()->null()->after('membership_id'));
        $this->createIndex('idx_space_join_answer_application', 'space_join_answer', 'application_id');

        $this->addForeignKey(
            'fk_space_join_answer_application',
            'space_join_answer',
            'application_id',
            'space_join_application',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Allow answers to outlive deleted memberships (declined applications).
        $this->alterColumn('space_join_answer', 'membership_id', $this->integer()->null());

        try {
            $this->dropForeignKey('fk_space_join_answer_membership', 'space_join_answer');
        } catch (\Throwable $e) {
            // Key name may differ on some installs.
        }

        $this->addForeignKey(
            'fk_space_join_answer_membership',
            'space_join_answer',
            'membership_id',
            'space_membership',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Backfill pending applications from current applicants that have answers.
        $rows = (new \yii\db\Query())
            ->select(['m.id AS membership_id', 'm.space_id', 'm.user_id', 'm.request_message', 'm.created_at'])
            ->from(['m' => 'space_membership'])
            ->innerJoin('space_join_answer a', 'a.membership_id = m.id')
            ->where(['m.status' => 2]) // STATUS_APPLICANT
            ->groupBy(['m.id', 'm.space_id', 'm.user_id', 'm.request_message', 'm.created_at'])
            ->all();

        foreach ($rows as $row) {
            $submittedAt = is_numeric($row['created_at'])
                ? (int) $row['created_at']
                : (int) strtotime((string) $row['created_at']);

            $this->insert('space_join_application', [
                'space_id' => (int) $row['space_id'],
                'user_id' => (int) $row['user_id'],
                'membership_id' => (int) $row['membership_id'],
                'status' => 'pending',
                'source' => 'request',
                'request_message' => $row['request_message'],
                'submitted_at' => $submittedAt ?: time(),
            ]);

            $applicationId = (int) $this->db->getLastInsertID();
            $this->update(
                'space_join_answer',
                ['application_id' => $applicationId],
                ['membership_id' => (int) $row['membership_id']]
            );
        }
    }

    public function safeDown()
    {
        try {
            $this->dropForeignKey('fk_space_join_answer_application', 'space_join_answer');
        } catch (\Throwable $e) {
        }

        try {
            $this->dropIndex('idx_space_join_answer_application', 'space_join_answer');
        } catch (\Throwable $e) {
        }

        $this->dropColumn('space_join_answer', 'application_id');
        $this->dropTable('space_join_application');
    }
}
