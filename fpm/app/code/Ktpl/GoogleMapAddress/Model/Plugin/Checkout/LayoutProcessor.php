<?php

namespace Ktpl\GoogleMapAddress\Model\Plugin\Checkout;

class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        // Loop all payment methods (because billing address is appended to the payments)
        $configuration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
            foreach ($configuration as $paymentGroup => $groupConfig) {
                if (isset($groupConfig['component']) AND $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['latitude'] = [
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'id' => 'latitude',
                        ],
                        'dataScope' => $groupConfig['dataScopePrefix'] . '.latitude',
                        'label' => __('latitude'),
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => [
                            'required-entry' => false,
                            'min_text_length' => 0,
                        ],
                        'sortOrder' => 300,
                        'id' => 'latitude'
                    ];

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['longitude'] = [
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'id' => 'longitude',
                        ],
                        'dataScope' => $groupConfig['dataScopePrefix'] . '.longitude',
                        'label' => __('longitude'),
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => [
                            'required-entry' => false,
                            'min_text_length' => 0,
                        ],
                        'sortOrder' => 300,
                        'id' => 'longitude'
                    ];

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentGroup]['children']['form-fields']['children']['telephone'] = [
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'id' => 'telephone',
                            'tooltip' => [
                                        'description' => __('For delivery questions.'),
                                    ]
                        ],
                        'dataScope' => $groupConfig['dataScopePrefix'] . '.telephone',
                        'label' => __('Telephone'),
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'required' => true,
                        'validation' => [
                            'required-entry' => true,
                            'validate-iraq-number' => 0
                            /*'min_text_length' => 11,
                            'max_text_length' => 15,*/
                        ],
                        'sortOrder' => 300,
                        'id' => 'telephone',
                    ];

                }
            }
            return $jsLayout;
      }
}

