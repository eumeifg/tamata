<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */

namespace Ktpl\CheckoutAccordionExtended\Plugin\Checkout;

class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $jsLayout
    ) {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) 
        {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]);
            }
        }
        
        if (isset($jsLayout['components']['checkout']['children']['sidebar']['children']['shipping-information'])) {
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['shipping-information']);
        }
        return $jsLayout;
    }
}
