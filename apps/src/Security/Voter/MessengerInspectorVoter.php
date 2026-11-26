<?php

namespace Labstag\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessengerInspectorVoter extends Voter
{
    public const VIEW_DASHBOARD = 'MESSENGER_INSPECTOR_VIEW_DASHBOARD';
    public const VIEW_MESSAGES = 'MESSENGER_INSPECTOR_VIEW_MESSAGES';
    public const VIEW_PAYLOAD = 'MESSENGER_INSPECTOR_VIEW_PAYLOAD';
    public const VIEW_ERRORS = 'MESSENGER_INSPECTOR_VIEW_ERRORS';
    public const RETRY = 'MESSENGER_INSPECTOR_RETRY';
    public const DELETE = 'MESSENGER_INSPECTOR_DELETE';
    public const PURGE = 'MESSENGER_INSPECTOR_PURGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::VIEW_DASHBOARD, self::VIEW_MESSAGES, self::VIEW_PAYLOAD,
            self::VIEW_ERRORS, self::RETRY, self::DELETE, self::PURGE,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}