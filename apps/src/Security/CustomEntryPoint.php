<?php

namespace Labstag\Security;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Service\SlugService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Throwable;

class CustomEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        protected PageRepository $pageRepository,
        protected SlugService $slugService,
    )
    {
    }

    public function start(Request $request, ?Throwable $authException = null): Response
    {
        unset($request, $authException);

        $page = $this->pageRepository->findOneBy(
            [
                'type' => PageEnum::LOGIN->value,
            ]
        );
        if (!$page instanceof Page) {
            return new RedirectResponse($this->urlGenerator->generate('front'));
        }

        $slug = $this->slugService->forEntity($page);

        return new RedirectResponse(
            $this->urlGenerator->generate(
                'front',
                ['slug' => $slug]
            )
        );
    }
}
