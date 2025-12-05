<?php

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Labstag\SlugHandler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\Proxy;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Sluggable\Handler\SlugHandlerWithUniqueCallbackInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Labstag\Enum\PageEnum;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function Symfony\Component\String\u;

/**
 * Sluggable handler which slugs all parent nodes
 * recursively and synchronizes on updates. For instance
 * category tree slug could look like "food/fruits/apples".
 *
 * @final since gedmo/doctrine-extensions 3.11
 */
class PageSlugHandler implements SlugHandlerWithUniqueCallbackInterface
{
    public const SEPARATOR = '/';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * True if node is being inserted.
     */
    private bool $isInsert = false;

    /**
     * Transliterated parent slug.
     */
    private string $parentSlug = '';

    private string $prefix = '';

    private string $suffix = '';

    /**
     * Used path separator.
     */
    private string $usedPathSeparator = self::SEPARATOR;

    public function __construct(
        protected SluggableListener $sluggable,
    )
    {
    }

    public function beforeMakingUnique(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug): void
    {
        unset($sluggableAdapter);
        $slug = $this->transliterate($slug, $config['separator'], $object);
    }

    public function handlesUrlization(): bool
    {
        return false;
    }

    public function onChangeDecision(
        SluggableAdapter $sluggableAdapter,
        array &$config,
        $object,
        &$slug,
        &$needToChangeSlug,
    ): void
    {
        unset($slug);
        $this->objectManager       = $sluggableAdapter->getObjectManager();
        $this->isInsert            = $this->objectManager->getUnitOfWork()->isScheduledForInsert($object);
        $options = $config['handlers'][static::class];

        $this->usedPathSeparator = $options['separator'] ?? self::SEPARATOR;
        $this->prefix            = $options['prefix'] ?? '';
        $this->suffix            = $options['suffix'] ?? '';

        if (!$this->isInsert && !$needToChangeSlug) {
            $changeSet = $sluggableAdapter->getObjectChangeSet($this->objectManager->getUnitOfWork(), $object);
            if (isset($changeSet[$options['parentRelationField']])) {
                $needToChangeSlug = true;
            }
        }
    }

    public function onSlugCompletion(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug): void
    {
        if (PageEnum::HOME->value == $object->getType()) {
            $this->setHome($sluggableAdapter, $object);
            $slug = '';
        }

        if ($this->isInsert) {
            return;
        }

        $wrapper                          = AbstractWrapper::wrap($object, $this->objectManager);
        $classMetadata                    = $wrapper->getMetadata();
        $target                           = $wrapper->getPropertyValue($config['slug']);
        $config['pathSeparator']          = $this->usedPathSeparator;
        $sluggableAdapter->replaceRelative($object, $config, $target . $config['pathSeparator'], $slug);
        $uow = $this->objectManager->getUnitOfWork();
        // update in memory objects
        foreach ($uow->getIdentityMap() as $className => $objects) {
            // for inheritance mapped classes, only root is always in the identity map
            if ($className !== $wrapper->getRootObjectName()) {
                continue;
            }

            $this->otherObjects($sluggableAdapter, $objects, $classMetadata, $config, $target, $slug, $uow);
        }
    }

    public function postSlugBuild(SluggableAdapter $sluggableAdapter, array &$config, $object, &$slug): void
    {
        unset($sluggableAdapter, $slug);
        $options          = $config['handlers'][static::class];
        $this->parentSlug = '';

        $wrapper = AbstractWrapper::wrap($object, $this->objectManager);
        $parent  = $wrapper->getPropertyValue($options['parentRelationField']);
        if ($parent) {
            $parent           = AbstractWrapper::wrap($parent, $this->objectManager);
            $this->parentSlug = $parent->getPropertyValue($config['slug']);
            // if needed, remove suffix from parentSlug, so we can use it to prepend it to our slug
            if (isset($options['suffix'])) {
                $this->parentSlug = u($this->parentSlug)->trimSuffix($options['suffix'])->toString();
            }
        }
    }

    /**
     * Transliterates the slug and prefixes the slug
     * by collection of parent slugs.
     *
     * @param string $separator
     * @param object $object
     */
    public function transliterate(string $text, $separator, $object): string
    {
        unset($separator, $object);
        $slug = $text . $this->suffix;

        if (0 !== strlen($this->parentSlug)) {
            return $this->parentSlug . $this->usedPathSeparator . $slug;
        }

        // if no parentSlug, apply our prefix
        return $this->prefix . $slug;
    }

    /**
     * @param ClassMetadata<object> $meta
     */
    public static function validate(array $options, ClassMetadata $meta): void
    {
        if (!$meta->isSingleValuedAssociation($options['parentRelationField'])) {
            throw new InvalidMappingException(
                sprintf(
                    'Unable to find tree parent slug relation through field - [%s] in class - %s',
                    $options['parentRelationField'],
                    $meta->getName()
                )
            );
        }
    }

    private function otherObjects(
        SluggableAdapter $sluggableAdapter,
        $objects,
        $meta,
        array $config,
        $target,
        &$slug,
        $uow,
    ): void
    {
        foreach ($objects as $object) {
            // @todo: Remove the check against `method_exists()` in the next major release.
            if (($object instanceof Proxy || method_exists(
                $object,
                '__isInitialized'
            )) && !$object->__isInitialized()
            ) {
                continue;
            }

            $objectSlug = (string) $meta->getFieldValue($object, $config['slug']);
            if (preg_match(sprintf('@^%s%s@smi', $target, $config['pathSeparator']), $objectSlug)) {
                $objectSlug = str_replace($target, $slug, $objectSlug);
                $meta->setFieldValue($object, $config['slug'], $objectSlug);
                $sluggableAdapter->setOriginalObjectProperty($uow, $object, $config['slug'], $objectSlug);
            }
        }
    }

    private function setHome(SluggableAdapter $sluggableAdapter, $object): void
    {
        $objectManager    = $sluggableAdapter->getObjectManager();
        $classMetadata    = $objectManager->getClassMetadata($object::class);
        $objectRepository = $objectManager->getRepository($classMetadata->getName());

        $existingPages = $objectRepository->findBy(
            [
                'type' => $object->getType(),
            ]
        );

        foreach ($existingPages as $existingPage) {
            if ($existingPage === $object) {
                continue;
            }

            $existingPage->setPage(PageEnum::PAGE->value);
            $asciiSlugger = new AsciiSlugger();
            $existingPage->setPage($asciiSlugger->slug((string) $object->getTitle())->lower());
            $objectManager->persist($existingPage);
        }
    }
}
