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

use Magento\Catalog\Model\Product\Type;

class Live extends Product
{
    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getLiveProductsCollection());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'mylisting.result.listlive.pager'
        );
        $limit = $this->getRequest()->getParam('limit', false);
        $sfrm = $this->getRequest()->getParam('sfrm');
        $pager->setData('pagersfrm', 'l');
        $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
        $reqParams = $this->getRequest()->getParams();
        if ($sfrm == 'nl' || !$limit) {
            $limit = 10;
            $pager->setPage(1);
        }
        $pager->setLimit($limit)
            ->setCollection($this->getCollection());
        $this->setChild('pager', $pager);

        if (isset($reqParams['p']) && isset($reqParams['sfrm'])) {
            if ($reqParams['sfrm'] == 'nl') {
                $this->getCollection()->setCurPage(1);
            }
        }
        $this->getCollection()->load();

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @return string
     */
    public function getParamUrl()
    {
        return $this->getUrl('rbcatalog/listing/index/', ['tab' => '1,0']);
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrlLive()
    {
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        return $this->getUrl('rbcatalog/listing/livesubmit/', ['_query' => $queryParams]);
    }

    /**
     *
     * @param integer $pId
     * @return type
     */
    public function getProductOffers($pId = null)
    {
        if ($pId) {
            $vendorId = $this->getVendor()->getVendorId();
            $collection = $this->vendorProductFactory->create()->getCollection()
                ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
                ->addFieldToFilter('parent_id', ['eq' => $pId]);

            return $collection;
        }
    }

    /*
     *
     */
    public function getLiveProductsCollection()
    {
        $collection = $this->liveProducts->getList(
            $this->getVendor()->getVendorId(),
            $this->getCurrentStoreId()
        );

        $searchQuery = $this->getRequest()->getParam('q');
        $data = $this->authSession->getGridSession();

        if ($searchQuery && $this->getRequest()->getParam('sfrm') == 'l') {
            // $str = preg_replace('/[^A-Za-z0-9()\-\_\ ]/', '', $searchQuery);
            $str = trim($searchQuery); //'/[^\w\s]+/u'

            $collection->getSelect()->where(
                'cpev.value like "%' . $str . '%" OR cpev_default.value like "%' .
                $str . '%" OR vendor_sku like "%' . $str . '%"'
            );
        }

        if ($this->getRequest()->getParam('session-clear-product-live') == "1") {
            if ($data) {
                $product_live = $data['grid_session']['product_live'];
                foreach ($product_live as $key => $val) {
                    $product_live[$key] = '';
                }

                $gridsession = $this->authSession->getGridSession();
                $gridsession['grid_session']['product_live'] = $product_live;
                $this->authSession->setGridSession($gridsession);
            }
        }

        if ($this->getRequest()->getParam('ostock', 0)) {
            $collection->getSelect()->where('mdvp.qty <= 0');
        }

        $sortOrder = $this->getRequest()->getParam('sort_order', 'mdvp.vendor_product_id');
        if ($this->getRequest()->getParam('sfrm') != 'l') {
            $sortOrder = 'mdvp.vendor_product_id';
        }

        $collection->getSelect()->order($sortOrder . ' ' . $this->getRequest()->getParam('dir', "DESC"));
        return $collection;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $item
     * @param integer $store
     * @param integer $p
     * @param string $sfrm
     * @param integer $limit
     * @param boolean $isViewURL
     * @return string
     */
    public function getEditUrlWithStore(\Magento\Catalog\Model\Product $item, $store, $p = 1, $sfrm = 'l', $limit = 10, $isViewURL = false)
    {
        /* Allow Edit if product is main product & not offer product. $item->getIsOffered() */
        if (!$item->getIsOffered()) {
            $queryParams = [
                'p' => $p,
                'sfrm' => $sfrm,
                'limit' => $limit,
            ];
            $params = ['approve' => '1', 'id' => $item->getVendorProductId(), 'tab' => '1,0', 'store' => $store, '_query' => $queryParams];
            if ($isViewURL) {
                $params['view'] = 1;
            }
            return $url = $this->getUrl(
                'rbcatalog/product/approvededit',
                $params
            );
        }
        return '';
    }
}
