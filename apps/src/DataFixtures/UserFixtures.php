<?php

namespace Labstag\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\User;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;

class UserFixtures extends Fixture
{
    public function __construct(
        protected WorkflowService $workflowService,
        protected UserService $userService
    )
    {
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $data = $this->data();
        foreach ($data as $row) {
            $this->setUser($objectManager, $row);
        }

        $objectManager->flush();
    }

    private function data(): array
    {
        return [
            [
                'username' => 'admin',
                'password' => 'password',
                'email'    => 'admin@test.local',
                'roles'    => ['ROLE_ADMIN'],
            ],
            [
                'username' => 'superadmin',
                'password' => 'password',
                'email'    => 'superadmin@test.local',
                'roles'    => ['ROLE_SUPER_ADMIN'],
            ],
        ];
    }

    private function setUser(ObjectManager $objectManager, array $data): void
    {
        $user = new User();
        $this->workflowService->init($user);
        $hash = $this->userService->hashPassword($user, $data['password']);
        $user->setEnable(true);
        $user->setUsername($data['username']);
        $user->setPassword($hash);
        $user->setEmail($data['email']);
        $user->setRoles($data['roles']);

        $objectManager->persist($user);
    }
}
