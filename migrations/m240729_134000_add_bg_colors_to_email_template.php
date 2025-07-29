<?php

use yii\db\Migration;

/**
 * Add background color fields to email template table
 */
class m240729_134000_add_bg_colors_to_email_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('space_join_email_template', 'header_bg_color', $this->string(7)->defaultValue('#f8f9fa')->after('footer'));
        $this->addColumn('space_join_email_template', 'footer_bg_color', $this->string(7)->defaultValue('#f8f9fa')->after('header_bg_color'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('space_join_email_template', 'header_bg_color');
        $this->dropColumn('space_join_email_template', 'footer_bg_color');
    }
} 