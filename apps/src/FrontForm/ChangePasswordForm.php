<?php

namespace Labstag\FrontForm;

use Labstag\Entity\User;
use Labstag\Form\Front\ChangePasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class ChangePasswordForm extends FrontFormAbstract
{
    public function getCode(): string
    {
        return 'change-password';
    }

    public function getForm(): string
    {
        return ChangePasswordType::class;
    }

    public function getName(): string
    {
        return new TranslatableMessage('Form change password');
    }

    #[\Override]
    public function setParamsTwig(
        FormInterface $form,
        $formCode,
        $paragraph,
        $data,
        bool $disable = false,
        bool $save = true,
    ): array
    {
        unset($save, $disable, $formCode);
        $request = $this->requestStack->getCurrentRequest();
        $uid     = $request->query->get('uid');
        if (empty($uid)) {
            return [
                'paragraph' => $paragraph,
                'error'     => 'notuser',
            ];
        }

        $entityRepository = $this->entityManager->getRepository(User::class);
        $user             = $entityRepository->find($uid);
        if (!$user instanceof User) {
            return [
                'paragraph' => $paragraph,
                'error'     => 'notuser',
            ];
        }

        $places = $this->workflowService->getPlaces($user);
        if (!isset($places['lostpassword'])) {
            return [
                'paragraph' => $paragraph,
                'error'     => 'notallowed',
            ];
        }

        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);
        $execute = false;
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $data          = $form->get('plainPassword');
            $plainPassword = $data->getData();

            $hash     = $this->userService->hashPassword($user, $plainPassword);
            $user->setPassword($hash);
            $entityRepository->save($user);
            $this->workflowService->change(User::class, 'changepassword', $user->getId());
            $execute = true;
        }

        return [
            'execute'   => $execute,
            'form'      => $form,
            'paragraph' => $paragraph,
            'data'      => $data,
        ];
    }
}
