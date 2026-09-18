<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StoryTag extends Tag
{

    /**
     * @var Collection<int, Story>
     */
    #[ORM\ManyToMany(targetEntity: Story::class, inversedBy: 'tags', cascade: ['persist', 'detach'])]
    #[ORM\JoinTable(name: 'tag_story')]
    protected Collection $stories;

    public function __construct()
    {
        $this->stories  = new ArrayCollection();
    }

    public function addStory(Story $story): static
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
        }

        return $this;
    }

    /**
     * @return Collection<int, Story>
     */
    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function removeStory(Story $story): static
    {
        $this->stories->removeElement($story);

        return $this;
    }
}
