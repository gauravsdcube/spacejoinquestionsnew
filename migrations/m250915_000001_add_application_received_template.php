<?php

use yii\db\Migration;

/**
 * Handles the creation of table `space_join_application_received_template`.
 */
class m250915_000001_add_application_received_template extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('space_join_application_received_template', [
            'id' => $this->primaryKey(),
            'space_id' => $this->integer()->notNull()->comment('Space ID'),
            'subject' => $this->string(255)->notNull()->comment('Email subject'),
            'body' => $this->text()->notNull()->comment('Email body (HTML)'),
            'is_enabled' => $this->boolean()->defaultValue(true)->comment('Whether template is enabled'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key constraint
        $this->addForeignKey(
            'fk_application_received_template_space_id',
            'space_join_application_received_template',
            'space_id',
            'space',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add unique index for space_id
        $this->createIndex(
            'idx_application_received_template_space_id',
            'space_join_application_received_template',
            'space_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_application_received_template_space_id', 'space_join_application_received_template');
        $this->dropTable('space_join_application_received_template');
    }
}
