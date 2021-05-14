<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Intl\Exception\NotImplementedException;

/*
 * Should be thrown if not implemented action was reached in Voter
 */
class TaskActionNotImplementedException extends NotImplementedException
{
    /** @var string */
    protected $message = 'This action is not implemented for tasks now.';

    /**
     * TaskActionNotImplementedException constructor.
     */
    public function __construct()
    {
        parent::__construct($this->message);
    }
}
