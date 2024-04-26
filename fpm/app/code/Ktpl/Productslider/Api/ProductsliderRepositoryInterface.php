<?php


namespace Ktpl\Productslider\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductsliderRepositoryInterface
{

    /**
     * Save Productslider
     * @param \Ktpl\Productslider\Api\Data\ProductsliderInterface $productslider
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ktpl\Productslider\Api\Data\ProductsliderInterface $productslider
    );

    /**
     * Retrieve Productslider
     * @param string $productsliderId
     * @return \Ktpl\Productslider\Api\Data\ProductsliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($productsliderId);

    /**
     * Retrieve Productslider matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Productslider\Api\Data\ProductsliderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Productslider
     * @param \Ktpl\Productslider\Api\Data\ProductsliderInterface $productslider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ktpl\Productslider\Api\Data\ProductsliderInterface $productslider
    );

    /**
     * Delete Productslider by ID
     * @param string $productsliderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productsliderId);

    /**
     * Retrieve Productslider matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ktpl\Productslider\Api\Data\ProductsliderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListNew(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
