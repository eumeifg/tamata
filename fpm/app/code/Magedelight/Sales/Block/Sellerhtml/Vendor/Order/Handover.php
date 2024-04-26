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

class Handover extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Sales::vendor/order/handover.phtml';

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getHandoverOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.handoverorder.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'handover');
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            if ($sfrm != 'handover' || !$limit) {
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
     * @param $vendorOrder
     * @return string
     */
    public function getManifestUrl($vendorOrder)
    {
        return $this->getUrl('rbsales/order/manifest', ['vendor_order_id' => $vendorOrder->getVendorOrderId()]);
    }

    /**
     * @param array $includeStatuses
     * @param bool $skipfilterSearch
     * @param int $vendorId
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getHandoverOrders(
        $includeStatuses = [VendorOrder::STATUS_SHIPPED],
        $skipfilterSearch = false,
        $vendorId = 0
    ) {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-orderhandover']) && $paramsData['session-clear-orderhandover'] == "1") {
            $orders_active_handover = $grid_session['grid_session']['orders_active_handover'];
            foreach ($orders_active_handover as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_active_handover[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_active_handover'] = $orders_active_handover;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) && isset($paramsData['sort_order']) && $paramsData['sfrm'] == 'handover') {
            foreach ($paramsData as $key => $val) {
                $orders_active_handover[$key] = $val;
            }
            $grid_session['grid_session']['orders_active_handover'] = $orders_active_handover;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter(
            'main_table.status',
            ['in' => $includeStatuses]
        );
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        /* return collection if it is for summary */
        if ($skipfilterSearch) {
            return $collection;
        }

        if (isset($grid_session['grid_session']['orders_active_handover']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_handover']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sort_order'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_handover'],
                'orders_active_handover',
                $grid_session['grid_session']['orders_active_handover']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_active_handover']['sort_order'];
        $direction = $grid_session['grid_session']['orders_active_handover']['dir'];

        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getIntransitUrl($order)
    {
        return $this->getUrl('rbsales/order/intransit', ['id' => $order->getId(), 'tab' => $this->getTab()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('rbsales/order/view', ['id' => $order->getRvoVendorOrderId(), 'tab' => $this->getTab()]);
    }
}
