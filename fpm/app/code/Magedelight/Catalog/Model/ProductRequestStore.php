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
class ProductRequestStore extends AbstractModel implements \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
{
    const CACHE_TAG = 'md_vendor_product_request_store';

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
    protected $_eventPrefix = 'md_vendor_product_request_store';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'product_request_store';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\ProductRequestStore::class);
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
     * Get Product Request id
     *
     * @return array
     */
    public function getProductRequestId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::PRODUCT_REQUEST_ID);
    }

    /**
     * set Product Request id
     *
     * @param int $productRequestId
     * @return \Magedelight\Catalog\Api\Data\ProductRequestWebsiteInterface|ProductRequestStore
     */
    public function setProductRequestId($productRequestId)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::PRODUCT_REQUEST_ID,
            $productRequestId
        );
    }

    /**
     * set Product Name
     *
     * @param mixed $name
     * @return \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
     */
    public function setName($name)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::NAME, $name);
    }

    /**
     * get Product Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::NAME);
    }

    /**
     * set Attributes
     *
     * @param mixed $attributes
     * @return \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
     */
    public function setAttributes($attributes)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::ATTRIBUTES, $attributes);
    }

    /**
     * get Attributes
     *
     * @return string
     */
    public function getAttributes()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::ATTRIBUTES);
    }

    /**
     * set Condition Note
     *
     * @param mixed $conditionNote
     * @return \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
     */
    public function setConditionNote($conditionNote)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::CONDITION_NOTE,
            $conditionNote
        );
    }

    /**
     * get Condition Note
     *
     * @return string
     */
    public function getConditionNote()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::CONDITION_NOTE);
    }

    /**
     * set Warranty Description
     *
     * @param mixed $warrantyDescription
     * @return \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
     */
    public function setWarrantyDescription($warrantyDescription)
    {
        return $this->setData(
            \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::WARRANTY_DESCRIPTION,
            $warrantyDescription
        );
    }

    /**
     * get Warranty Description
     *
     * @return string
     */
    public function getWarrantyDescription()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::WARRANTY_DESCRIPTION);
    }

    /**
     * set Store
     *
     * @param mixed $storeId
     * @return \Magedelight\Catalog\Api\Data\ProductRequestStoreInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::STORE_ID, $storeId);
    }

    /**
     * get Store
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(\Magedelight\Catalog\Api\Data\ProductRequestStoreInterface::STORE_ID);
    }
}
