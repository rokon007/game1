<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PlayingCard extends Component
{
    public $suit;
    public $rank;
    public $selected;
    public $clickable;
    public $size;

    public function __construct($suit, $rank, $selected = false, $clickable = true, $size = 'normal')
    {
        $this->suit = $suit;
        $this->rank = $rank;
        $this->selected = $selected;
        $this->clickable = $clickable;
        $this->size = $size;
    }

    public function getSuitSymbol()
    {
        return match($this->suit) {
            'hearts' => '♥',
            'diamonds' => '♦',
            'clubs' => '♣',
            'spades' => '♠',
            default => '?'
        };
    }

    public function getDisplayRank()
    {
        return match($this->rank) {
            'A' => 'A',
            'K' => 'K',
            'Q' => 'Q',
            'J' => 'J',
            default => $this->rank
        };
    }

    public function getCardClasses()
    {
        $classes = ['card', $this->suit];

        if ($this->selected) {
            $classes[] = 'selected';
        }

        if (in_array($this->rank, ['A'])) {
            $classes[] = 'ace';
        }

        if (in_array($this->rank, ['K', 'Q', 'J'])) {
            $classes[] = strtolower($this->rank === 'K' ? 'king' : ($this->rank === 'Q' ? 'queen' : 'jack'));
        }

        if ($this->size === 'small') {
            $classes[] = 'small';
        }

        return implode(' ', $classes);
    }

    public function render()
    {
        return view('components.playing-card');
    }
}
