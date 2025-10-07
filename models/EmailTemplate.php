<?php

namespace humhub\modules\spaceJoinQuestions\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use humhub\components\ActiveRecord;
use humhub\modules\space\models\Space;

/**
 * EmailTemplate Model
 * 
 * Stores custom email templates for space join applications
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $template_type
 * @property string $subject
 * @property text $body
 * @property integer $is_active
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Space $space
 */
class EmailTemplate extends ActiveRecord
{
    const TYPE_APPLICATION_RECEIVED = 'application_received';
    const TYPE_APPLICATION_RECEIVED_CONFIRMATION = 'application_received_confirmation';
    const TYPE_APPLICATION_ACCEPTED = 'application_accepted';
    const TYPE_APPLICATION_DECLINED = 'application_declined';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_join_email_template';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'template_type', 'subject', 'body'], 'required'],
            [['space_id', 'is_active'], 'integer'],
            [['body', 'header', 'footer'], 'string'],
            [['template_type'], 'string', 'max' => 50],
            [['subject'], 'string', 'max' => 255],
            [['header_bg_color', 'footer_bg_color', 'header_font_color', 'footer_font_color'], 'string', 'max' => 7], // Hex color codes
            [['template_type'], 'in', 'range' => [
                self::TYPE_APPLICATION_RECEIVED,
                self::TYPE_APPLICATION_RECEIVED_CONFIRMATION,
                self::TYPE_APPLICATION_ACCEPTED,
                self::TYPE_APPLICATION_DECLINED
            ]],
            [['space_id'], 'exist', 'skipOnError' => true, 'targetClass' => Space::class, 'targetAttribute' => ['space_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('SpaceJoinQuestionsModule.base', 'ID'),
            'space_id' => Yii::t('SpaceJoinQuestionsModule.base', 'Space'),
            'template_type' => Yii::t('SpaceJoinQuestionsModule.base', 'Template Type'),
            'subject' => Yii::t('SpaceJoinQuestionsModule.base', 'Subject'),
            'body' => Yii::t('SpaceJoinQuestionsModule.base', 'Body'),
            'header' => Yii::t('SpaceJoinQuestionsModule.base', 'Header'),
            'footer' => Yii::t('SpaceJoinQuestionsModule.base', 'Footer'),
            'header_bg_color' => Yii::t('SpaceJoinQuestionsModule.base', 'Header Background Color'),
            'footer_bg_color' => Yii::t('SpaceJoinQuestionsModule.base', 'Footer Background Color'),
            'header_font_color' => Yii::t('SpaceJoinQuestionsModule.base', 'Header Font Color'),
            'footer_font_color' => Yii::t('SpaceJoinQuestionsModule.base', 'Footer Font Color'),
            'is_active' => Yii::t('SpaceJoinQuestionsModule.base', 'Active'),
            'created_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Created At'),
            'updated_at' => Yii::t('SpaceJoinQuestionsModule.base', 'Updated At'),
        ];
    }

    /**
     * Get space relation
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    /**
     * Get template type options
     */
    public static function getTemplateTypeOptions()
    {
        return [
            self::TYPE_APPLICATION_RECEIVED => Yii::t('SpaceJoinQuestionsModule.base', 'Application Received'),
            self::TYPE_APPLICATION_RECEIVED_CONFIRMATION => Yii::t('SpaceJoinQuestionsModule.base', 'Application Received Confirmation'),
            self::TYPE_APPLICATION_ACCEPTED => Yii::t('SpaceJoinQuestionsModule.base', 'Application Accepted'),
            self::TYPE_APPLICATION_DECLINED => Yii::t('SpaceJoinQuestionsModule.base', 'Application Declined'),
        ];
    }

    /**
     * Get template type label
     */
    public function getTemplateTypeLabel()
    {
        $options = self::getTemplateTypeOptions();
        return isset($options[$this->template_type]) ? $options[$this->template_type] : $this->template_type;
    }

    /**
     * Find template by space and type
     */
    public static function findBySpaceAndType($spaceId, $templateType)
    {
        return static::find()
            ->where(['space_id' => $spaceId, 'template_type' => $templateType])
            ->one();
    }

    /**
     * Get default template content for a specific type
     * 
     * @param string $templateType
     * @return array
     */
    public static function getDefaultTemplate($templateType)
    {
        $defaults = [
            self::TYPE_APPLICATION_RECEIVED => [
                'subject' => 'Application Received - {space_name}',
                'header' => '<h2 style="color: #007bff; margin: 0;">{space_name}</h2><p style="margin: 5px 0 0 0; color: #6c757d;">Application Received</p>',
                'body' => "Hello {user_name},\n\nThank you for your application to join {space_name}. We have received your application and will review it shortly.\n\n**Application Details:**\n- **Date:** {application_date}\n- **Space:** {space_name}\n- **Admin:** {admin_name}\n\n**Your Answers:**\n{application_answers}\n\nWe will notify you once we have reviewed your application. Please allow 2-3 business days for processing.\n\nBest regards,\n{admin_name}",
                'footer' => '<p style="margin: 0;">This is an automated message from {space_name}</p><p style="margin: 5px 0 0 0; font-size: 11px;">If you have any questions, please contact the space administrator.</p>',
                'header_bg_color' => '#e3f2fd',
                'footer_bg_color' => '#f8f9fa',
                'header_font_color' => '#0c5460',
                'footer_font_color' => '#6c757d',
            ],
            self::TYPE_APPLICATION_ACCEPTED => [
                'subject' => 'Application Accepted - Welcome to {space_name}!',
                'header' => '<h2 style="color: #28a745; margin: 0;">{space_name}</h2><p style="margin: 5px 0 0 0; color: #6c757d;">Application Accepted</p>',
                'body' => "Congratulations {user_name}!\n\nYour application to join {space_name} has been **accepted**! We are excited to welcome you to our community.\n\n**Acceptance Details:**\n- **Date:** {accepted_date}\n- **Space:** {space_name}\n- **Admin:** {admin_name}\n\n**Admin Notes:**\n{admin_notes}\n\nYou should receive an invitation to join the space shortly. Please check your email for the invitation link.\n\nWelcome aboard!\n{admin_name}",
                'footer' => '<p style="margin: 0;">Welcome to {space_name}!</p><p style="margin: 5px 0 0 0; font-size: 11px;">We look forward to seeing you in our community.</p>',
                'header_bg_color' => '#d4edda',
                'footer_bg_color' => '#f8f9fa',
                'header_font_color' => '#155724',
                'footer_font_color' => '#6c757d',
            ],
            self::TYPE_APPLICATION_DECLINED => [
                'subject' => 'Application Update - {space_name}',
                'header' => '<h2 style="color: #dc3545; margin: 0;">{space_name}</h2><p style="margin: 5px 0 0 0; color: #6c757d;">Application Status</p>',
                'body' => "Hello {user_name},\n\nThank you for your interest in joining {space_name}. After careful review, we regret to inform you that your application has been declined.\n\n**Application Details:**\n- **Date:** {declined_date}\n- **Space:** {space_name}\n- **Admin:** {admin_name}\n\n**Reason for Decline:**\n{decline_reason}\n\n**Admin Notes:**\n{admin_notes}\n\nWe encourage you to review our community guidelines and consider applying again in the future.\n\nBest regards,\n{admin_name}",
                'footer' => '<p style="margin: 0;">Thank you for your interest in {space_name}</p><p style="margin: 5px 0 0 0; font-size: 11px;">You may apply again in the future.</p>',
                'header_bg_color' => '#f8d7da',
                'footer_bg_color' => '#f8f9fa',
                'header_font_color' => '#721c24',
                'footer_font_color' => '#6c757d',
            ],
            self::TYPE_APPLICATION_RECEIVED_CONFIRMATION => [
                'subject' => 'Application Received - {space_name}',
                'header' => '<h2 style="color: #007bff; margin: 0;">{space_name}</h2><p style="margin: 5px 0 0 0; color: #6c757d;">Application Received</p>',
                'body' => "Hello {user_name},\n\nThank you for your application to join {space_name}. We have received your application and will review it shortly.\n\n**Application Details:**\n- **Date:** {application_date}\n- **Space:** {space_name}\n- **Admin:** {admin_name}\n\n**Your Answers:**\n{application_answers}\n\nWe will notify you once we have reviewed your application. Please allow 2-3 business days for processing.\n\nBest regards,\n{admin_name}",
                'footer' => '<p style="margin: 0;">This is an automated message from {space_name}</p><p style="margin: 5px 0 0 0; font-size: 11px;">If you have any questions, please contact the space administrator.</p>',
                'header_bg_color' => '#e3f2fd',
                'footer_bg_color' => '#f8f9fa',
                'header_font_color' => '#0c5460',
                'footer_font_color' => '#6c757d',
            ],
        ];

        return isset($defaults[$templateType]) ? $defaults[$templateType] : [
            'subject' => 'Email from {space_name}',
            'header' => '<h2 style="color: #007bff; margin: 0;">{space_name}</h2>',
            'body' => "Hello {user_name},\n\nThis is a message from {space_name}.\n\nBest regards,\n{admin_name}",
            'footer' => '<p style="margin: 0;">This is an automated message from {space_name}</p>',
            'header_bg_color' => '#e3f2fd',
            'footer_bg_color' => '#f8f9fa',
            'header_font_color' => '#0c5460',
            'footer_font_color' => '#6c757d',
        ];
    }

    /**
     * Process template variables
     * 
     * @param array $variables
     * @param \humhub\modules\user\models\User|null $recipient
     * @param bool $isPreview Whether this is for preview (true) or actual email (false)
     * @return array
     */
    public function processTemplate($variables = [], $recipient = null, $isPreview = false)
    {
        $subject = $this->subject ?: '';
        $header = $this->header ?: '';
        $body = $this->body ?: '';
        $footer = $this->footer ?: '';

        foreach ($variables as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value, $subject);
            $header = str_replace('{' . $key . '}', $value, $header);
            $body = str_replace('{' . $key . '}', $value, $body);
            $footer = str_replace('{' . $key . '}', $value, $footer);
        }

        // Convert rich text content to HTML for email display
        $body = $this->convertRichTextToHtml($body, $recipient, $isPreview);
        
        // Process header and footer (they can be plain text or HTML)
        $header = $this->processHeaderFooter($header, $isPreview);
        $footer = $this->processHeaderFooter($footer, $isPreview);

        // Combine header, body, and footer into complete email
        $completeBody = $this->buildCompleteEmail($header, $body, $footer);

        return [
            'subject' => $subject,
            'body' => $completeBody
        ];
    }

    /**
     * Process header and footer content
     * 
     * @param string $content
     * @param bool $isPreview
     * @return string
     */
    protected function processHeaderFooter($content, $isPreview = false)
    {
        if (empty($content)) {
            return '';
        }

        // Check if content contains markdown formatting (like #, **, etc.)
        if (preg_match('/^#+\s+/m', $content) || preg_match('/\*\*.*\*\*/', $content) || 
            preg_match('/!\[.*\]\(file-guid:/', $content) || preg_match('/\[.*\]\(.*\)/', $content)) {
            // This is rich text content with markdown formatting, convert it
            return $this->convertRichTextToHtml($content, null, $isPreview);
        }

        // Check if content contains HTML tags (but not markdown)
        if (strpos($content, '<') !== false && strpos($content, '>') !== false) {
            // This is HTML content, process any plain URLs in it
            return $this->processPlainUrls($content);
        }

        // Otherwise, treat as plain text and convert to HTML, then process URLs
        $htmlContent = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        return $this->processPlainUrls($htmlContent);
    }

    /**
     * Build complete email with header, body, and footer
     * 
     * @param string $header
     * @param string $body
     * @param string $footer
     * @return string
     */
    protected function buildCompleteEmail($header, $body, $footer)
    {
        // Get background and font colors with defaults
        $headerBgColor = $this->header_bg_color ?: '#f8f9fa';
        $footerBgColor = $this->footer_bg_color ?: '#f8f9fa';
        $headerFontColor = $this->header_font_color ?: '#495057';
        $footerFontColor = $this->footer_font_color ?: '#6c757d';
        
        
        // Apply custom colors directly to header content
        if (!empty($header)) {
            $header = $this->applyCustomColors($header, $headerFontColor);
        }
        
        // Apply custom colors directly to footer content
        if (!empty($footer)) {
            $footer = $this->applyCustomColors($footer, $footerFontColor);
        }
        
        $email = '<div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6;">';
        
        // Header section
        if (!empty($header)) {
            $email .= '<div style="background-color: ' . htmlspecialchars($headerBgColor, ENT_QUOTES, 'UTF-8') . '; padding: 20px; text-align: center;">';
            $email .= $header;
            $email .= '</div>';
        }
        
        // Body section
        $email .= '<div style="padding: 20px; background-color: #ffffff;">';
        $email .= $body;
        $email .= '</div>';
        
        // Footer section
        if (!empty($footer)) {
            $email .= '<div style="background-color: ' . htmlspecialchars($footerBgColor, ENT_QUOTES, 'UTF-8') . '; padding: 20px; text-align: center; font-size: 12px;">';
            $email .= $footer;
            $email .= '</div>';
        }
        
        $email .= '</div>';
        
        return $email;
    }
    
    /**
     * Apply custom font color to HTML content
     * 
     * @param string $content
     * @param string $fontColor
     * @return string
     */
    protected function applyCustomColors($content, $fontColor)
    {
        // If content is plain text (no HTML tags), wrap it in a div with the color
        if (strpos($content, '<') === false) {
            return '<div style="color: ' . htmlspecialchars($fontColor, ENT_QUOTES, 'UTF-8') . ';">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</div>';
        }
        
        // If content has HTML tags, apply color to existing elements
        $content = preg_replace_callback('/<([^>]+)>/', function($matches) use ($fontColor) {
            $tag = $matches[1];
            
            // Skip if it's a closing tag
            if (strpos($tag, '/') === 0) {
                return $matches[0];
            }
            
            // If the tag already has a style attribute with color, skip it
            if (preg_match('/style\s*=\s*["\'][^"\']*color\s*:/i', $tag)) {
                return $matches[0];
            }
            
            // If the tag has a style attribute, add color to it
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $tag, $styleMatches)) {
                $styles = $styleMatches[1];
                $styles .= '; color: ' . $fontColor;
                $tag = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', 'style="' . $styles . '"', $tag);
            } else {
                // Add style attribute with color
                $tag .= ' style="color: ' . $fontColor . '"';
            }
            
            return '<' . $tag . '>';
        }, $content);
        
        return $content;
    }

    /**
     * Convert rich text content to HTML for email display
     * 
     * @param string $content
     * @param \humhub\modules\user\models\User|null $recipient
     * @param bool $isPreview Whether this is for preview (true) or actual email (false)
     * @return string
     */
    protected function convertRichTextToHtml($content, $recipient = null, $isPreview = false)
    {
        // Check if content is already HTML (from TinyMCE)
        if (strpos($content, '<') !== false && strpos($content, '>') !== false) {
            // Content is already HTML from TinyMCE, just process any remaining plain URLs
            $result = $this->processPlainUrls($content);
            
            // Ensure all links have the red color
            $result = $this->ensureLinkColors($result);
            
            return $result;
        }
        
        // Fallback to rich text converter for markdown content
        $result = \humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter::process($content, [
            'minimal' => false,
            'exclude' => ['mention', 'oembed'], // Exclude features that don't work well in emails
            \humhub\modules\content\widgets\richtext\converter\RichTextToEmailHtmlConverter::OPTION_RECEIVER_USER => $recipient, // Add receiver for proper token generation
        ]);
        
        // Process any remaining plain URLs that weren't converted to links
        $result = $this->processPlainUrls($result);
        
        // Ensure all links have the red color
        $result = $this->ensureLinkColors($result);
        
        return $result;
    }
    
    /**
     * Process plain URLs in HTML content and convert them to clickable links
     * 
     * @param string $html
     * @return string
     */
    protected function processPlainUrls($html)
    {
        // First, find all existing <a> tags and temporarily replace them
        $existingLinks = [];
        $html = preg_replace_callback('/<a[^>]*>.*?<\/a>/i', function($matches) use (&$existingLinks) {
            $placeholder = '___EXISTING_LINK_' . count($existingLinks) . '___';
            $existingLinks[] = $matches[0];
            return $placeholder;
        }, $html);
        
        // Now process URLs that are not in existing links
        // Improved pattern to catch more URL formats
        $pattern = '/\b(https?:\/\/[^\s<>"\'{}|\\^`\[\]]+)/i';
        
        $html = preg_replace_callback($pattern, function($matches) {
            $url = $matches[1];
            // Ensure URL is properly encoded
            $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            return '<a href="' . $encodedUrl . '" target="_blank" rel="noopener noreferrer" style="color: #dd0031; text-decoration: underline;">' . $encodedUrl . '</a>';
        }, $html);
        
        // Also handle URLs without protocol (www.example.com)
        $wwwPattern = '/\b(www\.[^\s<>"\'{}|\\^`\[\]]+)/i';
        $html = preg_replace_callback($wwwPattern, function($matches) {
            $url = 'https://' . $matches[1];
            $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            return '<a href="' . $encodedUrl . '" target="_blank" rel="noopener noreferrer" style="color: #dd0031; text-decoration: underline;">' . $encodedUrl . '</a>';
        }, $html);
        
        // Restore existing links
        foreach ($existingLinks as $index => $link) {
            $html = str_replace('___EXISTING_LINK_' . $index . '___', $link, $html);
        }
        
        return $html;
    }
    
    /**
     * Ensure all links have the red color (#dd0031)
     * 
     * @param string $html
     * @return string
     */
    protected function ensureLinkColors($html)
    {
        // Process all <a> tags to ensure they have the red color
        $html = preg_replace_callback('/<a([^>]*)>/i', function($matches) {
            $attributes = $matches[1];
            
            // Check if style attribute already exists
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/', $attributes, $styleMatches)) {
                $styles = $styleMatches[1];
                // Add or update color
                if (preg_match('/color\s*:\s*[^;]+/', $styles)) {
                    $styles = preg_replace('/color\s*:\s*[^;]+/', 'color: #dd0031', $styles);
                } else {
                    $styles .= '; color: #dd0031; text-decoration: underline;';
                }
                $attributes = preg_replace('/style\s*=\s*["\'][^"\']*["\']/', 'style="' . $styles . '"', $attributes);
            } else {
                // Add style attribute with red color
                $attributes .= ' style="color: #dd0031; text-decoration: underline;"';
            }
            
            return '<a' . $attributes . '>';
        }, $html);
        
        return $html;
    }
    
    /**
     * Remove color styles from HTML to allow email template colors to take precedence
     * 
     * @param string $html
     * @return string
     */
    protected function removeColorStyles($html)
    {
        // Remove color-related properties while preserving other styles
        $html = preg_replace_callback('/style\s*=\s*["\']([^"\']*)["\']/i', function($matches) {
            $styles = $matches[1];
            
            // Remove color-related properties
            $styles = preg_replace('/color\s*:\s*[^;]+;?\s*/i', '', $styles);
            $styles = preg_replace('/background-color\s*:\s*[^;]+;?\s*/i', '', $styles);
            
            // Clean up any double semicolons or leading/trailing semicolons
            $styles = preg_replace('/;\s*;/', ';', $styles);
            $styles = preg_replace('/^;\s*/', '', $styles);
            $styles = preg_replace('/;\s*$/', '', $styles);
            
            // If no styles left, return empty string
            if (empty(trim($styles))) {
                return '';
            }
            
            return 'style="' . $styles . '"';
        }, $html);
        
        // Ensure all links have the red color
        $html = preg_replace_callback('/<a([^>]*)>/i', function($matches) {
            $attributes = $matches[1];
            
            // Check if style attribute already exists
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/', $attributes, $styleMatches)) {
                $styles = $styleMatches[1];
                // Add or update color
                if (preg_match('/color\s*:\s*[^;]+/', $styles)) {
                    $styles = preg_replace('/color\s*:\s*[^;]+/', 'color: #dd0031', $styles);
                } else {
                    $styles .= '; color: #dd0031; text-decoration: underline;';
                }
                $attributes = preg_replace('/style\s*=\s*["\'][^"\']*["\']/', 'style="' . $styles . '"', $attributes);
            } else {
                // Add style attribute
                $attributes .= ' style="color: #dd0031; text-decoration: underline;"';
            }
            
            return '<a' . $attributes . '>';
        }, $html);
        
        return $html;
    }
    

    /**
     * Create a public copy of an image file for email access
     * 
     * @param \humhub\modules\file\models\File $file
     * @return string
     */
    protected function createPublicImageCopy($file)
    {
        // Check if this is an image file
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $fileExtension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $imageExtensions)) {
            // Not an image, return original URL
            return $file->getUrl();
        }
        
        // Create public email images directory if it doesn't exist
        $publicDir = Yii::getAlias('@webroot/uploads/email_images');
        if (!is_dir($publicDir)) {
            if (!mkdir($publicDir, 0755, true)) {
                \Yii::error('Failed to create email_images directory: ' . $publicDir, 'spaceJoinQuestions');
                return $file->getUrl();
            }
        }
        
        // Generate unique filename for the public copy
        $publicFileName = $file->guid . '_' . time() . '.' . $fileExtension;
        $publicPath = $publicDir . '/' . $publicFileName;
        
        // Check if public copy already exists
        if (file_exists($publicPath)) {
            return Yii::getAlias('@web/uploads/email_images/' . $publicFileName);
        }
        
        try {
            // Copy the file to public directory
            $sourcePath = $file->store->get($file->file_name);
            if (file_exists($sourcePath) && copy($sourcePath, $publicPath)) {
                // Set proper permissions
                chmod($publicPath, 0644);
                
                // Clean up old files periodically
                $this->cleanupOldEmailImages();
                
                // Return public URL
                return Yii::getAlias('@web/uploads/email_images/' . $publicFileName);
            } else {
                \Yii::error('Failed to copy file to public directory: ' . $sourcePath . ' -> ' . $publicPath, 'spaceJoinQuestions');
            }
        } catch (\Exception $e) {
            \Yii::error('Exception while creating public image copy: ' . $e->getMessage(), 'spaceJoinQuestions');
        }
        
        // If copy fails, return original URL
        return $file->getUrl();
    }

    /**
     * Clean up old email image files (older than 30 days)
     */
    protected function cleanupOldEmailImages()
    {
        // Only run cleanup occasionally (1 in 10 chance)
        if (rand(1, 10) !== 1) {
            return;
        }
        
        $publicDir = Yii::getAlias('@webroot/uploads/email_images');
        if (!is_dir($publicDir)) {
            return;
        }
        
        $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 days ago
        
        $files = glob($publicDir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
} 