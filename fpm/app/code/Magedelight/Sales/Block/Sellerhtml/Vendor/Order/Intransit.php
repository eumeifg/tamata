<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order;

use Magedelight\Sales\Model\Order as VendorOrder;

class Intransit extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getIntransitOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.intransit.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            $pager->setData('pagersfrm', 'intransit');
            if ($sfrm != 'intransit' || !$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            $pager->setLimit($limit)
                ->setCollection(
                    $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getIntransitOrders($vendorId = 0)
    {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-orderintransit']) && $paramsData['session-clear-orderintransit'] == "1") {
            $orders_active_intransit = $grid_session['grid_session']['orders_active_intransit'];
            foreach ($orders_active_intransit as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_active_intransit[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_active_intransit'] = $orders_active_intransit;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) &&
            isset($paramsData['sort_order']) && $paramsData['sfrm'] == 'intransit') {
            foreach ($paramsData as $key => $val) {
                $orders_active_intransit[$key] = $val;
            }
            $grid_session['grid_session']['orders_active_intransit'] = $orders_active_intransit;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter('main_table.status', VendorOrder::STATUS_IN_TRANSIT);
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        if (isset($grid_session['grid_session']['orders_active_intransit']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_intransit']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sort_order']) && isset($paramsData['sfrm'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_intransit'],
                'orders_active_intransit',
                $grid_session['grid_session']['orders_active_intransit']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_active_intransit']['sort_order'];
        $direction = $grid_session['grid_session']['orders_active_intransit']['dir'];
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getDeliveredUrl($order)
    {
        return $this->getUrl('rbsales/order/delivered', ['id' => $order->getId(), 'tab' => $this->getTab()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl(
            'rbsales/order/view',
            ['id' => $order->getRvoVendorOrderId(), 'tab' => $this->_tabs]
        );
    }
}
