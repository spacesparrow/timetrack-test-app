<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Intl\Exception\NotImplementedException;

class TaskActionNotImplementedException extends NotImplementedException
{
    /** @var string  */
    protected $message = 'This action is not implemented for tasks now.';

    /**
     * TaskActionNotImplementedException constructor.
     */
    public function __construct()
    {
        parent::__construct($this->message);
    }
}