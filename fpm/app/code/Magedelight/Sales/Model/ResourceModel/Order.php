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
namespace Magedelight\Sales\Model\ResourceModel;

use Magedelight\Sales\Api\Data\VendorOrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magedelight\Sales\Model\ResourceModel\Order\Handler\State as StateHandler;

/**
 * @author Rocket Bazaar Core Team
 */
class Order extends AbstractDb
{
    /**
     * @var StateHandler
     */
    protected $stateHandler;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        StateHandler $stateHandler,
        $connectionName = null
    ) {
        $this->stateHandler = $stateHandler;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('md_vendor_order', 'vendor_order_id');
    }
    
    // public function getByOriginOrderId($orderId, $vendorId)
    // {
    //     $connection = $this->getConnection();
        
    //     $select = $connection->select()
    //             ->from(['main_table' => $this->getMainTable()])
    //             ->where('order_id = :order_id')
    //             ->where('vendor_id = :vendor_id')
    //             ;
    //     $select->joinLeft(
    //         'sales_order',
    //         'sales_order.entity_id = main_table.order_id',
    //         [
    //             'customer_firstname as firstname',
    //             'customer_lastname as lastname'
    //         ]
    //     );

    //     $bind = [':order_id' => (int)$orderId, ':vendor_id' => (int)$vendorId];
    //     return $connection->fetchRow($select, $bind);
    // }

    public function getByOriginOrderId($orderId, $vendorId, $vendorOrderId = null)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
                ->from(['main_table' => $this->getMainTable()])
                ->where('order_id = :order_id')
                ->where('vendor_id = :vendor_id')
                ;

        if($vendorOrderId != null) {
            $select->where('vendor_order_id = :vendor_order_id');
        }
        $select->joinLeft(
                'sales_order',
                'sales_order.entity_id = main_table.order_id',
            [
                'customer_firstname as firstname',
                'customer_lastname as lastname'
            ]
        );

        if($vendorOrderId != null) {
            $bind = [':order_id' => (int)$orderId, ':vendor_id' => (int)$vendorId, ':vendor_order_id' => (int)$vendorOrderId];
        }
        else {
             $bind = [':order_id' => (int)$orderId, ':vendor_id' => (int)$vendorId];
        }
        
        return $connection->fetchRow($select, $bind);
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magedelight\Sales\Model\Order $object */
        $this->stateHandler->check($object);
        return parent::save($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object instanceof VendorOrderInterface && empty($object->getIncrementId())) {
            if ($object->getMainOrderIncrementId()) {
                $object->setIncrementId(
                    $object->getMainOrderIncrementId().'-'.$object->getId()
                )->save();
            }
        }
        parent::_afterSave($object);
        return $this;
    }

    /**
     * Return vendor discount amount
     * @param $vendorOrderId
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDiscountAmount($vendorOrderId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from(['main_table' => $this->getMainTable()], 'discount_amount')
            ->where('vendor_order_id = :vendor_order_id')
        ;
        $bind = [':vendor_order_id' => (int)$vendorOrderId];
        return $connection->fetchOne($select, $bind);
    }
}
