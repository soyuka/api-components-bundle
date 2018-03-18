<?php

namespace Silverback\ApiComponentBundle\Tests\Unit\Factory\Entity\Content\Component\Gallery;

use Silverback\ApiComponentBundle\Factory\Entity\Content\Component\Gallery\GalleryFactory;
use Silverback\ApiComponentBundle\Factory\Entity\Content\Component\Gallery\GalleryItemFactory;
use Silverback\ApiComponentBundle\Tests\Unit\Factory\Entity\AbstractFactory;

class GalleryFactoryTest extends AbstractFactory
{
    protected $presets = ['component'];

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->className = GalleryFactory::class;
        $this->testOps = [];
        $itemFactoryMock = $this
            ->getMockBuilder(GalleryItemFactory::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->extraConstructorArgs = [
            $itemFactoryMock
        ];
        parent::setUp();
    }
}
