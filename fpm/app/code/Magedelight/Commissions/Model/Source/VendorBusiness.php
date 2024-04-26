<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VendorBusiness implements OptionSourceInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    private $_vendor;

    private $_vendorcommission;

    public function __construct(
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory
    ) {
        $this->_vendor = $vendorFactory->create();
        $this->request = $request;
        $this->_vendorcommission = $vendorcommissionFactory->create();
    }

    public function toOptionArray($websiteId = 1)
    {
        $id = $this->request->getParam('vendor_commission_id');
        if (!$id) {
            $ids = $this->inVendorId($websiteId);
        } else {
            $ids= [];
        }
        $options = [];
        foreach ($this->_vendor->getVendorOptions($websiteId) as $vId => $label) {
            if (in_array($vId, $ids)) {
                continue;
            }
            $options[] = ['value' => $vId, 'label' => $label];
        }
        return $options;
    }
    public function inVendorId($websiteId)
    {
        return $this->_vendorcommission->getCollection()->addFieldToFilter(
            "website_id",
            $websiteId
        )->getColumnValues('vendor_id');
    }
}
