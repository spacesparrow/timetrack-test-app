<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/*
 * Should be thrown if unsupported export type was requested or if type support was not implemented yet
 */
class UnsupportedExportFormatException extends Exception
{
    /** @var string */
    protected $message = 'Provided type %s not allowed for import';

    /**
     * UnsupportedExportFormatException constructor.
     */
    public function __construct(string $extension)
    {
        $this->message = sprintf($this->message, $extension);
        parent::__construct($this->message, Response::HTTP_NOT_ACCEPTABLE);
    }
}
