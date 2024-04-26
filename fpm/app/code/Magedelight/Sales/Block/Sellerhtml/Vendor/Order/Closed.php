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

class Closed extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{
    protected $_template = 'Magedelight_Sales::vendor/order/complete/closed.phtml';

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getClosedOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.closedorder.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            if (!$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            $pager->setLimit($limit)->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        $this->_tabs = '2,2';
        return $this;
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getClosedOrders()
    {
        if (!($vendorId = $this->authSession->getUser()->getVendorId())) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if (isset($paramsData['session-clear-orderclosed']) && $paramsData['session-clear-orderclosed'] == "1") {
            $orders_closed = $grid_session['grid_session']['orders_closed'];
            foreach ($orders_closed as $key => $val) {
                if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                    $orders_closed[$key] = '';
                }
            }
            $grid_session['grid_session']['orders_closed'] = $orders_closed;
            $this->setGridSession($grid_session);
        } elseif (isset($paramsData['sfrm']) && isset($paramsData['sort_order']) && $paramsData['sfrm'] == 'closed') {
            foreach ($paramsData as $key => $val) {
                $orders_closed[$key] = $val;
            }
            $grid_session['grid_session']['orders_closed'] = $orders_closed;
            $this->setGridSession($grid_session);
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection
            ->addFieldToFilter(
                'main_table.status',
                ['in' => [VendorOrder::STATUS_CLOSED]]
            );
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        if (isset($grid_session['grid_session']['orders_closed']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_closed']['q']
            );
        }

        if (!empty($paramsData) && isset($paramsData['sfrm']) && isset($paramsData['sort_order'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_closed'],
                'orders_closed',
                $grid_session['grid_session']['orders_closed']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_closed']['sort_order'];
        $direction = $grid_session['grid_session']['orders_closed']['dir'];
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/order/complete', ['tab' => $this->getTab()]);
    }
}
