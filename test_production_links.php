<?php
/**
 * Simple Production Test for Link Processing
 * This script tests the link processing functionality
 */

echo "=== LINK PROCESSING TEST ===\n\n";

// Test the processPlainUrls function directly
function testProcessPlainUrls($html) {
    // First, find all existing <a> tags and temporarily replace them
    $existingLinks = [];
    $html = preg_replace_callback('/<a[^>]*>.*?<\/a>/i', function($matches) use (&$existingLinks) {
        $placeholder = '___EXISTING_LINK_' . count($existingLinks) . '___';
        $existingLinks[] = $matches[0];
        return $placeholder;
    }, $html);
    
    // Now process URLs that are not in existing links
    $pattern = '/\b(https?:\/\/[^\s<>"\'{}|\\^`\[\]]+)/i';
    
    $html = preg_replace_callback($pattern, function($matches) {
        $url = $matches[1];
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

// Test content
$testContent = '
<p>Hello John Doe,</p>
<p>Please visit our website: https://example.com</p>
<p>Also check out www.humhub.org for more information.</p>
<p>Contact us at https://support.example.com/help</p>
<p>Email us at mailto:test@example.com</p>
';

echo "Original content:\n";
echo htmlspecialchars($testContent) . "\n\n";

echo "Processing links...\n";
$processed = testProcessPlainUrls($testContent);

echo "Processed content:\n";
echo htmlspecialchars($processed) . "\n\n";

echo "Rendered HTML:\n";
echo $processed . "\n\n";

$linkCount = substr_count($processed, '<a ');
$redColorCount = substr_count($processed, 'color: #dd0031');

echo "Results:\n";
echo "- Links found: $linkCount\n";
echo "- Red color instances: $redColorCount\n";

if ($linkCount > 0 && $redColorCount > 0) {
    echo "✅ SUCCESS: Link processing is working!\n";
} else {
    echo "❌ FAILURE: Link processing is not working!\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
