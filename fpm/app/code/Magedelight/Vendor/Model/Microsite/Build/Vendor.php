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
namespace Magedelight\Vendor\Model\Microsite\Build;

use Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magedelight\Vendor\Api\Data\VendorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Vendor\Model\Microsite\Build\Category as CategoryBuilder;

/**
 * Build microsite vendor data
 */
class Vendor extends \Magento\Framework\DataObject
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var VendorInterfaceFactory
     */
    protected $vendorInterfaceFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $helper;

    /**
     * @var Category
     */
    protected $categoryBuilder;

    /**
     * Vendor constructor.
     * @param StoreManagerInterface $storeManager
     * @param VendorCollectionFactory $vendorCollectionFactory
     * @param \Magedelight\Vendor\Api\Data\VendorInterfaceFactory $vendorInterfaceFactory
     * @param \Magedelight\Vendor\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        VendorCollectionFactory $vendorCollectionFactory,
        \Magedelight\Vendor\Api\Data\VendorInterfaceFactory $vendorInterfaceFactory,
        \Magedelight\Vendor\Helper\Data $helper,
        CategoryBuilder $categoryBuilder,
        $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->storeManager = $storeManager;
        $this->vendorInterfaceFactory = $vendorInterfaceFactory;
        $this->helper = $helper;
        $this->categoryBuilder = $categoryBuilder;
        parent::__construct($data);
    }

    /**
     * @param int $vendorId
     * @param int $storeId
     * @return VendorInterface
     * @throws NoSuchEntityException
     */
    public function build($vendorId, $storeId)
    {
        $vendorInterface = $this->vendorInterfaceFactory->create();
        if ($vendorId) {
            $vendor = $this->getVendor($vendorId);
            /* Set vendor data. */
            if ($vendor) {
                $vendor->setLogo($this->helper->getLogoUrl($vendor->getLogo()));
                $this->categoryBuilder->build($vendor, $storeId);
                $vendor->setData('category_ids', []);
                $vendorInterface->setData($vendor->getData());
            }
        }
        return $vendorInterface;
    }

    /**
     * @param $vendorId
     * @return bool
     */
    protected function getVendor($vendorId = null)
    {
        if ($vendorId) {
            $collection = $this->vendorCollectionFactory->create()
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
            $vendor =  $collection->_addWebsiteData($this->getVendorWebsiteFields())
                ->getFirstItem();
            if ($vendor && $vendor->getVendorId()) {
                return $vendor;
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getVendorWebsiteFields()
    {
        return [
            'logo',
            'business_name',
            'city',
            'country_id'
        ];
    }
}
