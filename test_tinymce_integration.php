<?php
/**
 * Test script to verify TinyMCE integration for email templates
 */

echo "=== TINYMCE INTEGRATION TEST ===\n\n";

// Test 1: Check if TinyMCE widget class exists
echo "1. CHECKING TINYMCE WIDGET:\n";
echo "===========================\n";

if (class_exists('humhub\modules\spaceJoinQuestions\widgets\EmailTinyMce')) {
    echo "✅ EmailTinyMce widget class exists\n";
} else {
    echo "❌ EmailTinyMce widget class NOT found\n";
}

// Test 2: Check if TinyMCE dependencies are available
echo "\n2. CHECKING TINYMCE DEPENDENCIES:\n";
echo "=================================\n";

$dependencies = [
    'humhub\modules\custom_pages\widgets\TinyMce' => 'Custom Pages TinyMCE widget',
    'humhub\modules\custom_pages\assets\TinyMcePluginsAssets' => 'TinyMCE plugins assets',
    'dosamigos\tinymce\TinyMce' => 'TinyMCE base widget'
];

foreach ($dependencies as $class => $description) {
    if (class_exists($class)) {
        echo "✅ $description: Available\n";
    } else {
        echo "❌ $description: NOT available\n";
    }
}

// Test 3: Check if upload controller exists
echo "\n3. CHECKING UPLOAD CONTROLLER:\n";
echo "==============================\n";

if (class_exists('humhub\modules\spaceJoinQuestions\controllers\UploadController')) {
    echo "✅ UploadController class exists\n";
} else {
    echo "❌ UploadController class NOT found\n";
}

// Test 4: Check if URL rules are configured
echo "\n4. CHECKING URL CONFIGURATION:\n";
echo "==============================\n";

$configFile = 'config.php';
if (file_exists($configFile)) {
    $config = include $configFile;
    if (isset($config['urlManagerRules'])) {
        $hasUploadRule = false;
        foreach ($config['urlManagerRules'] as $rule) {
            if (strpos($rule, 'upload/image') !== false) {
                $hasUploadRule = true;
                break;
            }
        }
        
        if ($hasUploadRule) {
            echo "✅ Upload URL rule configured\n";
        } else {
            echo "❌ Upload URL rule NOT configured\n";
        }
    } else {
        echo "❌ URL rules not found in config\n";
    }
} else {
    echo "❌ Config file not found\n";
}

// Test 5: Test EmailTemplate model with HTML content
echo "\n5. TESTING EMAIL TEMPLATE PROCESSING:\n";
echo "====================================\n";

try {
    if (class_exists('humhub\modules\spaceJoinQuestions\models\EmailTemplate')) {
        echo "✅ EmailTemplate model exists\n";
        
        // Test HTML content processing
        $htmlContent = '<p>Hello <strong>John Doe</strong>,</p>
<p>Please visit our website: <a href="https://example.com" style="color: #dd0031;">https://example.com</a></p>
<p>Also check out <a href="https://www.humhub.org">www.humhub.org</a> for more information.</p>
<p>Contact us at <a href="mailto:test@example.com">test@example.com</a></p>';

        echo "🧪 Testing HTML content processing...\n";
        echo "Input: " . htmlspecialchars($htmlContent) . "\n";
        
        // Test the ensureLinkColors method using reflection
        $template = new \humhub\modules\spaceJoinQuestions\models\EmailTemplate();
        $reflection = new ReflectionClass($template);
        
        if ($reflection->hasMethod('ensureLinkColors')) {
            $method = $reflection->getMethod('ensureLinkColors');
            $method->setAccessible(true);
            
            $result = $method->invoke($template, $htmlContent);
            echo "📤 Result: " . htmlspecialchars($result) . "\n";
            
            $linkCount = substr_count($result, '<a ');
            $redColorCount = substr_count($result, 'color: #dd0031');
            
            echo "🔗 Links found: $linkCount\n";
            echo "🎨 Red color instances: $redColorCount\n";
            
            if ($linkCount > 0 && $redColorCount > 0) {
                echo "✅ Link color processing is working!\n";
            } else {
                echo "❌ Link color processing is NOT working!\n";
            }
        } else {
            echo "❌ ensureLinkColors method not found\n";
        }
    } else {
        echo "❌ EmailTemplate model NOT found\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Test 6: Check file permissions for uploads
echo "\n6. CHECKING UPLOAD PERMISSIONS:\n";
echo "===============================\n";

$uploadDirs = [
    '../../uploads',
    '../../../uploads',
    '../../../../uploads'
];

foreach ($uploadDirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ Upload directory found: $dir\n";
        echo "📁 Writable: " . (is_writable($dir) ? 'Yes' : 'No') . "\n";
        
        // Check if email_images subdirectory exists or can be created
        $emailImagesDir = $dir . '/email_images';
        if (is_dir($emailImagesDir)) {
            echo "✅ Email images directory exists: $emailImagesDir\n";
        } else {
            if (is_writable($dir)) {
                echo "📁 Email images directory can be created: $emailImagesDir\n";
            } else {
                echo "❌ Cannot create email images directory: $emailImagesDir\n";
            }
        }
        break;
    }
}

echo "\n=== TINYMCE INTEGRATION TEST COMPLETE ===\n";
echo "If all tests pass, TinyMCE should work properly for email templates.\n";
?>
