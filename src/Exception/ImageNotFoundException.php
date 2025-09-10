<?php

namespace App\Exception;

use RuntimeException;

class ImageNotFoundException extends RuntimeException
{
    public function __construct(string $filename)
    {
        parent::__construct(sprintf('Image file "%s" not found.', $filename));
    }
}
