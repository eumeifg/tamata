<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api;

/**
 * @api
 */
interface CategoryRequestRepositoryInterface
{
    /**
     * Create request
     *
     * @param \Magedelight\Vendor\Api\Data\CategoryRequestInterface $request
     * @return \Magedelight\Vendor\Api\Data\CategoryRequestInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magedelight\Vendor\Api\Data\CategoryRequestInterface $request);

    /**
     * Get info about request by request id
     *
     * @param int $requestId
     * @return \Magedelight\Vendor\Api\Data\CategoryRequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($requestId);

    /**
     * Delete request
     *
     * @param \Magedelight\Vendor\Api\Data\CategoryRequestInterface $request
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magedelight\Vendor\Api\Data\CategoryRequestInterface $request);
}
