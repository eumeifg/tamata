<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Microsite\Html;

class VendorProduct extends \Magedelight\Vendor\Block\Microsite\Html\AbstractProduct
{
    const LIMIT  = 5;

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $productIds = array_merge(
            $this->getProductCollectionForSimple()->getAllIds(),
            $this->getProductCollectionForConfig()->getAllIds()
        );
        $collection = $this->_productCollectionFactory->create();
        /* Flat table Compatibility Changes */
        if ($this->flatState->isAvailable()) {
            $collection->addFieldToSelect('*');
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        } else {
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        }
        $this->setCollection($collection);
    }

    /**
     * @return $this|AbstractProduct
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        $toolbar = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'vendor.product.list.pager'
        );
        $toolbar->setCollection($this->getCollection());
        $this->setChild('toolbar', $toolbar);
        $this->getCollection()->load();
        return $this;
    }

    /**
     * @return string
     */
    public function getViewMoreUrl()
    {
        return $this->getUrl('rbvendor/microsite_vendor/product/vid/' . $this->getRequest()->getParam('vid'));
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
}
