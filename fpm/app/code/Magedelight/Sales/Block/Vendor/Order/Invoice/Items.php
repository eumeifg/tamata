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
namespace Magedelight\Sales\Block\Vendor\Order\Invoice;

class Items extends \Magento\Sales\Block\Order\Invoice\Items
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $vendorHelper;
    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    private $orderItem;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Sales\Model\Order\Item $orderItem,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->vendorHelper = $vendorHelper;
        $this->orderItem = $orderItem;
    }

    public function getVendorDetail($vendorId)
    {
        return $this->vendorHelper->getVendorDetails($vendorId);
    }

    public function getVendorGstin($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if ($this->vendorHelper->isModuleEnabled('RB_VendorInd') &&
            $this->vendorHelper->getConfigValue('rbvendorind/general_settings/enabled')) {
            if (!empty($vendor)) {
                return $vendor->getGstin();
            }
        }
    }

    public function getFullAddress($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if (!empty($vendor)) {
            $vendorAddress = $vendor->getAddress1() . ' ' . $vendor->getAddress2() . '<br/>';
            $vendorAddress .= $vendor->getCity() . ', ' . $vendor->getRegion() . ', ' . $vendor->getPincode();
            return $vendorAddress;
        }
    }
}
