<?php

namespace CAT\Bnpl\Model;

class Bnpl extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CUSTOM_INVOICE_CODE = 'bnpl';

    /**
     * Payment Method code
     *
     * @var string
     */
    protected $_isOffline = true;
    protected $_code = self::PAYMENT_METHOD_CUSTOM_INVOICE_CODE;
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {


        if(parent::isAvailable($quote)){

            // Customer checks attbitue check
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $BnplHelper = $objectManager->create(\CAT\Bnpl\Helper\Data::class);
            return $BnplHelper->isBnplAvailable();
        }
        else{
            return false;
        }
    }
}
