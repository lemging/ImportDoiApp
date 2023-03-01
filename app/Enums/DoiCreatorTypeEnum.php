<?php

namespace App\Enums;

enum DoiCreatorTypeEnum {
    case Person;
    case Organization;
    case Unknown;

    public function getType(): string
    {
        return match($this) {
            self::Person => 'person',
            self::Organization => 'organization',
            self::Unknown => 'unknown',
        };
    }
}