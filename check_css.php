<?php
$c = file_get_contents('C:/wamp64/www/Project-D/assets/css/style.css');
$lines = explode("\n", $c);

// Check for END TRACKS DIRECTORY PAGE occurrences
$count1 = 0;
$positions1 = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'END TRACKS DIRECTORY PAGE') !== false) {
        $count1++;
        $positions1[] = $i + 1;
    }
}
echo "END TRACKS DIRECTORY PAGE: $count1 times at lines " . implode(', ', $positions1) . "\n";

// Check for TRACK DETAIL PAGE occurrences
$count2 = 0;
$positions2 = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'TRACK DETAIL PAGE') !== false) {
        $count2++;
        $positions2[] = $i + 1;
    }
}
echo "TRACK DETAIL PAGE: $count2 times at lines " . implode(', ', $positions2) . "\n";

// Check for TRACKS DIRECTORY PAGE occurrences
$count3 = 0;
$positions3 = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'TRACKS DIRECTORY PAGE') !== false) {
        $count3++;
        $positions3[] = $i + 1;
    }
}
echo "TRACKS DIRECTORY PAGE: $count3 times at lines " . implode(', ', $positions3) . "\n";

// Check for duplicated @media rules
$mediaCount = 0;
$mediaPositions = [];
foreach ($lines as $i => $line) {
    if (preg_match('/@media\s*\(max-width:\s*(\d+)px\)/', trim($line), $m)) {
        $mediaCount++;
        $mediaPositions[] = ($i+1) . ': ' . trim($line);
    }
}
echo "\n@media rules: $mediaCount\n";
foreach ($mediaPositions as $p) echo "  $p\n";

// Check for any malformed lines (comment immediately followed by @media on same line)
echo "\nChecking for malformed comment@media lines...\n";
foreach ($lines as $i => $line) {
    if (strpos($line, '*/') !== false && strpos($line, '@media') !== false && strpos($line, '*/') < strpos($line, '@media')) {
        echo "  Line " . ($i+1) . ": " . trim($line) . "\n";
    }
}

// Check total lines
echo "\nTotal lines: " . count($lines) . "\n";

// Check for duplicate CSS blocks
$blocks = [];
preg_match_all('/^\s*([.#][^{]+)\s*\{/m', $c, $blockMatches);
foreach ($blockMatches[1] as $name) {
    $name = trim($name);
    if (!isset($blocks[$name])) $blocks[$name] = 0;
    $blocks[$name]++;
}
$duplicates = array_filter($blocks, function($v) { return $v > 1; });
if (!empty($duplicates)) {
    echo "\nDuplicate CSS selectors:\n";
    foreach ($duplicates as $name => $count) echo "  $name: $count times\n";
} else {
    echo "\nNo duplicate CSS selectors found.\n";
}
