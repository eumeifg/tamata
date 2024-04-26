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

class Confirmedorder extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{
    /**
     * @var string
     */
    protected $_template = 'Magedelight_Sales::vendor/order/confirmed.phtml';

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getConfirmOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.order.confirmedorder.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'confirmed');
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            if ($sfrm != 'confirmed' || !$limit) {
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
        $this->_tabs = '2,0';
        return $this;
    }

    /**
     * @param object $order
     * @return string
     */
    public function getGenratePackUrl($order)
    {
        return $this->getUrl('rbsales/order/pack', ['id' => $order->getId()]);
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
