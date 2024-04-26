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
namespace Magedelight\Catalog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductStore _getResource()
 * @method \Magedelight\Catalog\Model\ResourceModel\ProductStore getResource()
 */
class ProductStore extends AbstractModel implements \Magedelight\Catalog\Api\Data\ProductStoreInterface
{
    const CACHE_TAG = 'md_vendor_product_store';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'md_vendor_product_store';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'ProductStore';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\ProductStore::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Product Store id
     *
     * @return array
     */
    public function getProductStoreId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::PRODUCTSTORE_ID);
    }

    /**
     * set Product Store id
     *
     * @param int $ProductStoreId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setProductStoreId($ProductStoreId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::PRODUCTSTORE_ID, $ProductStoreId);
    }

    /**
     * set Product Store Data
     *
     * @param mixed $productStoreVendorProductId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setProductStoreVendorProductId($productStoreVendorProductId)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductStoreInterface::PRODUCT_STORE_VENDORPRODUCT_ID,
            $productStoreVendorProductId
        );
    }

    /**
     * get Product Store Data
     *
     * @return string
     */
    public function getProductStoreVendorProductId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::PRODUCT_STORE_VENDORPRODUCT_ID);
    }

    /**
     * set Vendor Product
     *
     * @param mixed $vendorProductId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setVendorProductId($vendorProductId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::VENDOR_PRODUCT_ID, $vendorProductId);
    }

    /**
     * get Vendor Product
     *
     * @return string
     */
    public function getVendorProductId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::VENDOR_PRODUCT_ID);
    }

    /**
     * set Condition Note
     *
     * @param mixed $conditionNote
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setConditionNote($conditionNote)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::CONDITION_NOTE, $conditionNote);
    }

    /**
     * get Condition Note
     *
     * @return string
     */
    public function getConditionNote()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::CONDITION_NOTE);
    }

    /**
     * set Warranty Description
     *
     * @param mixed $warrantyDesciption
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setWarrantyDesciption($warrantyDesciption)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductStoreInterface::WARRANTY_DESCIPTION,
            $warrantyDesciption
        );
    }

    /**
     * get Warranty Description
     *
     * @return string
     */
    public function getWarrantyDesciption()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::WARRANTY_DESCIPTION);
    }

    /**
     * set Store ID
     *
     * @param mixed $storeId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::STORE_ID, $storeId);
    }

    /**
     * get Store ID
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::STORE_ID);
    }

    /**
     * set Website ID
     *
     * @param mixed $websiteId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::WEBSITE_ID, $websiteId);
    }

    /**
     * get Website ID
     *
     * @return string
     */
    public function getWebsiteId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductStoreInterface::WEBSITE_ID);
    }
}
