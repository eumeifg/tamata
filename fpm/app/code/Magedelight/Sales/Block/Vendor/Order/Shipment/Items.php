<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Vendor\Order\Shipment;

class Items extends \Magento\Shipping\Block\Items
{
    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    private $vendorFactory;
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $vendorHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->vendorFactory = $vendorFactory->create();
        $this->vendorHelper = $vendorHelper;
    }

    public function getVendorDetail($vendorId)
    {
        $vendor = $this->vendorFactory->getCollection()->addFieldToFilter('vendor_id', $vendorId)->getFirstItem();
        return $vendor;
    }

    public function getFullAddress($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if (!empty($vendor)) {
            return $vendor->getAddress1() . ' ' . $vendor->getAddress2() . ' ' . $vendor->getCity() . ' '
                . $vendor->getRegion() . ' ' . $vendor->getPincode();
        }
    }
}
