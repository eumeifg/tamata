<?php

namespace MDC\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Plugin;

class Offer
{
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ){
        $this->_coreRegistry = $registry;
    }

    public function afterSetForm(\Magedelight\Catalog\Block\Adminhtml\ProductRequest\Edit\Tab\Offer $offer) {
        $model = $this->_coreRegistry->registry('vendor_product_request');
        $form = $offer->getForm();
        $fieldset = $form->addFieldset('hidden_fieldset', ['legend' => __('')], 'offer_fieldset', '');
        $fieldset->addField('cost_price_iqd', 'hidden', ['name' => 'cost_price_iqd']);
        $fieldset->addField('cost_price_usd', 'hidden', ['name' => 'cost_price_usd']);
        $form->addValues(
            [
                'cost_price_iqd' => $model->getCostPriceIqd(),
                'cost_price_usd' => $model->getCostPriceUsd()
            ]
        );
        return $form;
    }
}
