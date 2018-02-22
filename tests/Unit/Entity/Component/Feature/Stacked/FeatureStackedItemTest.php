<?php

namespace Silverback\ApiComponentBundle\Tests\Unit\Entity\Component\Feature;

use Silverback\ApiComponentBundle\Entity\Component\Feature\Stacked\FeatureStackedItem;
use Silverback\ApiComponentBundle\Tests\Unit\Entity\AbstractEntityTest;
use Symfony\Component\Validator\Constraints\NotBlank;

class FeatureStackedItemTest extends AbstractEntityTest
{
    public function test_constraints()
    {
        $entity = new FeatureStackedItem();
        $constraints = $this->getConstraints($entity);
        $this->assertTrue($this->instanceInArray(NotBlank::class, $constraints['description']));
    }
}