<?php

namespace App\Enums;

enum DoiState {
    case Draft;
    case Registered;
    case Findable;

    public function getType(): string
    {
        return match($this) {
            self::Draft => 'draft',
            self::Registered => 'registered',
            self::Findable => 'findable',
        };
    }
}