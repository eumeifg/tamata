<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Model\Rule\Condition;

use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Payment\Model\Config\Source\Allmethods as SourceAllmethods;
use Magento\Rule\Model\Condition\Context;
use Magento\Shipping\Model\Config\Source\Allmethods;
use Magedelight\VendorPromotion\Helper\Data as HelperData;

class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * @var HelperData
     */
    protected $_promoHlp;

    /**
     *
     * @param Context $context
     * @param Country $directoryCountry
     * @param Allregion $directoryAllregion
     * @param Allmethods $shippingAllmethods
     * @param SourceAllmethods $paymentAllmethods
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Country $directoryCountry,
        Allregion $directoryAllregion,
        Allmethods $shippingAllmethods,
        SourceAllmethods $paymentAllmethods,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_promoHlp = $helperData;

        parent::__construct($context, $directoryCountry, $directoryAllregion, $shippingAllmethods, $paymentAllmethods, $data);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        $attr = $this->getAttribute();
        $ruleVid = $this->getRule()->getVendorId();
        if (in_array($attr, ['base_subtotal', 'weight', 'total_qty']) && $ruleVid) {
            $origTotal = $address->getData($attr);
            $vendorTotal = $this->_promoHlp->getQuoteAddrTotal($address, $attr, $ruleVid);
            $address->setData($attr, $vendorTotal);
        }

        $valResult = parent::validate($address);

        if (in_array($attr, ['base_subtotal', 'weight', 'total_qty']) && $ruleVid && isset($origTotal)) {
            $address->setData($attr, $origTotal);
        }

        return $valResult;
    }
}
