<?php

namespace Labstag\FrontForm;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Form\Front\LoginType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;

class LoginForm extends FrontFormAbstract
{
    public function getCode(): string
    {
        return 'login';
    }

    public function getForm(): string
    {
        return LoginType::class;
    }

    public function getName(): string
    {
        return new TranslatableMessage('Form login');
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

        $error        = $this->authenticationUtils->getLastAuthenticationError();
        $request      = $this->requestStack->getCurrentRequest();
        $referer      = $request->headers->get('referer');
        $data         = [
            'username'    => $this->authenticationUtils->getLastUsername(),
            'target_path' => $referer ?? $this->generateUrl('front'),
        ];

        $entityRepository = $this->entityManager->getRepository(Page::class);
        $lost             = $entityRepository->findOneBy(
            [
                'type' => PageEnum::LOSTPASSWORD->value,
            ]
        );
        $form = $this->createForm(
            $this->getForm(),
            $data,
            ['method' => 'POST']
        );
        $execute = false;

        return [
            'lost'      => $lost,
            'error'     => $error,
            'execute'   => $execute,
            'form'      => $form,
            'paragraph' => $paragraph,
            'data'      => $data,
        ];
    }
}
