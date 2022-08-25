<?php

namespace iRealWorlds\Netopia\Exceptions;

use Exception;
use iRealWorlds\Netopia\Enums\ExceptionCode;

class NetopiaException extends Exception
{
    /**
     * Create a new NetopiaException instance from a given exception code.
     *
     * @param ExceptionCode $exceptionCode
     * @return static
     */
    public static function fromCode(ExceptionCode $exceptionCode): self
    {
        return new self(code: $exceptionCode->value);
    }
}
