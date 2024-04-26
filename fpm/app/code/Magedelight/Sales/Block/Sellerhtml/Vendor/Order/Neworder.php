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

/**
 * @method getCollection()
 */
class Neworder extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{
     /**
      * @var string
      */
    protected $_template = 'Magedelight_Sales::vendor/order/new/new.phtml';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getNewOrders()) {
            $pager = $this->getLayout()->getBlock(
                'vendor.order.neworder.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'new');
            $pager->setTemplate('Magedelight_Theme::html/pager.phtml');
            if ($sfrm != 'new' || !$limit) {
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
     *
     * @return string
     */
    public function getMassActionUrl()
    {
        return $this->getUrl('rbsales/order/massaction');
    }
}
