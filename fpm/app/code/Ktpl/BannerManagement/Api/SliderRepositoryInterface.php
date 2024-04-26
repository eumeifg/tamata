<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Api;

/**
 * Banner crud interface.
 *
 * @api
 * @since 100.0.2
 */
interface SliderRepositoryInterface
{
    /**
     * Save banner.
     *
     * @param  \Ktpl\BannerManagement\Api\Data\SliderInterface $banner
     * @return \Ktpl\BannerManagement\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\SliderInterface $slider);

    /**
     * Retrieve banner.
     *
     * @param  int $bannerId
     * @return \Ktpl\BannerManagement\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sliderId);

    /**
     * Retrieve banners matching the specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\BannerManagement\Api\Data\SliderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slider.
     *
     * @param  \Ktpl\BannerManagement\Api\Data\SliderInterface $slider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\SliderInterface $slider);

    /**
     * Delete banner by ID.
     *
     * @param  int $sliderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sliderId);
}
