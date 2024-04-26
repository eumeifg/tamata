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
namespace Magedelight\Vendor\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */

interface VendorReviewInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const VENDOR_RATING_ID = 'vendor_rating_id';
    const VENDOR_ID = 'vendor_id';
    const CUSTOMER_ID = 'customer_id';
    const COMMENT = 'comment';
    const VENDOR_ORDER_ID = 'vendor_order_id';
    const IS_SHARED = 'is_shared';
    const SHARED_BY = 'shared_by';
    const SHARED_AT = 'shared_at';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const VENDOR_NAME = 'name';
    const VENDOR_BUSINESS_NAME = 'business_name';
    const RATING_AVG = 'rating_avg';
    const RATING_OPTIONS = 'rating_options';
    const CUSTOMER_NAME = 'customer_name';
    const INCREMENT_ID = 'increment_id';

    /**
     * Get Vendor Rating Id
     *
     * @return int
     */
    public function getVendorRatingId();

    /**
     * Set vendor rating id
     *
     * @param int $id
     * @return $this
     */
    public function setVendorRatingId($id);

    /**
     * Get Vendor ID
     * @return int
     */
    public function getVendorId();

    /**
     * Set Vendor ID
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Get Vendor Id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set Customer ID
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get Comment
     * @return string
     */
    public function getComment();

    /**
     * Set Comment
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Get Vendor Order ID
     * @return int
     */
    public function getVendorOrderId();

    /**
     * Set Comment
     * @param int $vendorOrderId
     * @return $this
     */
    public function setVendorOrderId($vendorOrderId);

    /**
     * Get Is String
     * @return int
     */
    public function getIsShared();

    /**
     * Set Is Shared
     * @param int $isShared
     * @return $this
     */
    public function setIsShared($isShared);

    /**
     * Get Shared By
     * @return int
     */
    public function getSharedBy();

    /**
     * Get Shared By
     * @param int $sharedBy
     * @return $this
     */
    public function setSharedBy($sharedBy);

    /**
     * Get Shared At
     * @return string
     */
    public function getSharedAt();

    /**
     * Set Shared At
     * @param string $sharedDate
     * @return $this
     */
    public function setSharedAt($sharedDate);

    /**
     * Get Store Id
     * @return int
     */
    public function getStoreId();

    /**
     * Get Store Id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Set Created At
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Created At
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $name|null
     * @return $this
     */
    public function setVendorName($name);

    /**
     * @return string $name|null
     */
    public function getVendorName();

    /**
     * @param string $businessName|null
     * @return $this
     */
    public function setBusinessName($businessName);

    /**
     * @return string $businessName|null
     */
    public function getBusinessName();

    /**
     * @param string $customerName|null
     * @return $this
     */
    public function setCustomerName($customerName);

    /**
     * @return string|null
     */
    public function getCustomerName();

    /**
     * @param int $ratingAvg|null
     * @return $this
     */
    public function setRatingAvg($ratingAvg);

    /**
     * @return int $ratingAvg|null
     */
    public function getRatingAvg();

    /**
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * @return string $incrementId
     */
    public function getIncrementId();

    /**
     * @param \Magedelight\Vendor\Api\Data\VendorRatingDataInterface[] $ratingOptions|null
     * @return $this
     */
    public function setRatingOptions($ratingOptions);

    /**
     * @return \Magedelight\Vendor\Api\Data\VendorRatingDataInterface[] $ratingOptions|null
     */
    public function getRatingOptions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\VendorReviewExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorReviewExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorReviewExtensionInterface $extensionAttributes
    );
}
