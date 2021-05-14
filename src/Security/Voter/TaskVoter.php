<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Task;
use App\Exception\TaskActionNotImplementedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const ACTION_CREATE = 'create';
    public const ACTION_VIEW = 'view';
    public const SUPPORTED_ACTIONS = [self::ACTION_CREATE, self::ACTION_VIEW];

    /**
     * @param string $attribute
     * @param mixed  $subject
     */
    protected function supports($attribute, $subject): bool
    {
        /*
         * Check if this voter supports provided subject and actions (attribute param)
         */
        return in_array($attribute, self::SUPPORTED_ACTIONS) && $subject instanceof Task;
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /*
         * Deny access if there is no logged in user
         */
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        /*
         * Find matched action and check access for it
         * Throw exception if action (attribute param) has no check method
         */
        switch ($attribute) {
            case self::ACTION_CREATE:
                return $this->canCreate($task, $user);
            case self::ACTION_VIEW:
                return $this->canView($task, $user);
            default:
                throw new TaskActionNotImplementedException();
        }
    }

    private function canCreate(Task $task, UserInterface $user): bool
    {
        return true;
    }

    private function canView(Task $task, UserInterface $user): bool
    {
        return $user === $task->getUser();
    }
}
