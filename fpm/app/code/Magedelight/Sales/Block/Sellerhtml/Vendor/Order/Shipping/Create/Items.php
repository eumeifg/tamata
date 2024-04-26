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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Shipping\Create;

class Items extends \Magento\Sales\Block\Order\Items
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($context, $registry, $data);
        $this->_coreRegistry = $registry;
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

        /**
         * @param array $filterByTypes
         * @param bool $nonChildrenOnly
         * @return ImportCollection
         */
    public function getItemsCollection()
    {
       // $vendorOrder = $this->getVendorOrder();
        $vendorOrder = $this->_coreRegistry->registry('vendor_order');
        
        return $vendorOrder->getItems();
    }
    /**
     * Format given price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve HTML of update button
     *
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get url for update
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    /**
     * Check possibility to send shipment email
     *
     * @return bool
     */
    public function canSendShipmentEmail()
    {
        return $this->_salesData->canSendNewShipmentEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Checks the possibility of creating shipping label by current carrier
     *
     * @return bool
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->_carrierFactory->create(
            $this->getOrder()->getShippingMethod(true)->getCarrierCode()
        );
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }
    
     /**
      * Return true if can ship partially
      *
      * @param Order|null $order
      * @return bool
      */
    public function canShipPartially($order = null)
    {
        if ($order === null || !$order instanceof Order) {
            $order = $this->_coreRegistry->registry('current_shipment')->getOrder();
        }
        $value = $order->getCanShipPartially();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Return true if can ship items partially
     *
     * @param Order|null $order
     * @return bool
     */
    public function canShipPartiallyItem($order = null)
    {
        if ($order === null || !$order instanceof Order) {
            $order = $this->_coreRegistry->registry('current_shipment')->getOrder();
        }
        $value = $order->getCanShipPartiallyItem();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Check is shipment is regular
     *
     * @return bool
     */
    public function isShipmentRegular()
    {
        if (!$this->canShipPartiallyItem() || !$this->canShipPartially()) {
            return false;
        }
        return true;
    }
}
