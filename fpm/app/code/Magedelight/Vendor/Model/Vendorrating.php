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

use Magedelight\Vendor\Api\Data\VendorReviewInterface;

class Vendorrating extends \Magento\Framework\Model\AbstractExtensibleModel implements VendorReviewInterface
{
    const STATUS_APPROVED = 1;
    const STATUS_PENDING = 2;
    const STATUS_NOT_APPROVED = 3;

    protected function _construct()
    {
        $this->_init(\Magedelight\Vendor\Model\ResourceModel\Vendorrating::class);
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_NOT_APPROVED => __('Not Approved')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getVendorRatingId()
    {
        return $this->getData(VendorReviewInterface::VENDOR_RATING_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorRatingId($id)
    {
        return $this->setData(VendorReviewInterface::VENDOR_RATING_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getVendorId()
    {
        return $this->getData(VendorReviewInterface::VENDOR_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(VendorReviewInterface::VENDOR_ID, $vendorId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(VendorReviewInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(VendorReviewInterface::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->getData(VendorReviewInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        return $this->setData(VendorReviewInterface::COMMENT, $comment);
    }

    /**
     * @inheritdoc
     */
    public function getVendorOrderId()
    {
        return $this->getData(VendorReviewInterface::VENDOR_ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorOrderId($vendorOrderId)
    {
        return $this->setData(VendorReviewInterface::VENDOR_ORDER_ID, $vendorOrderId);
    }

    /**
     * @inheritdoc
     */
    public function getIsShared()
    {
        return $this->getData(VendorReviewInterface::IS_SHARED);
    }

    /**
     * @inheritdoc
     */
    public function setIsShared($isShared)
    {
        return $this->setData(VendorReviewInterface::IS_SHARED, $isShared);
    }

    /**
     * @inheritdoc
     */
    public function getSharedBy()
    {
        return $this->getData(VendorReviewInterface::SHARED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setSharedBy($sharedBy)
    {
        return $this->setData(VendorReviewInterface::SHARED_BY, $sharedBy);
    }

    /**
     * @inheritdoc
     */
    public function getSharedAt()
    {
        return $this->getData(VendorReviewInterface::SHARED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setSharedAt($sharedDate)
    {
        return $this->setData(VendorReviewInterface::SHARED_AT, $sharedDate);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->getData(VendorReviewInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(VendorReviewInterface::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(VendorReviewInterface::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(VendorReviewInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setVendorName($name)
    {
        return $this->setData(VendorReviewInterface::VENDOR_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getVendorName()
    {
        return $this->getData(VendorReviewInterface::VENDOR_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setBusinessName($businessName)
    {
        return $this->setData(VendorReviewInterface::VENDOR_BUSINESS_NAME, $businessName);
    }

    /**
     * @inheritdoc
     */
    public function getBusinessName()
    {
        return $this->getData(VendorReviewInterface::VENDOR_BUSINESS_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(VendorReviewInterface::CUSTOMER_NAME, $customerName);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerName()
    {
        return $this->getData(VendorReviewInterface::CUSTOMER_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setRatingAvg($ratingAvg)
    {
        return $this->setData(VendorReviewInterface::RATING_AVG, $ratingAvg);
    }

    /**
     * @inheritdoc
     */
    public function getRatingAvg()
    {
        return $this->getData(VendorReviewInterface::RATING_AVG);
    }

    /**
     * @inheritdoc
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(VendorReviewInterface::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritdoc
     */
    public function getIncrementId()
    {
        return $this->getData(VendorReviewInterface::INCREMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRatingOptions($ratingOptions)
    {
        return $this->setData(VendorReviewInterface::RATING_OPTIONS, $ratingOptions);
    }

    /**
     * @inheritdoc
     */
    public function getRatingOptions()
    {
        return $this->getData(VendorReviewInterface::RATING_OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorReviewExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
