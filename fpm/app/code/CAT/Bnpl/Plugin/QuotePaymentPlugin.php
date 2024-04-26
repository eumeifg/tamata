<?php

namespace CAT\Bnpl\Plugin;

class QuotePaymentPlugin
{
    /**
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param array $data
     * @return array
     */
    public function beforeImportData(\Magento\Quote\Model\Quote\Payment $subject, array $data)
    {
        var_dump($data);
        die;
        if (array_key_exists('additional_information', $data)) {
            $subject->setAdditionalInformation($data['additional_information']);
        }

        return [$data];
    }
}