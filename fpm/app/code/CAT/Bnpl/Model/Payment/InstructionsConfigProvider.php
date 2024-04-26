<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CAT\Bnpl\Model\Payment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use CAT\Bnpl\Helper\Data as BnplHelper;
use CAT\Bnpl\Model\Bnpl as Offline;

class InstructionsConfigProvider implements ConfigProviderInterface
{
    const PAYMENT_METHOD_CUSTOM_INVOICE_CODE = 'bnpl';
    /**
     * @var string[]
     */
    protected $methodCodes = [
        Offline::PAYMENT_METHOD_CUSTOM_INVOICE_CODE
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;
    protected $bnplHelper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        BnplHelper $bnplHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->bnplHelper = $bnplHelper;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
            }
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        $BnplData = $this->bnplHelper->getCustomerBnplBalance();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $total = (float)$cart->getQuote()->getGrandTotal();


        if(isset($BnplData['availableBalance'])){
            $html = "<span>".__("Available Balance IQD %1",$BnplData['availableBalance'])."</span>";
            if((float)$BnplData['availableBalance'] < $total) {
                $html = $html."<br><span style='color: red;font-size: 13px;'>".__("You've reached the maximum allowance amount, you should pay in advance, and the rest will be installment")."</span>";
            }
            return $html;
        }
    }
}