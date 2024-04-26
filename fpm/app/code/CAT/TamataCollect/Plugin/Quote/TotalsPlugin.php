<?php

namespace CAT\TamataCollect\Plugin\Quote;


use Magedelight\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use CAT\TamataCollect\Helper\Data as TamataCollectData;

class TotalsPlugin
{   
     const SALES_ORDER_TABLE = 'sales_order';

    /**
     * @var OrderExtensionFactory
     */
    protected $_orderExtensionFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /*
     * @var TamataCollectData
     */
    public $tamataCollectData;

     /**
     * OrderCustomDataGet constructor.
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        ResourceConnection $resourceConnection,
        TamataCollectData           $tamatacollectdata
    ) {
        $this->_orderExtensionFactory = $orderExtensionFactory;
        $this->_resourceConnection = $resourceConnection;
        $this->_connection = $this->_resourceConnection->getConnection();
        $this->tamataCollectData = $tamatacollectdata;
    }

    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        if($this->tamataCollectData->isEnabled()){
            $optionID = $this->tamataCollectData->getOptionId();
            $extensionAttributes = $order->getExtensionAttributes();
            $orderExtension = $extensionAttributes ? $extensionAttributes : $this->_orderExtensionFactory->create();

             $isTamataCollect = 'false';
             /* get shipping address */
            $shipping = $order->getShippingAddress();
            if($shipping){
                if($shipping->getData('addresstype') == $optionID){
                    $isTamataCollect = 'true';
                }
            }

            $orderExtension->setIsTamataCollect($isTamataCollect);
            $order->setExtensionAttributes($extensionAttributes);
        }
        return $order;
    }

}