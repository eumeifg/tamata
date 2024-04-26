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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive;

class Approved extends \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive
{
    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getApprovedProducts());
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getApprovedProducts()
    {
        $session = $this->authSession;
        $data = $session->getGridSession();

        $keytype = "product_nonlive_approved";
        $searchQuery = $this->getRequest()->getParam('q');
        if ($this->getRequest()->getParam('session-clear-product-nonlive') == "1") {
            if ($data) {
                $product_live = $data['grid_session'][$keytype];
                foreach ($product_live as $key => $val) {
                    $product_live[$key] = '';
                }
                $gridsession = $session->getGridSession();
                $gridsession['grid_session'][$keytype] = $product_live;
                $session->setGridSession($gridsession);
            }
        }

        $collection = $this->approvedProducts->getList(
            $this->getVendor()->getVendorId(),
            $this->getCurrentStoreId()
        );

        $defaultSortOrder = 'main_table.vendor_product_id';
        $gridsession = $session->getGridSession();
        $searchFrom = $this->getRequest()->getParam('sfrm');
        if (!empty($searchQuery) && $searchFrom == 'nl') {
            // $str = preg_replace('/[^A-Za-z0-9\-\_\ ]/', '', $searchQuery);
            $str = trim($searchQuery);
            // $collection->getSelect()->where('cpev.value like "%' . $str . '%" OR vendor_sku like "%' . $str . '%"');
            $collection->getSelect()->where('cpev.value like "%' . $str . '%" OR cpev_default.value like "%' . $str . '%" OR vendor_sku like "%' . $str . '%"');
            $gridsession['grid_session']['product_nonlive_approved']['q'] = $str;
        }

        $session->setGridSession($gridsession);

        $sortOrder = $this->getRequest()->getParam('sort_order', $defaultSortOrder);
        $sortDir = $this->getRequest()->getParam('dir', "DESC");
        if ($searchFrom != 'nl') {
            $sortOrder = $defaultSortOrder;
        }

        $collection->setOrder($sortOrder, $sortDir);
        $collection->getSelect()->distinct(true);
        return $collection;
    }

    /**
     * @return $this|\Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'mylisting.approved.result.list.pager'
        );
        $limit = $this->getRequest()->getParam('limit', false);
        $sfrm = $this->getRequest()->getParam('sfrm');
        $pager->setData('pagersfrm', 'nl');
        $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
        $reqParams = $this->getRequest()->getParams();

        if ($sfrm != 'nl' || !$limit) {
            $limit = 10;
            $pager->setPage(1);
        }
        $pager->setLimit($limit)->setCollection($this->getCollection());
        $this->setChild('pager', $pager);

        if (isset($reqParams['p']) && isset($reqParams['sfrm'])) {
            if ($reqParams['sfrm'] == 'l') {
                $this->getCollection()->setCurPage(1);
            }
        }

        $this->getCollection()->load();

        return $this;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $item
     * @param integer $store
     * @param integer $p
     * @param integer $limit
     * @param boolean $isViewURL
     * @return string
     */
    public function getApprovedProductEditUrlWithStore($item, $store, $p = 1, $limit = 10, $isViewURL = false)
    {
        if ($item->getParentId()) {
            $params = [
                'approve' => '1',
                'id' => $item->getVendorProductId(),
                'tab' => '1,0',
                'super' => true,
                'store' => $store,
                'p' => $p,
                'limit' => $limit,
                'vpro' => 'approve',
                'sfrm' => 'nl',
            ];
        } else {
            $params = ['approve' => '1', 'id' => $item->getVendorProductId(), 'tab' => '1,0', 'store' => $store, 'p' => $p, 'limit' => $limit, 'vpro' => 'approve', 'sfrm' => 'nl'];
        }
        if ($isViewURL) {
            $params['view'] = 1;
        }
        return $this->getUrl('rbcatalog/product/approvededit/', $params);
    }
}
