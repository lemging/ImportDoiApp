<?php

namespace App\Model\Data;

/**
 * Abstract class for data objects that are sent directly to the template.
 */
abstract class AData
{
    public ?int $navbarActiveIndex = null;

    public ?string $accountUnsetErrorMessage = null;
}



