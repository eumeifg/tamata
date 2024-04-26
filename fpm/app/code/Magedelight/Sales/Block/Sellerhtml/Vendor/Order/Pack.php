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

class Pack extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Sales::vendor/order/pack.phtml';

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPackedOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.packed.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'packed');
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            if ($sfrm != 'packed' || !$limit) {
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

    public function getPackedOrders($vendorId = 0, $skipfilterSearch = false)
    {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-orderpack']) && $paramsData['session-clear-orderpack'] == "1") {
            $orders_active_pack = $grid_session['grid_session']['orders_active_pack'];
            foreach ($orders_active_pack as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_active_pack[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_active_pack'] = $orders_active_pack;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) && isset($paramsData['sort_order']) && $paramsData['sfrm'] == 'packed') {
            foreach ($paramsData as $key => $val) {
                $orders_active_pack[$key] = $val;
            }
            $grid_session['grid_session']['orders_active_pack'] = $orders_active_pack;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter(
            'main_table.status',
            ['eq' => VendorOrder::STATUS_PACKED]
        );
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        /* return collection if it is for summary */
        if ($skipfilterSearch) {
            return $collection;
        }

        if (isset($grid_session['grid_session']['orders_active_pack']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_pack']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sort_order']) && isset($paramsData['sfrm'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_active_pack'],
                'orders_active_pack',
                $grid_session['grid_session']['orders_active_pack']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_active_pack']['sort_order'];
        $direction = $grid_session['grid_session']['orders_active_pack']['dir'];

        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /**
     *
     * @return string
     */
    public function getInvoicePrintUrl()
    {
        return $this->getUrl('rbsales/order/print');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getHandoverUrl($order)
    {
        return $this->getUrl('rbsales/order/handover', ['id' => $order->getId()]);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('rbsales/order/view', ['id' => $order->getRvoVendorOrderId(), 'tab' => $this->_tabs]);
    }
}
