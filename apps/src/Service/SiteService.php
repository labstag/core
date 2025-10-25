<?php

namespace Labstag\Service;

use Labstag\Entity\Configuration;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SiteService
{
    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private iterable $dataLibs,
        private ConfigurationService $configurationService,
        private FileService $fileService,
        private TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function asset(mixed $entity, string $field, bool $placeholder = true): string
    {
        $file = $this->fileService->asset($entity, $field);

        if ('' !== $file) {
            return $file;
        }

        if (!$placeholder) {
            return '';
        }

        if (!$entity instanceof Configuration) {
            $config = $this->configurationService->getConfiguration();

            return $this->asset($config, 'placeholder');
        }

        return 'https://picsum.photos/1200/1200?md5=' . md5((string) $entity->getId());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFileFavicon(): ?array
    {
        $favicon = $this->getFavicon('favicon.ico');

        return is_null($favicon) ? $this->getFavicon('favicon') : $favicon;
    }

    public function getTitleMeta(object $entity): ?string
    {
        foreach ($this->dataLibs as $datalib) {
            if ($datalib->supports($entity)) {
                return $datalib->getTitleMeta($entity);
            }
        }

        return '';
    }

    public function isEnable(object $entity): bool
    {
        return !(!$entity->isEnable() && !$this->getUser() instanceof UserInterface);
        // TODO : Prévoir de vérifier les droits de l'utilisateur
    }

    /**
     * @param mixed[] $data
     */
    public function isHome(array $data): bool
    {
        return isset($data['entity']) && $data['entity'] instanceof Page && PageEnum::HOME->value == $data['entity']->getType();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getFavicon(string $type): ?array
    {
        $info = null;
        $file = $this->fileService->getFileInAdapter('assets', 'manifest.json');
        $json = json_decode(file_get_contents($file), true);
        foreach ($json as $title => $file) {
            $info = null;
            if (0 === substr_count((string) $title, $type)) {
                continue;
            }

            $file          = str_replace('/assets/', '', $file);
            $fileInAdapter = $this->fileService->getFileInAdapter('assets', $file);
            if (is_null($fileInAdapter)) {
                continue;
            }

            $info = $this->fileService->getInfoImage($fileInAdapter);
            if (!is_array($info['data'])) {
                continue;
            }

            if (0 === substr_count((string) $info['data']['type'], 'image')) {
                continue;
            }

            break;
        }

        if (is_null($info)) {
            return null;
        }

        return $info;
    }

    private function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        return $token instanceof TokenInterface ? $token->getUser() : null;
    }
}
