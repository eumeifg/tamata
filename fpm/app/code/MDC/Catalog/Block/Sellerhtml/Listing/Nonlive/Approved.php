<?php

namespace MDC\Catalog\Block\Sellerhtml\Listing\Nonlive;

class Approved extends \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Approved
{
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
            $collection->getSelect()->where('cpev.value like "%' . $str . '%" OR cpev_default.value like "%' . $str . '%" OR vendor_sku like "%' . $str . '%" OR cpev_barcode.value  like "%' . $str . '%"');
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
}
