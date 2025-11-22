<?php

namespace Labstag\Security\Voter;

use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\MenuItemDto;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class EasyadminVoter extends Voter
{
    public function __construct(
        private Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        unset($subject);

        return in_array(
            $attribute,
            [
                Permission::EA_ACCESS_ENTITY,
                Permission::EA_EXECUTE_ACTION,
                Permission::EA_VIEW_MENU_ITEM,
                Permission::EA_VIEW_FIELD,
                Permission::EA_EXIT_IMPERSONATION,
            ]
        );
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match (true) {
            Permission::EA_EXECUTE_ACTION === $attribute => $this->canExecuteAction($subject, $user),
            Permission::EA_VIEW_MENU_ITEM === $attribute => $this->canViewMenuItem($subject, $user),
            default                                      => true,
        };
    }

    private function canExecuteAction(mixed $subject, UserInterface $user): bool
    {
        $actionSubject = $subject['action'];

        if (is_string($actionSubject)) {
            $entityClass = $this->getEntityClass($subject);
            if (is_null($entityClass)) {
                return true;
            }

            $reflectionClass = new ReflectionClass($entityClass);

            return $this->getPermission($user);
        }

        if ($actionSubject instanceof ActionDto) {
            $entityClass = $subject['entity'] instanceof EntityDto ? $subject['entity']->getFqcn() : null;
            if (is_null($entityClass)) {
                return $this->getPermission($user);
            }

            $reflectionClass = new ReflectionClass($entityClass);

            return $this->getPermission($user);
        }

        return true;
    }

    private function canViewMenuItem(mixed $subject, UserInterface $user): bool
    {
        if ($subject instanceof MenuItemDto) {
            $type = $subject->getType();

            return match ($type) {
                'crud'  => $this->menuCrud($subject, $user),
                default => true,
            };
        }

        return true;
    }

    private function getEntityClass(mixed $subject): ?string
    {
        if (isset($subject['entityFqcn'])) {
            return $subject['entityFqcn'];
        }

        if (isset($subject['entity']) && $subject['entity'] instanceof EntityDto) {
            return $subject['entity']->getFqcn();
        }

        return null;
    }

    private function getPermission(UserInterface $user): bool
    {
        return $this->security->isGrantedForUser($user, 'ROLE_ADMIN');
    }

    private function menuCrud(MenuItemDto $menuItemDto, UserInterface $user): bool
    {
        $routeParams     = $menuItemDto->getRouteParameters();
        new ReflectionClass($routeParams['entityFqcn']);

        return $this->getPermission($user);
    }
}
