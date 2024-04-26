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
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest;

class Offer extends \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Edit
{
    protected function _prepareLayout()
    {
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            // Setting empty page title if content heading is absent
            $pageTitle = $this->getPageTitle();
            $pageMainTitle->setPageTitle($this->escapeHtml($pageTitle));
        }

        if ($this->getProduct()->getTypeId() == 'configurable') {
            $block = $this->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\Product\Variants::class
            )->setTemplate(
                'Magedelight_Catalog::product/variants.phtml'
            );

            $existingOffersblock = $this->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\Listing\Live::class
            )->setTemplate(
                'Magedelight_Catalog::existing_offers.phtml'
            )->setPId($this->getProduct()->getId());
            $this->setChild('productoffers', $existingOffersblock);
        } else {
            $block = $this->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Offers::class
            )->setTemplate(
                'Magedelight_Catalog::product/offers.phtml'
            );
        }
        $this->setChild('offers', $block);
    }
    public function getPageTitle()
    {
        if ($this->isRequestResubmitted()) {
            return __('Edit Offer to %1', $this->getProduct()->getName());
        }
        return __('Add Offer to %1', $this->getProduct()->getName());
    }
}
