<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Controller\Adminhtml\Offers;

use Magedelight\Catalog\Api\ProductStoreRepositoryInterface;
use Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface;
use Magedelight\Catalog\Model\Product;

abstract class AbstractSave extends \Magedelight\OffersImportExport\Controller\Adminhtml\Offers
{
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;
    const CORE_PRODUCT_TYPE_DEFAULT = 'simple';
    const CORE_PRODUCT_TYPE_ASSOCIATED = 'config-simple';

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var \Magedelight\Vendor\Api\VendorRepositoryInterface
     */
    protected $vendorRepositoryInterface;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var ProductWebsiteRepositoryInterface
     */
    protected $productWebsiteRepository;

    /**
     * @var ProductStoreRepositoryInterface
     */
    protected $productStoreRepository;

    /**
     * @var \Magedelight\OffersImportExport\Model\Offers\Validator
     */
    protected $offersValidator;

    /**
     * @var \Magedelight\OffersImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var Product\Copier
     */
    protected $vendorProductCopier;

    /**
     * @var \Magedelight\Catalog\Model\ProductStoreFactory
     */
    protected $vendorProductStoreFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductWebsiteFactory
     */
    protected $vendorProductWebsiteFactory;

    /**
     * AbstractSave constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\OffersImportExport\Helper\Data $helper
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Directory\Model\CountryFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Product\Copier $vendorProductCopier
     * @param \Magedelight\Catalog\Model\ProductStoreFactory $vendorProductStoreFactory
     * @param \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorProductWebsiteFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param ProductStoreRepositoryInterface $productStoreRepository
     * @param \Magedelight\OffersImportExport\Model\Offers\Validator $offersValidator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\OffersImportExport\Helper\Data $helper,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Directory\Model\CountryFactory $countryCollectionFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Model\Product\Copier $vendorProductCopier,
        \Magedelight\Catalog\Model\ProductStoreFactory $vendorProductStoreFactory,
        \Magedelight\Catalog\Model\ProductWebsiteFactory $vendorProductWebsiteFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\Math\Random $mathRandom,
        ProductWebsiteRepositoryInterface $productWebsiteRepository,
        ProductStoreRepositoryInterface $productStoreRepository,
        \Magedelight\OffersImportExport\Model\Offers\Validator $offersValidator
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->helper = $helper;
        $this->regionFactory = $regionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->vendorRepositoryInterface = $vendorRepositoryInterface;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->vendorProductCopier = $vendorProductCopier;
        $this->vendorProductStoreFactory = $vendorProductStoreFactory;
        $this->vendorProductWebsiteFactory = $vendorProductWebsiteFactory;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->mathRandom = $mathRandom;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->productStoreRepository = $productStoreRepository;
        $this->offersValidator = $offersValidator;
        parent::__construct($context);
    }

    /**
     * @param $vendorProductWebsite
     * @param $websiteDataArray
     */
    protected function setWebsiteDetails(
        $vendorProductWebsite,
        $websiteDataArray
    ) {
        foreach ($this->getVendorOfferWebsiteFields() as $field) {
            if (array_key_exists($field, $websiteDataArray)) {
                if (in_array($field, ['price','special_price']) &&
                    ($websiteDataArray[$field] && (int)$websiteDataArray[$field] === 0)
                ) {
                    $vendorProductWebsite->setData($field, $websiteDataArray[$field]);
                } elseif (!empty($websiteDataArray[$field])) {
                    $vendorProductWebsite->setData($field, $websiteDataArray[$field]);
                } elseif (in_array($field, ['status','category_id']) && ($websiteDataArray[$field] != '' || $websiteDataArray[$field] == 0)) {
					$vendorProductWebsite->setData($field, $websiteDataArray[$field]);
				} elseif (!$websiteDataArray[$field]) {
                    $vendorProductWebsite->setData($field, null);
                }
            }
        }
    }

    /**
     *
     * @param $row
     * @param $predefinedHeaders
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getImportRow($row, $predefinedHeaders)
    {
        $data =[];
        /* strip whitespace from the beginning and end of each row */
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        $coreProduct = $this->productRepository->get($row['marketplace_sku']);
        $data['marketplace_product_id'] = $coreProduct->getId();
        $data['name'] = $coreProduct->getName();
        $data['type_id'] = $coreProduct->getTypeId();
        $data['attribute_set_id'] = $coreProduct->getAttributeSetId();
        $coreProduct->unsetData();

        /* Format special_from_date */
        $special_from_date = $this->_formateDateValue($row['special_from_date']);
        $row['special_from_date'] = ($special_from_date) ? $special_from_date : null;

        /* Format special_to_date */
        $special_to_date = $this->_formateDateValue($row['special_to_date']);
        $row['special_to_date'] = ($special_to_date) ? $special_to_date : null;
        $fields = $predefinedHeaders;
        unset($row['marketplace_sku']);
        foreach ($fields as $field) {
            if (array_key_exists($field, $row)) {
                $data[$field] = $row[$field];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getVendorOfferWebsiteFields()
    {
        return[
            'status',
            'condition',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'reorder_level',
            'warranty_type',
            'category_id'
        ];
    }

    /**
     * Parse and validate date
     * Return false if value is not decimal or is not positive
     *
     * @param string $date
     * @return bool|float
     * @throws \Exception
     */
    protected function _formateDateValue($date = '')
    {
        if ($date) {
            $newDate = strtr($date, '/', '-');
            $convertdate = (new \DateTime())->setTimestamp(strtotime($newDate));
            $d = $convertdate->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
            return $d;
        }
        return false;
    }

}
