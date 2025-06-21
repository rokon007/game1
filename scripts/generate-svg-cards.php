<?php

// This script generates SVG playing cards
$suits = [
    'hearts' => ['symbol' => '♥', 'color' => '#dc2626'],
    'diamonds' => ['symbol' => '♦', 'color' => '#dc2626'],
    'clubs' => ['symbol' => '♣', 'color' => '#1f2937'],
    'spades' => ['symbol' => '♠', 'color' => '#1f2937']
];

$ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

// Create SVG cards directory
$outputDir = __DIR__ . '/../public/images/cards/svg/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

foreach ($suits as $suitName => $suitData) {
    foreach ($ranks as $rank) {
        $svg = generateCardSVG($rank, $suitName, $suitData['symbol'], $suitData['color']);
        file_put_contents($outputDir . $suitName . '_' . $rank . '.svg', $svg);
        echo "Generated: {$suitName}_{$rank}.svg\n";
    }
}

function generateCardSVG($rank, $suit, $symbol, $color) {
    return <<<SVG
<svg width="64" height="96" viewBox="0 0 64 96" xmlns="http://www.w3.org/2000/svg">
  <!-- Card background -->
  <rect width="64" height="96" rx="8" ry="8" fill="white" stroke="#333" stroke-width="2"/>
  
  <!-- Top left corner -->
  <text x="8" y="16" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="{$color}">{$rank}</text>
  <text x="8" y="32" font-family="Arial, sans-serif" font-size="16" fill="{$color}">{$symbol}</text>
  
  <!-- Center symbol -->
  <text x="32" y="56" font-family="Arial, sans-serif" font-size="24" text-anchor="middle" fill="{$color}">{$symbol}</text>
  
  <!-- Bottom right corner (rotated) -->
  <g transform="rotate(180 56 80)">
    <text x="8" y="16" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="{$color}">{$rank}</text>
    <text x="8" y="32" font-family="Arial, sans-serif" font-size="16" fill="{$color}">{$symbol}</text>
  </g>
</svg>
SVG;
}

echo "Generated " . (count($suits) * count($ranks)) . " SVG card files successfully!\n";
