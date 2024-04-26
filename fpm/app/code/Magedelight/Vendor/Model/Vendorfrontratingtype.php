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

use Magedelight\Vendor\Api\Data\VendorRatingDataInterface;

class Vendorfrontratingtype extends \Magento\Framework\Model\AbstractModel implements VendorRatingDataInterface
{

    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\Vendorfrontratingtype::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(VendorRatingDataInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($id)
    {
        return $this->setData(VendorRatingDataInterface::ENTITY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getVendorRatingId()
    {
        return $this->getData(VendorRatingDataInterface::VENDOR_RATING_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorRatingId($vendorRatingId)
    {
        return $this->setData(VendorRatingDataInterface::VENDOR_RATING_ID, $vendorRatingId);
    }

    /**
     * @inheritdoc
     */
    public function getOptionId()
    {
        return $this->getData(VendorRatingDataInterface::OPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOptionId($optionId)
    {
        return $this->setData(VendorRatingDataInterface::OPTION_ID, $optionId);
    }

    /**
     * @inheritdoc
     */
    public function getRatingValue()
    {
        return $this->getData(VendorRatingDataInterface::RATING_VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setRatingValue($value)
    {
        return $this->setData(VendorRatingDataInterface::RATING_VALUE, $value);
    }

    /**
     * Get Rating Average
     * @return string
     */
    public function getRatingAvg()
    {
        return $this->getData(VendorRatingDataInterface::RATING_AVG);
    }

    /**
     * Set Rating Average
     * @param string $value
     * @return $this
     */
    public function setRatingAvg($value)
    {
        return $this->setData(VendorRatingDataInterface::RATING_AVG, $value);
    }

    /**
     * Get Store Id
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(VendorRatingDataInterface::STORE_ID);
    }

    /**
     * Get Store Id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(VendorRatingDataInterface::STORE_ID, $storeId);
    }

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(VendorRatingDataInterface::CREATED_AT);
    }

    /**
     * Set Created At
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(VendorRatingDataInterface::CREATED_AT, $createdAt);
    }

    /**
     * Get Rating Code
     * @return string
     */
    public function getRatingCode()
    {
        return $this->getData(VendorRatingDataInterface::RATING_CODE);
    }

    /**
     * Set Rating Code
     * @param string $value
     * @return $this
     */
    public function setRatingCode($value)
    {
        return $this->setData(VendorRatingDataInterface::RATING_CODE, $value);
    }
}
