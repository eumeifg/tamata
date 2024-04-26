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
namespace Magedelight\Sales\Model\Sales\Service;

/**
 * Description of CreditmemoService
 *
 * @author Rocket Bazaar team
 */
class CreditmemoService extends \Magento\Sales\Model\Service\CreditmemoService
{
    /**
     * rewrited to do validation based on vendor order
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateForRefund(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        if ($creditmemo->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot register an existing credit memo.')
            );
        }
        /*Replaced $creditmemo->getVendorOrder() to $creditmemo->getOrder() because store credit works on Main order level*/

        $baseOrderRefund = $this->priceCurrency->round(
            $creditmemo->getOrder()->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
        );
    
        if ($baseOrderRefund > $this->priceCurrency->round($creditmemo->getOrder()->getBaseTotalPaid())) {
            $baseAvailableRefund = $creditmemo->getOrder()->getBaseTotalPaid()
                - $creditmemo->getOrder()->getBaseTotalRefunded();

            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'The most money available to refund is %1.',
                    $creditmemo->getOrder()->formatBasePrice($baseAvailableRefund)
                )
            );
        }
        return true;
    }
}
