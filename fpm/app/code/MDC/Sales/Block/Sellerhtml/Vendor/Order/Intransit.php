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
namespace MDC\Sales\Block\Sellerhtml\Vendor\Order;

use Magedelight\Sales\Model\Order as VendorOrder;

class Intransit extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Intransit
{
	
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
        $includeStatuses = [VendorOrder::STATUS_IN_TRANSIT, VendorOrder::STATUS_OUT_WAREHOUSE];
        $collection->addFieldToFilter('main_table.status', ['in' => $includeStatuses]);
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
}
