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
namespace Magedelight\Commissions\Helper;

use Magedelight\Commissions\Model\Commission\Payment as CommissionsPayment;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @var CommissionsPayment
     */
    private $commPayment;

    /**
     * @var \Magento\Model\Store\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magedelight\Commissions\Model\Commission\PaymentFactory $commPayment,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->commPayment = $commPayment->create();
        $this->storeManager = $storeManager;
    }

    public function getCommissionRate($product, $vendorId, $catId)
    {
        $precedences = $this->commPayment->getCommissionLevelPrecedences();
        $comm = [];
        foreach ($precedences as $precedence) {
            if (CommissionsPayment::COMMISSION_LEVEL_PRODUCT == $precedence && $product->getId()) {
                $comm['rate'] = $product->getData('md_commission');
                if ($comm['rate']) {
                    $comm['calc_type'] = $product->getData('md_calculation_type');
                    continue;
                }
            }
            $comm = $this->commPayment->getCommissionRateByPrecedence(
                $precedence,
                $this->storeManager->getStore()->getWebsiteId(),
                $vendorId,
                $catId
            );
            if (!empty($comm)) {
                return $comm;
            }
        }
    }
    public function getMarketplaceFeeRate()
    {
        return $this->commPayment->getMarketplaceFeeRate();
    }
    public function getServiceTaxRate()
    {
        return $this->commPayment->getServiceTaxRate();
    }
}
