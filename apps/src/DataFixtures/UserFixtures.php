<?php

namespace Labstag\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Labstag\Service\FileService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;

class UserFixtures extends FixtureLib
{
    public function __construct(
        protected FileService $fileService,
        protected WorkflowService $workflowService,
        protected UserService $userService
    )
    {
        parent::__construct($fileService);
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $data = $this->data();
        foreach ($data as $row) {
            $entity = $row['entity'];
            unset($row['entity']);
            $this->setUser($objectManager, $entity, $row);
        }

        $objectManager->flush();
    }

    private function data(): array
    {
        $roles = $this->userService->getRoles();

        $user = new User();
        $user->setRoles(
            [
                $roles['Admin'],
            ]
        );
        $user->setLanguage('fr');
        $user->setUsername('admin');
        $user->setEmail('admin@test.local');

        $admin = $user;

        $user = new User();
        $user->setRoles(
            [
                $roles['Super Admin'],
            ]
        );
        $user->setLanguage('fr');
        $user->setUsername('superadmin');
        $user->setEmail('superadmin@test.local');

        $superadmin = $user;

        return [
            [
                'entity'   => $admin,
                'password' => 'password',
            ],
            [
                'entity'   => $superadmin,
                'password' => 'password',
            ],
        ];
    }

    private function setUser(ObjectManager $objectManager, User $user, array $data): void
    {
        $this->workflowService->init($user);
        $hash = $this->userService->hashPassword($user, $data['password']);
        $user->setEnable(true);
        $user->setPassword($hash);
        $this->setImage($user, 'avatarFile');

        $this->addReference('user_'.$user->getUsername(), $user);

        $objectManager->persist($user);
    }
}
