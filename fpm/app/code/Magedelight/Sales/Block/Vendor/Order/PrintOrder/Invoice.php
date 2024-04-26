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
namespace Magedelight\Sales\Block\Vendor\Order\PrintOrder;

class Invoice extends \Magento\Sales\Block\Order\PrintOrder\Invoice
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $vendorHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
        $this->vendorHelper = $vendorHelper;
    }

    /**
     *
     * @param integer $vendorId
     * @return \Magedelight\Vendor\Model\Vendor
     */
    public function getVendorDetail($vendorId)
    {
        return $this->vendorHelper->getVendorDetails($vendorId);
    }

    /**
     *
     * @param integer $vendorId
     * @return string
     */
    public function getVendorGstin($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if ($this->vendorHelper->isModuleEnabled('Magedelight_VendorInd') &&
            $this->vendorHelper->getConfigValue('rbvendorind/general_settings/enabled')
        ) {
            if (!empty($vendor)) {
                return $vendor->getGstin();
            }
        }
    }

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @return string
     */
    public function getFullAddress($vendor)
    {
        $address = [];
        if (!empty($vendor)) {
            $address = [
                $vendor->getAddress1(),
                $vendor->getAddress2(),
                $vendor->getCity(),
                $vendor->getRegion(),
                $vendor->getPincode()
            ];
            $address = array_filter($address);
        }
        return implode(', ', $address);
    }
}
