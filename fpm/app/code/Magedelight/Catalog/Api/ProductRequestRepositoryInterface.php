<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api;

/**
 * @api
 */

interface ProductRequestRepositoryInterface
{
    /**
     * Save Product Request.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestInterface $request
     * @return \Magedelight\Catalog\Api\Data\ProductRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductRequestInterface $request);

    /**
     * Retrieve Product Request
     *
     * @param int $requestId
     * @return \Magedelight\Catalog\Api\Data\ProductRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($requestId);

    /**
     * Retrieve Product Requests matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductRequestSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product Request.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductRequestInterface $request
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductRequestInterface $request);

    /**
     * Delete Product Request by ID.
     *
     * @param int $requestId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($requestId);

    /**
     * @param string $ids
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function deleteByIds($ids);
}
