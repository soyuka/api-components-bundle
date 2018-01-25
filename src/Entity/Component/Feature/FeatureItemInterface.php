<?php

namespace Silverback\ApiComponentBundle\Entity\Component\Feature;

interface FeatureItemInterface
{
    /**
     * @return AbstractFeature
     */
    public function getFeature(): AbstractFeature;

    /**
     * @param AbstractFeature $feature
     */
    public function setFeature(AbstractFeature $feature): void;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     */
    public function setLabel(string $label): void;

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int;

    /**
     * @param int|null $sortOrder
     */
    public function setSortOrder(?int $sortOrder): void;

    /**
     * @return int
     */
    public function getLink(): int;

    /**
     * @param int $link
     */
    public function setLink(int $link): void;
}