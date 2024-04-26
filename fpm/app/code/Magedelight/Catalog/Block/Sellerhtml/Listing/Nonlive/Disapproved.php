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

class Disapproved extends \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCollection($this->getDisapprovedProductRequests());
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisapprovedProductRequests()
    {
        $store = $this->_storeManager->getStore(true);
        $storeId = $this->_storeManager->getStore()->getId();
        $session = $this->authSession;
        $data = $session->getGridSession();
        $search = $this->getRequest()->getParam('vpro', false);
        $status =  0;

        $keytype = "product_nonlive_disaaproved";
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

        $collection = $this->productRequestFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', $this->getVendor()->getVendorId());
        $collection->getSelect()->join(
            ['mdvprw' => 'md_vendor_product_request_website'],
            'mdvprw.product_request_id = main_table.product_request_id AND mdvprw.website_id = '
            . $this->_storeManager->getStore()->getWebsiteId(),
            ['price','special_price',  'special_from_date', 'special_to_date']
        );
        $collection->getSelect()->join(
            ['mdvprs' => 'md_vendor_product_request_store'],
            'mdvprs.product_request_id = main_table.product_request_id AND mdvprs.store_id = ' . $storeId,
            ['name']
        );
        $collection->addFieldToFilter('status', 2);
        $website_id = [$store->getWebsiteId()];
        $collection->addFieldToFilter(
            'website_ids',
            [
                ['finset'=> $website_id]
            ]
        );
        $defaultSortOrder = 'product_request_id';

        $gridsession = $session->getGridSession();
        $searchFrom = $this->getRequest()->getParam('sfrm');
        if ($searchQuery && $searchFrom == 'nl') {
            // $str = preg_replace('/[^A-Za-z0-9\-\_\ ]/', '', $searchQuery);
            $str = trim($searchQuery);
            $collection->getSelect()->where('name like "%' . $str . '%" OR vendor_sku like "%' . $str . '%"');
            $gridsession['grid_session']['product_nonlive_disaaproved']['q'] = $str;
        }

        $session->setGridSession($gridsession);

        $sortOrder = $this->getRequest()->getParam('sort_order', $defaultSortOrder);
        $sortDir = $this->getRequest()->getParam('dir', "DESC");
        if ($searchFrom != 'nl') {
            $sortOrder = $defaultSortOrder;
        }

        $collection->setOrder($sortOrder, $sortDir);
        /* Exclude child products. */
        $collection->getSelect()->where(
            'main_table.product_request_id NOT IN (SELECT product_request_id from md_vendor_product_request_super_link)'
        );
        /* Exclude child products. */
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
            'mylisting.disapproved.result.list.pager'
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
     * @return string
     */
    public function getAjaxReasonUrl()
    {
        return $this->getUrl('rbcatalog/listing/ajaxnonlive/', ['tab' => '1,0']);
    }
}
