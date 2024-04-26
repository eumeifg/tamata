<?php

namespace CAT\SearchPage\Api\Data;

interface SearchPagesBuilderDataInterface
{
    /**
     * Get banner slider data
     *
     * @api
     * @return \CAT\SearchPage\Api\Data\BannerSliderDataInterface[]
     */
    public function getBannerSlider(): array;

    /**
     * Set banner slider data
     *
     * @api
     * @param \CAT\SearchPage\Api\Data\BannerSliderDataInterface[] $bannerSlider
     * @return $this
     */
    public function setBannerSlider(array $bannerSlider): SearchPagesBuilderDataInterface;
}
