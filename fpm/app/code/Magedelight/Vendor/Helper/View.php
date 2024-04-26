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
namespace Magedelight\Vendor\Helper;

use Magedelight\Vendor\Api\Data\VendorInterface;

/**
 * Vendor helper for view.
 */
class View extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var VendorMetadataInterface
     */
    protected $_vendorMetadataService;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param VendorMetadataInterface $vendorMetadataService
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Concatenate all vendor name parts into full vendor name.
     *
     * @param VendorInterface $vendorData
     * @return string
     */
    public function getVendorName(VendorInterface $vendorData)
    {
        return $vendorData->getName();
    }
        /**
         * vendor business name.
         *
         * @param VendorInterface $vendorData
         * @return string
         */
    public function getVendorBusinessName(VendorInterface $vendorData)
    {
        return $vendorData->getBusinessName();
    }
    /**
     * vendor business name.
     *
     * @param VendorInterface $vendorData
     * @return string
     */
    public function getVendorFullBusinessName(VendorInterface $vendorData)
    {
        $businessName = $vendorData->getBusinessName();
        $fullBusinessName = $businessName ? $businessName." (".$vendorData->getName().")" : $vendorData->getName();
        if ($vendorData->getWebsiteId()) {
            $website = $this->storeManager->getWebsite($vendorData->getWebsiteId());
            $fullBusinessName .= ($website && $website->getId())?' - '.$website->getName():'';
        }
        
        return $fullBusinessName;
    }
}
