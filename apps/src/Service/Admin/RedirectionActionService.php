<?php

declare(strict_types=1);

namespace Labstag\Service\Admin;

use Labstag\Repository\RedirectionRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RedirectionActionService
{
    public function __construct(
        private RedirectionRepository $redirectionRepository,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function redirectToIndex(): RedirectResponse
    {
        $url = $this->urlGenerator->generate('admin_redirection_index');

        return new RedirectResponse($url);
    }

    public function testSource(int $redirectionId): RedirectResponse
    {
        $redirection = $this->redirectionRepository->find($redirectionId);

        return new RedirectResponse($redirection->getSource());
    }
}
