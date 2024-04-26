<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CAT\ProductAssign\Block\Adminhtml\Category\AddProduct;

/**
 * @api
 * @since 100.0.2
 */
class Tabs extends \Magento\VisualMerchandiser\Block\Adminhtml\Category\AddProduct\Tabs
{
    /**
     * Prepare Layout Content
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareLayout()
    {
       $this->setDestElementId($this->getData('dest_element_id'));

        $this->addTab(
            'name_tab',
            [
                'label' => __('Search All Products'),
                'content' => $this->getChildHtml('catalog.category.add.product.tabs.nametab'),
                'active' => true
            ]
        );
        $this->addTab(
            'sku_tab',
            [
                'label' => __('Add Product by SKU'),
                'content' => $this->getChildHtml('catalog.category.add.product.tabs.skutab'),
                'active' => false
            ]
        );
        $this->addTab(
            'parent_sku_tab',
            [
                'label' => __('Add Product by Parent Sku'),
                'content' => $this->getChildHtml('catalog.category.add.product.tabs.productskutab'),
                'active' => false
            ]
        );
        return parent::_prepareLayout();
    }
}
