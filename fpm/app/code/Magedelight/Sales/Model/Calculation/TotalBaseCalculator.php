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
namespace Magedelight\Sales\Model\Calculation;

class TotalBaseCalculator extends \Magento\Tax\Model\Calculation\TotalBaseCalculator
{
    /**
     * Get address rate request
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * Modified to create addressRateRequest everytime tax for an item is being calculated.
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getAddressRateRequest()
    {
        $this->addressRateRequest = $this->calculationTool->getRateRequest(
            $this->shippingAddress,
            $this->billingAddress,
            $this->customerTaxClassId,
            $this->storeId,
            $this->customerId
        );
        return $this->addressRateRequest;
    }
}
