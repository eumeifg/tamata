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

class Inprogress extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order
{
    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($vendorId = $this->authSession->getUser()->getVendorId())) {
            return false;
        }

        if (!$this->orders) {
            $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'vendor_id',
                $vendorId
            )->addFieldToFilter(
                'rvo.status',
                ['eq' => 'processing']
            )->setOrder(
                'rvo.created_at',
                'desc'
            );

            $this->orders->getSelect()->joinLeft(
                ["rvo" => "md_vendor_order"],
                "main_table.entity_id = rvo.order_id",
                ["vendor_id","rvo.increment_id","rvo.status"]
            );
        }
        return $this->orders;
    }

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
                'vendor.order.inprogressorder.pager'
            );
            $limit = $this->getRequest()->getParam('limit', false);
            if (!$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            $pager->setLimit($limit)->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }
}
