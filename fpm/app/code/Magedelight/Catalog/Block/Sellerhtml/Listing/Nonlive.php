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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing;

class Nonlive extends Product
{

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @param string $vpro
     * @return string
     */
    public function getParamUrl($vpro = 'approve')
    {
        return $this->getUrl('rbcatalog/listing/index/') . "tab/1,0/vpro/{$vpro}/sfrm/nl/";
    }

    /**
     *
     * @param integer $id
     * @return boolean
     */
    public function checkIsChild($id)
    {
        $vendorProduct = $this->productRequestFactory->create()->load($id);
        if ($vendorProduct->getHasVariants()) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param object $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariants($item)
    {
        $html = '';
        $variantsData = $this->getVariantsData($item);
        if ($variantsData) {
            foreach ($variantsData as $_item) {
                $html .= '<dl class="item-options">';
                if (is_array($this->getVariantColumns($item))) {
                    foreach ($this->getVariantColumns($item) as $label) {
                        $html .= '<dt>' . ucfirst($label) . '</dt>';
                        $html .= '<dd>' . $this->renderField($label, $_item[$label]) . '</dd>';
                    }
                    $html .= '<dt>' . __('Qty') . '</dt>';
                    $html .= '<dd>' . $_item['qty'] . '</dd>';
                    $html .= '<dt>' . __('Price') . '</dt>';
                    $html .= '<dd>' . $this->catalogHelper->currency($_item['price']) . '</dd>';
                    if ($_item['special_price']) {
                        $html .= '<dt>' . __('Special Price') . '</dt>';
                        $html .= '<dd>' . $this->catalogHelper->currency($_item['special_price']) . '</dd>';
                    }
                }
                $html .= '</dl>';
            }
        }
        return $html;
    }

    /**
     *
     * @param type $item
     * @return array|Nonlive
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVariantsData($item)
    {
        $collection = $this->productRequestFactory->create()->getCollection();
        $collection->getSelect()->join(
            ['mvprw' => 'md_vendor_product_request_website'],
            'mvprw.product_request_id = main_table.product_request_id AND mvprw.website_id = '
            . $this->_storeManager->getStore()->getWebsiteId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprs' => 'md_vendor_product_request_store'],
            'mvprs.product_request_id = main_table.product_request_id AND mvprs.store_id = '
            . $this->_storeManager->getStore()->getId(),
            ['*']
        );
        $collection->getSelect()->join(
            ['mvprsl' => 'md_vendor_product_request_super_link'],
            'mvprsl.product_request_id = main_table.product_request_id AND mvprsl.parent_id = '
            . $item->getProductRequestId(),
            ['parent_id']
        );
        if ($collection && $collection->getSize() > 0) {
            $finalData = [];
            foreach ($collection as $variant) {
                $options = $this->serializer->unserialize($variant->getAttributes());
                $variantData = $variant->getData();
                foreach ($options as $index => $value) {
                    $variantData[$index] = $options[$index];
                }
                $finalData[] = $variantData;
            }
            return $finalData;
        }
        return $this;
    }

    /**
     *
     * @param $item
     * @return string
     */
    public function getVariantColumns($item)
    {
        return $this->getUsedProductAttributeIds($item);
    }

    /**
     * @param type $item
     * @return string
     */
    public function getUsedProductAttributeIds($item)
    {
        $usedAttributeIds = $item->getUsedProductAttributeIds();
        if ($usedAttributeIds) {
            return $this->serializer->unserialize($usedAttributeIds);
        }
        return '';
    }

    /**
     *
     * @param \Magedelight\Catalog\Model\ProductRequest $item
     * @param boolean $isViewURL
     * @return string
     */
    public function getResumitUrl($item, $isViewURL = false)
    {
        if (!($item->getMarketplaceProductId() === null) &&
            $item->getMarketplaceProductId() != '' &&
            !$item->getIsRequestedForEdit()) {
            $params = ['id' => $item->getId(), 'tab' => '1,2'];
            if ($isViewURL) {
                $params['view'] = 1;
            }
            return $this->getUrl('rbcatalog/product/offerEdit', $params);
        } else {
            $params = ['id' => $item->getId(), 'tab' => '1,0'];
            if ($isViewURL) {
                $params['view'] = 1;
            }
            // non approved product edit
            return $this->getUrl('rbcatalog/product/edit', $params);
        }
    }

    /**
     *
     * @param $item
     * @param integer $store
     * @param integer $p
     * @param string $sfrm
     * @param integer $limit
     * @return string
     */
    public function getResumitUrlWithStore($item, $store, $p = 1, $sfrm = 'l', $limit = 10)
    {
        $queryParams = [
            'p' => $p,
            'sfrm' => $sfrm,
            'limit' => $limit,
        ];
        if (!($item->getMarketplaceProductId() === null) &&
            $item->getMarketplaceProductId() != '' &&
            !$item->getIsRequestedForEdit()) {
            return $this->getUrl(
                'rbcatalog/product/offerEdit',
                ['id' => $item->getId(), 'tab' => '1,2', 'store' => $store, '_query' => $queryParams]
            );
        } elseif ($item->getIsRequestedForEdit()) {
            //Approved product edit request resubmit
            return $this->getUrl(
                'rbcatalog/product/edit',
                ['id' => $item->getId(), 'tab' => '1,0', 'store' => $store, '_query' => $queryParams]
            );
        } else {
            // non approved product edit
            return $this->getUrl(
                'rbcatalog/product/edit',
                ['id' => $item->getId(), 'tab' => '1,0', 'store' => $store, '_query' => $queryParams]
            );
        }
    }

    /**
     *
     * @param $item
     * @param integer $store
     * @param integer $p
     * @param string $sfrm
     * @param integer $limit
     * @param boolean $isViewURL
     * @return string
     */
    public function getEditUrlWithStore($item, $store, $p = 1, $sfrm = 'l', $limit = 10, $isViewURL = false)
    {
        $queryParams = [
            'p' => $p,
            'sfrm' => $sfrm,
            'limit' => $limit,
        ];
        $params = ['id' => $item->getId(), 'tab' => '1,0', 'store' => $store, '_query' => $queryParams];
        if ($isViewURL) {
            $params['view'] = 1;
        }
        return $this->getUrl(
            'rbcatalog/product/edit/',
            $params
        );
    }
}
