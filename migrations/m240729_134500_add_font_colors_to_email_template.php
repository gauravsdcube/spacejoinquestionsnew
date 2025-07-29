<?php

use yii\db\Migration;

/**
 * Add font color fields to email template table
 */
class m240729_134500_add_font_colors_to_email_template extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('space_join_email_template', 'header_font_color', $this->string(7)->defaultValue('#495057')->after('footer_bg_color'));
        $this->addColumn('space_join_email_template', 'footer_font_color', $this->string(7)->defaultValue('#6c757d')->after('header_font_color'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('space_join_email_template', 'header_font_color');
        $this->dropColumn('space_join_email_template', 'footer_font_color');
    }
} 