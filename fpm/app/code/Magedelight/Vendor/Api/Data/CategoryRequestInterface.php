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

/**
 * @api
 */
interface CategoryRequestInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const REQUEST_ID = 'request_id';

    const VENDOR_ID = 'vendor_id';

    const STORE_ID = 'store_id';

    const CATEGORIES = 'categories';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const STATUS = 'status';

    const STATUS_DESCRIPTION = 'status_description';

    /**#@-*/

    /**
     * Request id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Request id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Vendor id
     *
     * @return int|null
     */
    public function getVendorId();

    /**
     * Set Vendor id
     *
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set Store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Requested Categories
     *
     * @return string
     */
    public function getCategories();

    /**
     * Set Requested Categories
     *
     * @param string $categories
     * @return $this
     */
    public function setCategories($categories);

    /**
     * Request created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set request created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Request updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set request updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Request Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Request Status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Request Status Description
     *
     * @return string
     */
    public function getStatusDescription();

    /**
     * Set Request Status Description
     *
     * @param string $statusDescription
     * @return $this
     */
    public function setStatusDescription($statusDescription);
}
