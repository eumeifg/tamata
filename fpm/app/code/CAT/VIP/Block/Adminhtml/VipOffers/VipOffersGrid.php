<?php

namespace CAT\VIP\Block\Adminhtml\VipOffers;

class VipOffersGrid extends \Magento\Framework\View\Element\Template
{
    /**
     * Get Add new Attribute button
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddNewOfferButton()
    {
        /** @var \Magento\Backend\Block\Widget\Button $attributeCreate */
        $vendorOfferCreate = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        $vendorOfferCreate->setDataAttribute(
            [
                'mage-init' => [
                    'vipOffers' => [
                        'url' => $this->getUrl(
                            'viporders/offer/add',
                            ['popup' => 1, 'product_id' => $this->_request->getParam('id', null)]
                        ),
                        'mode' => 'add'
                    ],
                ],
            ]
        )->setType(
            'button'
        )->setLabel(
            __('Add New Offer')
        );
        return $vendorOfferCreate->toHtml();
    }
}
