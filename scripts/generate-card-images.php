<?php

// This script generates individual card images from a sprite sheet
// You'll need to place a cards sprite sheet at public/images/cards/cards-sprite.png

$suits = ['hearts', 'diamonds', 'clubs', 'spades'];
$ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

$cardWidth = 64;
$cardHeight = 96;
$spriteWidth = 832;
$spriteHeight = 384;

// Create individual card images directory
$outputDir = __DIR__ . '/../public/images/cards/individual/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Load sprite sheet
$spriteSheet = imagecreatefrompng(__DIR__ . '/../public/images/cards/cards-sprite.png');

if (!$spriteSheet) {
    echo "Error: Could not load sprite sheet. Please place cards-sprite.png in public/images/cards/\n";
    exit(1);
}

$cardIndex = 0;
foreach ($suits as $suitIndex => $suit) {
    foreach ($ranks as $rankIndex => $rank) {
        // Calculate position in sprite sheet
        $x = $rankIndex * $cardWidth;
        $y = $suitIndex * $cardHeight;
        
        // Create new image for this card
        $cardImage = imagecreatetruecolor($cardWidth, $cardHeight);
        
        // Copy card from sprite sheet
        imagecopy($cardImage, $spriteSheet, 0, 0, $x, $y, $cardWidth, $cardHeight);
        
        // Save individual card image
        $filename = $outputDir . $suit . '_' . $rank . '.png';
        imagepng($cardImage, $filename);
        
        // Clean up
        imagedestroy($cardImage);
        
        echo "Generated: {$suit}_{$rank}.png\n";
        $cardIndex++;
    }
}

// Clean up
imagedestroy($spriteSheet);

echo "Generated {$cardIndex} card images successfully!\n";
