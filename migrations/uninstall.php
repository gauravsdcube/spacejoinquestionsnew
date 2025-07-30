<?php

namespace humhub\modules\spaceJoinQuestions\migrations;

use humhub\components\Migration;

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
        $this->safeDropTable('space_join_email_template');
        $this->safeDropTable('space_join_decline_reason');
        $this->safeDropTable('space_join_answer');
        $this->safeDropTable('space_join_question');

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
