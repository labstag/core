<?php

namespace Labstag\FrontForm;

use Labstag\Entity\User;
use Labstag\Form\Front\LostPasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class LostPasswordForm extends FrontFormAbstract
{
    public function getCode(): string
    {
        return 'lost-password';
    }

    public function getForm(): string
    {
        return LostPasswordType::class;
    }

    public function getName(): string
    {
        return (string) new TranslatableMessage('Form lost password');
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
        $request          = $this->requestStack->getCurrentRequest();
        $entityRepository = $this->entityManager->getRepository(User::class);
        $form->handleRequest($request);
        $execute = false;
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $data = $form->get('find');
            $find = $data->getData();
            $user = $entityRepository->findUserName($find);
            if ($user instanceof User) {
                $this->workflowService->change(User::class, 'passwordlost', $user->getId());
            }

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
