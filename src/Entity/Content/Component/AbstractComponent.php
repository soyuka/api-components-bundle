<?php

namespace Silverback\ApiComponentBundle\Entity\Content\Component;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Silverback\ApiComponentBundle\Entity\Content\AbstractContent;
use Silverback\ApiComponentBundle\Entity\Content\ComponentGroup;
use Silverback\ApiComponentBundle\Entity\Content\ContentInterface;
use Silverback\ApiComponentBundle\Entity\DeleteCascadeInterface;
use Silverback\ApiComponentBundle\Entity\ValidComponentTrait;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractComponent
 * @package Silverback\ApiComponentBundle\Entity\Content\Component
 * @author Daniel West <daniel@silverback.is>
 * @ORM\Entity()
 * @ORM\Table(name="component")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "content" = "Silverback\ApiComponentBundle\Entity\Content\Component\Content\Content",
 *     "form" = "Silverback\ApiComponentBundle\Entity\Content\Component\Form\Form",
 *     "gallery" = "Silverback\ApiComponentBundle\Entity\Content\Component\Gallery\Gallery",
 *     "gallery_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Gallery\GalleryItem",
 *     "hero" = "Silverback\ApiComponentBundle\Entity\Content\Component\Hero\Hero",
 *     "feature_columns" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\Columns\FeatureColumns",
 *     "feature_columns_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\Columns\FeatureColumnsItem",
 *     "feature_stacked" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\Stacked\FeatureStacked",
 *     "feature_stacked_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\Stacked\FeatureStackedItem",
 *     "feature_text_list" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\TextList\FeatureTextList",
 *     "feature_text_list_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Feature\TextList\FeatureTextListItem",
 *     "article" = "Silverback\ApiComponentBundle\Entity\Content\Component\Article\Article",
 *     "nav_bar" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\NavBar\NavBar",
 *     "nav_bar_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\NavBar\NavBarItem",
 *     "tabs" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\Tabs\Tabs",
 *     "tabs_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\Tabs\TabsItem",
 *     "menu" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\Menu\Menu",
 *     "menu_item" = "Silverback\ApiComponentBundle\Entity\Content\Component\Navigation\Menu\MenuItem"
 * })
 * @ORM\EntityListeners({"Silverback\ApiComponentBundle\EntityListener\ComponentListener"})
 */
abstract class AbstractComponent implements ComponentInterface, DeleteCascadeInterface
{
    use ValidComponentTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"component", "content"})
     * @var null|string
     */
    private $className;

    /**
     * @ORM\OneToMany(targetEntity="Silverback\ApiComponentBundle\Entity\Content\Component\ComponentLocation", mappedBy="component")
     * @var ArrayCollection|ComponentLocation[]
     */
    protected $locations;

    /**
     * @ORM\OneToMany(targetEntity="Silverback\ApiComponentBundle\Entity\Content\ComponentGroup", mappedBy="parent", cascade={"persist"})
     * @ApiProperty(attributes={"fetchEager": false})
     * @Groups({"component", "content"})
     * @var ArrayCollection|ComponentGroup[]
     */
    protected $componentGroups;

    /**
     * AbstractComponent constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->getHex();
        $this->locations = new ArrayCollection;
        $this->componentGroups = new ArrayCollection;
        $this->validComponents = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param null|string $className
     * @return AbstractComponent
     */
    public function setClassName(?string $className): AbstractComponent
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @param ContentInterface $content
     * @return AbstractComponent
     */
    public function addLocation(ContentInterface $content): AbstractComponent
    {
        $this->locations->add($content);
        return $this;
    }

    /**
     * @param ContentInterface $content
     * @return AbstractComponent
     */
    public function removeLocation(ContentInterface $content): AbstractComponent
    {
        $this->locations->removeElement($content);
        return $this;
    }

    /**
     * @param array $componentGroups
     * @return AbstractComponent
     */
    public function setComponentGroups(array $componentGroups): AbstractComponent
    {
        $this->componentGroups = new ArrayCollection;
        foreach ($componentGroups as $componentGroup) {
            $this->addComponentGroup($componentGroup);
        }
        return $this;
    }

    /**
     * @param ComponentGroup $componentGroup
     * @return AbstractComponent
     */
    public function addComponentGroup(ComponentGroup $componentGroup): AbstractComponent
    {
        $componentGroup->setParent($this);
        $this->componentGroups->add($componentGroup);
        return $this;
    }

    /**
     * @param ComponentGroup $componentGroup
     * @return AbstractComponent
     */
    public function removeComponentGroup(ComponentGroup $componentGroup): AbstractComponent
    {
        $this->componentGroups->removeElement($componentGroup);
        return $this;
    }

    /**
     * @return ArrayCollection|ComponentGroup[]
     */
    public function getComponentGroups(): Collection
    {
        return $this->componentGroups;
    }

    /**
     * Return the component name for front-end to decipher
     * @Groups({"content", "component"})
     * @return string
     */
    public static function getComponentName(): string
    {
        $explodedClass = explode('\\', static::class);
        return array_pop($explodedClass);
    }

    /**
     * @return bool
     */
    public function onDeleteCascade(): bool
    {
        return false;
    }

    /**
     * @param AbstractComponent $component
     * @param int $componentGroupOffset
     * @return ComponentGroup
     * @throws \InvalidArgumentException
     */
    private function getComponentComponentGroup(AbstractComponent $component, int $componentGroupOffset = 0): ComponentGroup
    {
        /** @var ComponentGroup $componentGroup */
        $componentGroup = $component->getComponentGroups()->get($componentGroupOffset);
        if (null === $componentGroup) {
            throw new \InvalidArgumentException(sprintf('There is no component group child of this component with the offset %d', $componentGroupOffset));
        }
        return $componentGroup;
    }

    /**
     * @Groups({"component_write"})
     * @param AbstractComponent $parent
     * @param int $componentGroupOffset
     * @return AbstractComponent
     * @throws \InvalidArgumentException
     */
    public function setParent(AbstractComponent $parent, int $componentGroupOffset = 0): AbstractComponent
    {
        if (!\in_array($parent, $this->getParentComponents(), true)) {
            $componentGroup = $this->getComponentComponentGroup($parent, $componentGroupOffset);
            if (!$componentGroup->hasComponent($this)) {
                $componentGroup->addComponent(new ComponentLocation($componentGroup, $this));
            }
        }
        return $this;
    }

    /**
     * @return AbstractComponent[]
     */
    private function getParentComponents(): array
    {
        $parentContent = $this->getParentContent();
        return array_unique(
            array_filter(
                array_map(
                    function ($content) {
                        if ($content instanceof ComponentGroup) {
                            return $content->getParent();
                        }
                    },
                    $parentContent
                )
            )
        );
    }

    /**
     * @return AbstractContent[]
     */
    private function getParentContent(): array
    {
        return array_unique(
            array_filter(
                array_map(
                    function (ComponentLocation $loc) {
                        return $loc->getContent();
                    },
                    $this->locations->toArray()
                )
            )
        );
    }
}