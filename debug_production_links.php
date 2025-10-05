<?php
/**
 * Production Debug Script for Link Rendering Issues
 * Run this on your production server to investigate link processing
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SPACE JOIN QUESTIONS - LINK RENDERING DEBUG ===\n\n";

// 1. Check if the updated files exist and have the correct content
echo "1. CHECKING FILE VERSIONS AND CONTENT:\n";
echo "=====================================\n";

$files_to_check = [
    'models/EmailTemplate.php' => [
        'processPlainUrls' => 'function processPlainUrls',
        'red_color' => 'color: #dd0031',
        'www_pattern' => 'www\.[^\s<>"\'{}|\\^`\[\]]+'
    ],
    'views/email-template/preview.php' => [
        'red_css' => 'color: #dd0031 !important',
        'debug_panel' => 'Debug Information',
        'javascript' => 'visible-link-count'
    ],
    'VERSION' => [
        'version' => '2.3.1'
    ]
];

foreach ($files_to_check as $file => $checks) {
    echo "\n--- Checking: $file ---\n";
    
    if (!file_exists($file)) {
        echo "❌ FILE NOT FOUND: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    echo "✅ File exists\n";
    echo "📏 File size: " . strlen($content) . " bytes\n";
    echo "📅 Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    
    foreach ($checks as $check_name => $search_string) {
        if (strpos($content, $search_string) !== false) {
            echo "✅ $check_name: Found\n";
        } else {
            echo "❌ $check_name: NOT FOUND (searching for: $search_string)\n";
        }
    }
}

// 2. Test the EmailTemplate class directly
echo "\n\n2. TESTING EMAILTEMPLATE CLASS:\n";
echo "==============================\n";

try {
    // Include the EmailTemplate class
    if (file_exists('models/EmailTemplate.php')) {
        require_once 'models/EmailTemplate.php';
        echo "✅ EmailTemplate.php loaded successfully\n";
        
        // Test if the class exists
        if (class_exists('humhub\modules\spaceJoinQuestions\models\EmailTemplate')) {
            echo "✅ EmailTemplate class exists\n";
            
            // Create a test instance
            $template = new \humhub\modules\spaceJoinQuestions\models\EmailTemplate();
            echo "✅ EmailTemplate instance created\n";
            
            // Test the processPlainUrls method using reflection
            $reflection = new ReflectionClass($template);
            if ($reflection->hasMethod('processPlainUrls')) {
                echo "✅ processPlainUrls method exists\n";
                
                // Test the method
                $method = $reflection->getMethod('processPlainUrls');
                $method->setAccessible(true);
                
                $testHtml = '<p>Visit https://example.com and www.humhub.org for more info.</p>';
                echo "🧪 Testing with: $testHtml\n";
                
                $result = $method->invoke($template, $testHtml);
                echo "📤 Result: " . htmlspecialchars($result) . "\n";
                
                // Check if links were created
                $linkCount = substr_count($result, '<a ');
                echo "🔗 Links found: $linkCount\n";
                
                // Check for red color
                $redColorCount = substr_count($result, 'color: #dd0031');
                echo "🎨 Red color instances: $redColorCount\n";
                
                if ($linkCount > 0 && $redColorCount > 0) {
                    echo "✅ Link processing is working correctly!\n";
                } else {
                    echo "❌ Link processing is NOT working correctly!\n";
                }
                
            } else {
                echo "❌ processPlainUrls method NOT found\n";
            }
        } else {
            echo "❌ EmailTemplate class NOT found\n";
        }
    } else {
        echo "❌ EmailTemplate.php file NOT found\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// 3. Check HumHub environment
echo "\n\n3. CHECKING HUMHUB ENVIRONMENT:\n";
echo "=============================\n";

// Check if we're in a HumHub environment
if (defined('YII_DEBUG')) {
    echo "✅ YII_DEBUG: " . (YII_DEBUG ? 'true' : 'false') . "\n";
} else {
    echo "❌ YII_DEBUG not defined\n";
}

if (defined('YII_ENV')) {
    echo "✅ YII_ENV: " . YII_ENV . "\n";
} else {
    echo "❌ YII_ENV not defined\n";
}

// Check for HumHub paths
$humhub_paths = [
    '../../humhub',
    '../../../humhub',
    '../../../../humhub'
];

foreach ($humhub_paths as $path) {
    if (file_exists($path)) {
        echo "✅ HumHub found at: $path\n";
        break;
    }
}

// 4. Check cache and permissions
echo "\n\n4. CHECKING CACHE AND PERMISSIONS:\n";
echo "===================================\n";

// Check if cache directory exists and is writable
$cache_dirs = [
    '../../runtime/cache',
    '../../../runtime/cache',
    '../../../../runtime/cache'
];

foreach ($cache_dirs as $cache_dir) {
    if (is_dir($cache_dir)) {
        echo "✅ Cache directory found: $cache_dir\n";
        echo "📁 Cache directory writable: " . (is_writable($cache_dir) ? 'Yes' : 'No') . "\n";
        
        // List cache files
        $cache_files = glob($cache_dir . '/*');
        echo "📄 Cache files count: " . count($cache_files) . "\n";
        break;
    }
}

// Check file permissions
echo "\n📁 Current directory permissions:\n";
echo "Readable: " . (is_readable('.') ? 'Yes' : 'No') . "\n";
echo "Writable: " . (is_writable('.') ? 'Yes' : 'No') . "\n";

// 5. Test email template processing
echo "\n\n5. TESTING EMAIL TEMPLATE PROCESSING:\n";
echo "====================================\n";

try {
    // Try to simulate the email template processing
    $testContent = "Hello {user_name},\n\nPlease visit https://example.com and www.humhub.org for more information.\n\nBest regards,\n{admin_name}";
    
    echo "🧪 Test content: $testContent\n";
    
    // Check if we can access the EmailTemplate class
    if (class_exists('humhub\modules\spaceJoinQuestions\models\EmailTemplate')) {
        $template = new \humhub\modules\spaceJoinQuestions\models\EmailTemplate();
        
        // Test the convertRichTextToHtml method
        $reflection = new ReflectionClass($template);
        if ($reflection->hasMethod('convertRichTextToHtml')) {
            $method = $reflection->getMethod('convertRichTextToHtml');
            $method->setAccessible(true);
            
            echo "🧪 Testing convertRichTextToHtml method...\n";
            $result = $method->invoke($template, $testContent, null, true);
            echo "📤 Result: " . htmlspecialchars($result) . "\n";
            
            $linkCount = substr_count($result, '<a ');
            echo "🔗 Links found: $linkCount\n";
            
            if ($linkCount > 0) {
                echo "✅ Rich text conversion is working!\n";
            } else {
                echo "❌ Rich text conversion is NOT working!\n";
            }
        } else {
            echo "❌ convertRichTextToHtml method not found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR in email template processing: " . $e->getMessage() . "\n";
}

// 6. Check for any error logs
echo "\n\n6. CHECKING FOR ERROR LOGS:\n";
echo "===========================\n";

$log_dirs = [
    '../../runtime/logs',
    '../../../runtime/logs',
    '../../../../runtime/logs'
];

foreach ($log_dirs as $log_dir) {
    if (is_dir($log_dir)) {
        echo "✅ Log directory found: $log_dir\n";
        
        $log_files = glob($log_dir . '/*.log');
        if (!empty($log_files)) {
            echo "📄 Log files found: " . count($log_files) . "\n";
            
            // Check the most recent log file
            $latest_log = max($log_files);
            echo "📄 Latest log: " . basename($latest_log) . "\n";
            echo "📅 Last modified: " . date('Y-m-d H:i:s', filemtime($latest_log)) . "\n";
            
            // Check for any space-join-questions related errors
            $log_content = file_get_contents($latest_log);
            if (strpos($log_content, 'spaceJoinQuestions') !== false) {
                echo "⚠️  Found space-join-questions related entries in logs\n";
            }
        }
        break;
    }
}

echo "\n\n=== DEBUG COMPLETE ===\n";
echo "Please share this output to help identify the issue.\n";
?>
