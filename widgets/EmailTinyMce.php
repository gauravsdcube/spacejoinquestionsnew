<?php

namespace humhub\modules\spaceJoinQuestions\widgets;

use humhub\modules\custom_pages\assets\TinyMcePluginsAssets;
use Yii;
use yii\helpers\ArrayHelper;
use dosamigos\tinymce\TinyMce;

/**
 * TinyMCE widget specifically configured for email templates
 * 
 * This widget provides email-optimized TinyMCE configuration with:
 * - Email-specific toolbar and plugins
 * - Link handling optimized for emails
 * - Table support for email layouts
 * - Image handling for email content
 * - Code view for HTML editing
 */
class EmailTinyMce extends TinyMce
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initEmailDefaults();
    }

    /**
     * Initialize email-specific TinyMCE configuration
     */
    private function initEmailDefaults()
    {
        $this->options = ArrayHelper::merge([
            'rows' => 15,
        ], $this->options);

        $this->language = substr($this->language ?? Yii::$app->language, 0, 2);

        // Register TinyMCE plugins assets
        $tinyMcePluginsAssets = TinyMcePluginsAssets::register($this->view);
        
        // Configure external plugins
        $external_plugins = [
            'codemirror' => $tinyMcePluginsAssets->baseUrl . '/codemirror/plugin.min.js',
        ];

        // Email-optimized TinyMCE configuration
        $this->clientOptions = ArrayHelper::merge([
            // Core plugins for email editing
            'plugins' => [
                'code',           // Source code editing
                'autolink',       // Auto-link detection
                'link',           // Link management
                'image',          // Image handling
                'lists',          // Bullet and numbered lists
                'table',          // Table support
                'wordcount',      // Word count
                'anchor',         // Anchor links
                'fullscreen',     // Fullscreen editing
                'paste',          // Paste handling
                'searchreplace',  // Find and replace
                'charmap',        // Special characters
                'emoticons',      // Emoticons
                'template',       // Templates
                'textcolor',      // Text color
                'colorpicker',    // Color picker
                'textpattern',    // Text patterns
                'hr',             // Horizontal rule
                'nonbreaking',    // Non-breaking space
                'pagebreak',      // Page break
                'preview',        // Preview
                'save',           // Save
                'directionality', // Text direction
                'visualblocks',   // Visual blocks
                'visualchars',    // Visual characters
                'wordcount'       // Word count
            ],
            
            // Email-optimized toolbar
            'toolbar' => 'undo redo | blocks | bold italic underline strikethrough | ' .
                        'alignleft aligncenter alignright alignjustify | ' .
                        'bullist numlist outdent indent | ' .
                        'link image table | ' .
                        'forecolor backcolor | ' .
                        'removeformat | code fullscreen',
            
            // Menu configuration
            'menu' => [
                'insert' => [
                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Insert'),
                    'items' => 'image link anchor inserttable | hr charmap emoticons | template',
                ],
                'format' => [
                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Format'),
                    'items' => 'bold italic underline strikethrough superscript subscript | ' .
                              'alignleft aligncenter alignright alignjustify | ' .
                              'bullist numlist outdent indent | ' .
                              'blockquote | forecolor backcolor | removeformat',
                ],
                'table' => [
                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Table'),
                    'items' => 'inserttable | cell row column | tableprops deletetable',
                ],
                'tools' => [
                    'title' => Yii::t('SpaceJoinQuestionsModule.base', 'Tools'),
                    'items' => 'code | fullscreen | preview | searchreplace | wordcount',
                ],
            ],
            
            // Email-specific content styling
            'content_style' => '
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 14px; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                }
                a { 
                    color: #dd0031; 
                    text-decoration: underline; 
                }
                a:hover { 
                    color: #b30026; 
                }
                table { 
                    border-collapse: collapse; 
                    width: 100%; 
                }
                td, th { 
                    padding: 8px; 
                    border: 1px solid #ddd; 
                }
                .img-responsive { 
                    display: block; 
                    max-width: 100%; 
                    height: auto; 
                }
            ',
            
            // Allow all HTML elements for email flexibility
            'valid_elements' => '*[*]',
            'extended_valid_elements' => 'style[type|media|scoped],link[rel|type|href],meta[name|content|http-equiv]',
            
            // Email-specific settings
            'relative_urls' => false,
            'remove_script_host' => true,
            'convert_urls' => true,
            'remove_redundant_brs' => true,
            'cleanup_on_startup' => true,
            'trim_span_elements' => false,
            'verify_html' => false,
            
            // Link handling for emails
            'link_context_toolbar' => true,
            'link_default_protocol' => 'https',
            'link_title' => false,
            'link_assume_external_targets' => true,
            'link_default_target' => '_blank',
            
            // Table configuration for emails
            'table_default_attributes' => [
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
            ],
            'table_default_styles' => [
                'border-collapse' => 'collapse',
                'width' => '100%',
            ],
            'table_class_list' => [
                ['title' => 'None', 'value' => ''],
                ['title' => 'Email Table', 'value' => 'email-table'],
                ['title' => 'Responsive Table', 'value' => 'responsive-table'],
            ],
            
            // Image handling for emails
            'image_advtab' => true,
            'image_caption' => false,
            'image_description' => false,
            'image_dimensions' => false,
            'image_title' => false,
            'image_upload_url' => '/space-join-questions/upload/image',
            'image_upload_handler' => 'function (blobInfo, success, failure) {
                // Custom image upload handler for email templates
                var formData = new FormData();
                formData.append("file", blobInfo.blob(), blobInfo.filename());
                
                fetch("/space-join-questions/upload/image", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        success(result.url);
                    } else {
                        failure(result.error);
                    }
                })
                .catch(error => {
                    failure("Upload failed: " + error.message);
                });
            }',
            
            // Paste handling for email content
            'paste_auto_cleanup_on_paste' => true,
            'paste_remove_styles_if_webkit' => false,
            'paste_remove_empty_paragraphs' => true,
            'paste_merge_formats' => true,
            'paste_convert_word_fake_lists' => true,
            'paste_webkit_styles' => 'color font-size font-family background-color',
            
            // External plugins
            'external_plugins' => $external_plugins,
            
            // Setup callback for email-specific initialization
            'setup' => 'function (editor) {
                // Add email-specific toolbar button
                editor.ui.registry.addButton("emaillink", {
                    text: "Email Link",
                    icon: "link",
                    tooltip: "Insert email link",
                    onAction: function () {
                        editor.windowManager.open({
                            title: "Insert Email Link",
                            body: {
                                type: "panel",
                                items: [
                                    {
                                        type: "input",
                                        name: "email",
                                        label: "Email Address",
                                        placeholder: "example@domain.com"
                                    },
                                    {
                                        type: "input",
                                        name: "text",
                                        label: "Link Text (optional)",
                                        placeholder: "Click here"
                                    }
                                ]
                            },
                            buttons: [
                                {
                                    type: "cancel",
                                    text: "Cancel"
                                },
                                {
                                    type: "submit",
                                    text: "Insert",
                                    primary: true
                                }
                            ],
                            onSubmit: function (api) {
                                var data = api.getData();
                                var email = data.email;
                                var text = data.text || email;
                                
                                if (email) {
                                    editor.insertContent("<a href=\"mailto:" + email + "\" style=\"color: #dd0031; text-decoration: underline;\">" + text + "</a>");
                                    api.close();
                                }
                            }
                        });
                    }
                });
                
                // Add email-specific menu item
                editor.ui.registry.addMenuItem("emaillink", {
                    text: "Email Link",
                    icon: "link",
                    onAction: function () {
                        editor.execCommand("mceEmailLink");
                    }
                });
                
                // Auto-format links as they are typed
                editor.on("keyup", function (e) {
                    if (e.keyCode === 32) { // Space key
                        var content = editor.getContent();
                        var urlRegex = /(https?:\/\/[^\s]+)/g;
                        var wwwRegex = /(www\.[^\s]+)/g;
                        
                        content = content.replace(urlRegex, function(url) {
                            return "<a href=\"" + url + "\" target=\"_blank\" style=\"color: #dd0031; text-decoration: underline;\">" + url + "</a>";
                        });
                        
                        content = content.replace(wwwRegex, function(url) {
                            return "<a href=\"https://" + url + "\" target=\"_blank\" style=\"color: #dd0031; text-decoration: underline;\">" + url + "</a>";
                        });
                        
                        if (content !== editor.getContent()) {
                            editor.setContent(content);
                        }
                    }
                });
            }',
            
            // Add email link button to toolbar
            'toolbar' => 'undo redo | blocks | bold italic underline strikethrough | ' .
                        'alignleft aligncenter alignright alignjustify | ' .
                        'bullist numlist outdent indent | ' .
                        'link emaillink image table | ' .
                        'forecolor backcolor | ' .
                        'removeformat | code fullscreen',
        ], $this->clientOptions);

        // Fix modal window issues
        $this->view->registerJs('
            $(document).on("focusin", "[class^=tox-] input", function(e) {
                e.stopImmediatePropagation();
            });
            
            // Remove existing editor before reinitializing
            if (typeof tinymce !== "undefined") {
                tinymce.remove("#' . $this->options['id'] . '");
            }
        ');
    }
}
