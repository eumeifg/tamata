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
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\CategoryRequestInterface;

class CategoryRequest extends \Magento\Framework\Model\AbstractModel implements CategoryRequestInterface
{
    const REQUEST_PREFIX = 'Category-Request-';
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\CategoryRequest::class);
    }

    /**
     * Identifier getter
     *
     * @return int
     */
    public function getId()
    {
        return $this->_getData(CategoryRequestInterface::REQUEST_ID);
    }

    /**
     * Set request Id
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setData(CategoryRequestInterface::REQUEST_ID, $value);
    }

    /**
     * Vendor id
     *
     * @return int|null
     */
    public function getVendorId()
    {
        return $this->_getData(CategoryRequestInterface::VENDOR_ID);
    }

    /**
     * Set Vendor id
     *
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(CategoryRequestInterface::VENDOR_ID, $vendorId);
    }

    /**
     * Store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_getData(CategoryRequestInterface::STORE_ID);
    }

    /**
     * Set Store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(CategoryRequestInterface::STORE_ID, $storeId);
    }

    /**
     * Requested Categories
     *
     * @return string
     */
    public function getCategories()
    {
        return $this->_getData(CategoryRequestInterface::CATEGORIES);
    }

    /**
     * Set Requested Categories
     *
     * @param string $categories
     * @return $this
     */
    public function setCategories($categories)
    {
        return $this->setData(CategoryRequestInterface::CATEGORIES, $categories);
    }

    /**
     * Request created date
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData(CategoryRequestInterface::CREATED_AT);
    }

    /**
     * Set request created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(CategoryRequestInterface::CREATED_AT, $createdAt);
    }

    /**
     * Request updated date
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData(CategoryRequestInterface::UPDATED_AT);
    }

    /**
     * Set request updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(CategoryRequestInterface::UPDATED_AT, $updatedAt);
    }

    /**
     * Request Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_getData(CategoryRequestInterface::STATUS);
    }

    /**
     * Set Request Status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(CategoryRequestInterface::STATUS, $status);
    }

    /**
     * Request Status Description
     *
     * @return string
     */
    public function getStatusDescription()
    {
        return $this->_getData(CategoryRequestInterface::STATUS_DESCRIPTION);
    }

    /**
     * Set Request Status Description
     *
     * @param string $statusDescription
     * @return $this
     */
    public function setStatusDescription($statusDescription)
    {
        return $this->setData(CategoryRequestInterface::STATUS_DESCRIPTION, $statusDescription);
    }
}
