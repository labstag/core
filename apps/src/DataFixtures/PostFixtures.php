<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Entity\Tag;
use Labstag\Entity\User;
use Labstag\DataFixtures\Abstract\FixtureLib;
use Override;

class PostFixtures extends FixtureLib implements DependentFixtureInterface
{
    /**
     * @var int
     */
    protected const NUMBER_POST = 50;

    /**
     * @var User[]
     */
    protected array $users = [];

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            UserFixtures::class,
        ];
    }

    #[Override]
    public function load(ObjectManager $objectManager): void
    {
        $this->users      = $this->getIdentitiesByClass(User::class);
        $this->tags       = $this->getIdentitiesByClass(Tag::class, 'post');
        $this->categories = $this->getIdentitiesByClass(Category::class, 'post');
        $this->loadForeach(self::NUMBER_POST, 'addPost', $objectManager);
        $objectManager->flush();
    }

    protected function addPost(Generator $generator, ObjectManager $objectManager): void
    {
        $meta = new Meta();
        $post = new Post();
        $post->setResume($generator->unique()->text(200));
        $post->setCreatedAt($generator->unique()->dateTimeBetween('- 8 month', 'now'));
        $post->setMeta($meta);
        $this->setImage($post, 'imgFile');
        $post->setEnable((bool) random_int(0, 1));
        $post->setRefuser($this->getReference(array_rand($this->users), User::class));
        $post->setTitle($generator->unique()->colorName());
        $this->addParagraphText($post);
        $this->addTagToEntity($post);
        $this->addCategoryToEntity($post);
        $this->addReference('post_' . md5(uniqid()), $post);
        $objectManager->persist($post);
    }
}
