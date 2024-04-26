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

class Cancelorder extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.canceledorder.pager'
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
        $this->_tabs = '2,1';
        return $this;
    }

    /**
     * @param int $vendorId
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders($vendorId = 0)
    {
        if ($vendorId == 0) {
            $vendorId = $this->authSession->getUser()->getVendorId();
        }
        if (!($vendorId)) {
            return false;
        }
        $paramsData = $this->getRequest()->getParams();
        $grid_session = $this->getGridSession();

        if ($grid_session) {
            if (isset($paramsData['session-clear-ordercancel']) && $paramsData['session-clear-ordercancel'] == "1") {
                $orders_cancel = $grid_session['grid_session']['orders_cancel'];
                foreach ($orders_cancel as $key => $val) {
                    if (!in_array($key, ['sfrm', 'sort_order', 'dir'])) {
                        $orders_cancel[$key] = '';
                    }
                }
                $grid_session['grid_session']['orders_cancel'] = $orders_cancel;
                $this->setGridSession($grid_session);
            } elseif (isset($paramsData['sfrm']) && isset($paramsData['sort_order']) &&
                $paramsData['sfrm'] == 'cancelled') {
                foreach ($paramsData as $key => $val) {
                    $orders_cancel[$key] = $val;
                }
                $grid_session['grid_session']['orders_cancel'] = $orders_cancel;
                $this->setGridSession($grid_session);
            }
        }
        $grid_session = $this->getGridSession();
        $collection = $this->subOrdersListing->getSubOrdersCollection($vendorId);
        $collection->addFieldToFilter(
            'main_table.status',
            ['eq' => VendorOrder::STATUS_CANCELED]
        );
        $this->subOrdersListing->joinVendorCommissionPaymentTable($collection);
        $this->subOrdersListing->joinAddressColumnsToCollection($collection);

        if (isset($grid_session['grid_session']['orders_cancel']['q'])) {
            $this->subOrdersListing->addSearchFilterToCollection(
                $collection,
                $grid_session['grid_session']['orders_cancel']['q']
            );
        }

        if (isset($paramsData['sfrm']) && isset($paramsData['sort_order'])) {
            $this->_addFiltersToCollection(
                $collection,
                $grid_session['grid_session']['orders_cancel'],
                'orders_cancel',
                $grid_session['grid_session']['orders_cancel']
            );
        }

        $sortOrder = $grid_session['grid_session']['orders_cancel']['sort_order'];
        $direction = $grid_session['grid_session']['orders_cancel']['dir'];

        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $this->setCollection($collection);

        return $collection;
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/order/cancelgrid', ['tab' => $this->getTab()]);
    }
}
