<?php

namespace MDC\Catalog\Block\Sellerhtml\Listing\Nonlive;

class Pending extends \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Pending
{
    public function getPendingProductRequests()
    {
        $store = $this->_storeManager->getStore(true);
        $storeId = $this->_storeManager->getStore()->getId();
        $session = $this->authSession;
        $data = $session->getGridSession();
        $search = $this->getRequest()->getParam('vpro', false);
        $status =  0;

        $keytype = "product_nonlive_pending";

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
            ['name', 'attributes']
        );

        $collection->addFieldToFilter('status', 0);
        $website_id = [$store->getWebsiteId()];
        $collection->addFieldToFilter('website_ids', [['finset'=> $website_id]]);

        $gridsession = $session->getGridSession();
        $searchFrom = $this->getRequest()->getParam('sfrm');
        if ($searchQuery && $searchFrom == 'nl') {
            // $str = preg_replace('/[^A-Za-z0-9\-\_\ ]/', '', $searchQuery);
            $str = trim($searchQuery);
            //$collection->getSelect()->columns(['attributes' => new \Zend_Db_Expr('JSON_EXTRACT(attributes, "$.bar_code")')]);
            $collection->getSelect()->where('name like "%' . $str . '%" OR vendor_sku like "%' . $str . '%"');
            $gridsession['grid_session']['product_nonlive_pending']['q'] = $str;
        }
        $session->setGridSession($gridsession);

        $sortOrder = $this->getRequest()->getParam('sort_order', 'main_table.product_request_id');
        $sortDir = $this->getRequest()->getParam('dir', "DESC");
        if ($searchFrom != 'nl') {
            $sortOrder = 'main_table.product_request_id';
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
}
