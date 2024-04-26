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

/**
 * @author Rocket Bazaar Core Team
 */
class VendorProductAll extends \Magedelight\Vendor\Block\Microsite\Html\AbstractProduct
{
    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        if ($this->getRequest()->getParam('catId')) {
            $collection->addCategoryFilter($this->categoryRepository->get($this->getRequest()->getParam('catId')));
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
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'vendor.product.list.all.pager'
        );
        $limit = $this->getRequest()->getParam('limit', false);
        if (!$limit) {
            $limit = 8;
        }
        $pager->setCollection($this->getCollection());
        $this->setChild('toolbar', $pager);
        $this->getCollection()->load();
        return $this;
    }

    /**
     *
     * @param integer $productId
     * @return float
     * get vendor product price
     * @throws \Exception
     */
    public function getVendorProductPrice($productId = '')
    {
        $vendorId = $this->getRequest()->getParam('vid');
        $vendorProductPrice = $this->vendorHelperData->getVendorFinalPrice($vendorId, $productId);
        return $this->priceHelper->currency($vendorProductPrice, true, false);
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }
}
