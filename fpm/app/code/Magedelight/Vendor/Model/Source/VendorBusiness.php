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
namespace Magedelight\Vendor\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VendorBusiness implements OptionSourceInterface
{

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $_vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * VendorBusiness constructor.
     * @param \Magedelight\Vendor\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_vendor = $vendorFactory->create();
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $currentRule = $this->_coreRegistry->registry(
            \Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE
        );

        $websites = ($currentRule && $currentRule->getRuleId()) ? $currentRule->getWebsiteIds() : null;
        $options = [];
        if (!empty($websites)) {
            foreach ($websites as $website) {
                foreach ($this->_vendor->getVendorOptions($website) as $vId => $label) {
                    $options[] = ['value' => $vId, 'label' => $label];
                }
            }
        } else {
            foreach ($this->_vendor->getVendorOptions() as $vId => $label) {
                $options[] = ['value' => $vId, 'label' => $label];
            }
        }
        return $options;
    }
}
