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
namespace Magedelight\Sales\Plugin\Order;

use Magedelight\Sales\Model\Order as VendorOrder;

class Config
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    protected $allStatuses;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->state = $state;
        $this->authSession = $authSession;
    }

    public function aroundGetStatusLabel(\Magento\Sales\Model\Order\Config $subject, \Closure $proceed, $code)
    {
        $result = $proceed($code);
        $scope = 'customer';
        $area = $this->state->getAreaCode();
        if ($area == 'adminhtml') {
            $scope = 'admin';
        } elseif ($this->authSession->isLoggedIn() && $this->authSession->getUser()->getVendorId()) {
            $scope = 'vendor';
        }
        $statusLabel = $this->getStatusLabelForScope($scope, $code);
        if ($statusLabel != '' && $statusLabel != null) {
            return $statusLabel;
        }
        return $result;
    }

    /**
     * @param string $scope
     * @param $code
     * @return string
     */
    public function getStatusLabelForScope($scope = 'customer', $code)
    {
        $statuses = $this->getAllStatusLabels();
        if (array_key_exists($code, $statuses[$scope])) {
            return $statuses[$scope][$code];
        }
        return '';
    }

    protected function getAllStatusLabels()
    {
        if (!$this->allStatuses) {
            $this->allStatuses = [
                'vendor'=> [
                    VendorOrder::STATUS_PENDING => __('Upcoming'),
                    VendorOrder::STATUS_CONFIRMED => __('New'),
                    VendorOrder::STATUS_PROCESSING => __('New'),
                    VendorOrder::STATUS_PACKED => __('Packed'),
                    VendorOrder::STATUS_HANDOVER => __('Handover'),
                    VendorOrder::STATUS_SHIPPED => __('Handover'),
                    VendorOrder::STATUS_IN_TRANSIT => __('In Transit'),
                    VendorOrder::STATUS_COMPLETE => __('Delivered'),
                    VendorOrder::STATUS_CLOSED => __('Closed'),
                    VendorOrder::STATUS_CANCELED => __('Canceled')
                ],
                'admin'=> [
                    VendorOrder::STATUS_PENDING => __('New'),
                    VendorOrder::STATUS_CONFIRMED => __('New'),
                    VendorOrder::STATUS_PROCESSING => __('New'),
                    VendorOrder::STATUS_PACKED => __('Packed'),
                    VendorOrder::STATUS_HANDOVER => __('Handover'),
                    VendorOrder::STATUS_SHIPPED => __('Handover'),
                    VendorOrder::STATUS_IN_TRANSIT => __('In Transit'),
                    VendorOrder::STATUS_COMPLETE => __('Delivered'),
                    VendorOrder::STATUS_CLOSED => __('Closed'),
                    VendorOrder::STATUS_CANCELED => __('Canceled')
                ],
                'customer'=>[
                    VendorOrder::STATUS_PENDING => __('Ordered'),
                    VendorOrder::STATUS_CONFIRMED => __('Ordered'),
                    VendorOrder::STATUS_PACKED => __('Processing'),
                    VendorOrder::STATUS_PROCESSING => __('Ordered'),
                    VendorOrder::STATUS_HANDOVER => __('Shipped'),
                    VendorOrder::STATUS_SHIPPED => __('Shipped'),
                    VendorOrder::STATUS_IN_TRANSIT => __('Shipped'),
                    VendorOrder::STATUS_COMPLETE => __('Completed'),
                    VendorOrder::STATUS_CLOSED => __('Closed'),
                    VendorOrder::STATUS_CANCELED => __('Canceled')
                ]
            ];
        }
        return $this->allStatuses;
    }
}
