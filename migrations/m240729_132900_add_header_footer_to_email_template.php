<?php

use yii\db\Migration;

/**
 * Add header and footer fields to email template table
 */
class m240729_132900_add_header_footer_to_email_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('space_join_email_template', 'header', $this->text()->after('body'));
        $this->addColumn('space_join_email_template', 'footer', $this->text()->after('header'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('space_join_email_template', 'header');
        $this->dropColumn('space_join_email_template', 'footer');
    }
} 