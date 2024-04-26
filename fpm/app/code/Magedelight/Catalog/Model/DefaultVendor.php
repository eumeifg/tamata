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

class DefaultVendor extends \Magedelight\Catalog\Model\Product
{
    protected $_defaultVendor = null;
    protected $_ratingAvg = null;
    protected $_defaultVendorAttributeId = null;
    protected $_eavAttribute;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magedelight\Catalog\Model\ProductWebsiteRepository $productWebsiteRepository
     * @param \Magedelight\Catalog\Model\ProductStoreRepository $productStoreRepository
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendorsFactory $defaultVendorsFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magedelight\Catalog\Model\ProductWebsiteRepository $productWebsiteRepository,
        \Magedelight\Catalog\Model\ProductStoreRepository $productStoreRepository,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $typeConfigurableFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->datetime = $datetime;
        $this->request = $request;
        $this->_defaultVendorIndexersFactory = $defaultVendorsFactory->create();
        $this->_eavAttribute = $eavAttribute;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->productStoreRepository = $productStoreRepository;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $localeDate,
            $datetime,
            $request,
            $defaultVendorsFactory,
            $productRepository,
            $productWebsiteRepository,
            $productStoreRepository,
            $attributeRepository,
            $typeConfigurableFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param $productId
     * @return bool|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultVendor($productId)
    {
        $storeId = $this->getStoreId();
        $attributeTable = $this->getVendorAttributeTable();
        $vendorAttributeId = $this->getDefaultVendorAttributeId();
        $connection = $this->_getResource()->getConnection();
        $ratingAvg = $this->getRatingAvg();

        $select = $connection->select()->from(
            ['main_table' => $attributeTable],
            [
                'value'
            ]
        );

        $select->joinLeft(
            ['dv_rv' => $this->_getResource()->getTable('md_vendor')],
            'main_table.value = dv_rv.vendor_id',
            [
            'vendor_name' => 'dv_rv.name',
            'business_name',
            'vendor_id',
            'rating_avg' => $ratingAvg
            ]
        );

        $select->joinLeft(
            ['rvr' => $this->_getResource()->getTable('md_vendor_rating')],
            '`rvr`.`vendor_id` = `main_table`.`value`',
            []
        );

        $select->joinLeft(
            ['rvrt' => $this->_getResource()->getTable('md_vendor_rating_rating_type')],
            '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
            []
        );

        $select->where('main_table.entity_id = ' . $productId)
                ->where('main_table.attribute_id = ' . $vendorAttributeId)
                ->where('rvr.is_shared = 1')
                ->group('dv_rv.vendor_id');
        $select->order('rating_avg DESC');

        $data = $connection->fetchRow($select);
        if ($data) {
            $varienObject = new \Magento\Framework\DataObject();
            $varienObject->setData($data);
            return $varienObject;
        }
        return false;
    }

    /**
     * @param bool $vendorId
     * @param bool $productId
     * @param bool $addQtyFilter
     * @return bool|Vendor|\Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDefaultVendor($vendorId = false, $productId = false, $addQtyFilter = true)
    {
        if (!$this->_defaultVendor) {
            if ($productId) {
                if ($vendorId) {
                    $collection = $this->getAvailableVendorsForProduct($productId, $vendorId);
                    $this->_defaultVendor = $collection->getFirstItem();
                } else {
                    $this->_defaultVendor = $this->getDefaultVendor($productId);
                }
            }
        }
        return $this->_defaultVendor;
    }

    /**
     * @return int|null
     */
    public function getDefaultVendorAttributeId()
    {
        if (!$this->_defaultVendorAttributeId) {
            $this->_defaultVendorAttributeId = $this->_eavAttribute->getIdByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                \Magedelight\Vendor\Model\Vendor::DEFAULT_VENDOR_ATTRIBUTE
            );
        }
        return $this->_defaultVendorAttributeId;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorAttributeTable()
    {
        return $this->_getResource()->getTable('catalog_product_entity_int');
    }

    /**
     * @return string|null
     */
    protected function getRatingAvg()
    {
        if (!$this->_ratingAvg) {
            $select = "ROUND(SUM(rvrt.rating_avg)/(SELECT count(*) FROM md_vendor_rating WHERE
             (md_vendor_rating.vendor_id = main_table.value) AND (md_vendor_rating.is_shared = 1)))";

            $this->_ratingAvg = $select;
        }
        return $this->_ratingAvg;
    }
}
