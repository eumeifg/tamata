<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\VendorOffers;

class VendorOffersGrid extends \Magento\Framework\View\Element\Template
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
                    'vendorOffers' => [
                        'url' => $this->getUrl(
                            'rbcatalog/offer/add',
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
