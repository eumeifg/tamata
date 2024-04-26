<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\TopCategory\Api;

/**
 * Top Category interface.
 *
 * @api
 * @since 100.0.2
 */
interface TopCategoryRepositoryInterface
{
    /**
     * Retrieve Top Category list.
     * @param int|null $storeId
     * @return \Ktpl\TopCategory\Api\Data\TopCategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTopCategoryList($storeId = null);

}
