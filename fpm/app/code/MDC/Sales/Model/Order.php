<?php 

namespace MDC\Sales\Model;

use Magedelight\Sales\Api\Data\VendorOrderInterface;
use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;


/**
 * 
 */
class Order extends \Magedelight\Sales\Model\Order
{
	  /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magedelight\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $_orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product
     */
    protected $vendorProductResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $mainOrder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /*
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoice;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var PaymentCollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * @var \Magedelight\Sales\Plugin\Order\Config
     */
    protected $statusConfig;

    /**
     * @var CancelledBy
     */
    protected $cancelledBy;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

	function __construct(
		\Magento\Framework\Model\Context $context,
        \Magedelight\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magedelight\Catalog\Model\ResourceModel\Product $vendorProductResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderItemRepositoryInterface $itemRepository,
        PaymentCollectionFactory $paymentCollectionFactory,
        \Magedelight\Sales\Plugin\Order\Config $statusConfig,
        CancelledBy $cancelledBy,
        \Magedelight\Sales\Helper\Data $salesHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoice,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
	)
	{
		 parent::__construct(
            $context,
            $catalogHelper,
            $registry,
            $orderRepository,
            $scopeConfig,
            $orderItemCollectionFactory,
            $priceCurrency,
            $productRepository,
            $orderConfig,
            $vendorProductResource,
            $storeManager,
            $searchCriteriaBuilder,
            $itemRepository,
            $paymentCollectionFactory,
            $statusConfig,
            $cancelledBy,
            $salesHelper,
            $invoice,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

         $this->scopeConfig = $scopeConfig;
	}

	public function canCancel()
    {
    	/* Enable cancel order option after Invoice and Shipment */
        $allRefund = false;
        foreach ($this->getItems() as $item){            
                if ($item->getQtyOrdered() == $item->getQtyRefunded()) {
                    $allRefund = true;
                    break;
                }
            }            
        

        if ($allRefund) {
            return false;
        }

        if ($this->getStatus() === self::STATUS_CANCELED ||
            $this->getStatus() === self::STATUS_CLOSED ||
            //$this->getStatus() === self::STATUS_SHIPPED ||
            $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        return true;
    }

    public function canCreditmemo()
    {
        /* Allowed to create credit memo with Zero amount when order Grand total is zero same as native magento */

        if ($this->getStatus() === self::STATUS_PENDING || $this->getStatus() === self::STATUS_CONFIRMED || $this->getStatus() === self::STATUS_PROCESSING || $this->getStatus() === self::STATUS_CANCELED || $this->getStatus() === self::STATUS_CLOSED) {
            return false;
        }

        $allRefund = false;
        foreach ($this->getItems() as $item){             
                if ($item->getQtyOrdered() == $item->getQtyRefunded()) {
                    $allRefund = true;
                    break;
                }
            }            
        

        if ($allRefund) {
            return false;
        }

        
        /* if (abs($this->_priceCurrency->round($this->getTotalPaid()) - $this->getTotalRefunded()) < .0001) {
            return false;
        }*/

        return true;
    }

    public function registerCancel(\Magento\Sales\Model\Order $order, $orderCancelledAfterInvAndShp = false, $fromCron = false)
    {
         
        $autCreditStockReturnEnabled = $this->scopeConfig->getValue('cataloginventory/item_options/auto_return',ScopeInterface::SCOPE_STORE); 
        
        $skipQtyUpdate = false;

        if($autCreditStockReturnEnabled && $orderCancelledAfterInvAndShp){
            $skipQtyUpdate = true;            
        }
        
        if ($this->canCancel() || $fromCron) {
            $vendorOrderId = $this->getData('vendor_order_id');
            $vendorId = $this->getData('vendor_id');
            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getData('vendor_order_id') == $vendorOrderId) {
                    if (!$fromCron) {
                        $item->cancel();
                    }
                    /* Increase vendor product qty when order canceled */
                    if ($item->getId() && $item->getProduct()) {
                        $connection = $this->vendorProductResource->getConnection();
                        $productTable = $this->vendorProductResource->getMainTable();

                        if(!$skipQtyUpdate){

                            if ($item->getProductType() === "configurable") {
                                foreach ($item->getChildrenItems() as $childItem) {
                                    $connection->query("update {$productTable} SET qty = qty + {$childItem->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$childItem->getProduct()->getId()}");
                                }
                            } else {
                                $connection->query("update {$productTable} SET qty = qty + {$item->getQtyOrdered()} where vendor_id = {$vendorId} AND marketplace_product_id = {$item->getProduct()->getId()}");
                            }
                        }
                        
                    }
                }
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getVendorDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());
            if (!$fromCron) {
                //Update original order:
                $order->setSubtotalCanceled($order->getSubtotalCanceled() + $this->getSubtotalCanceled());
                $order->setBaseSubtotalCanceled($order->getBaseSubtotalCanceled() + $this->getBaseSubtotalCanceled());

                $order->setTaxCanceled($order->getTaxCanceled() + $this->getTaxCanceled());
                $order->setBaseTaxCanceled($order->getBaseTaxCanceled() + $this->getBaseTaxCanceled());

                $order->setShippingCanceled($order->getShippingCanceled() + $this->getShippingCanceled());
                $order->setBaseShippingCanceled($order->getBaseShippingCanceled() + $this->getBaseShippingCanceled());

                $order->setDiscountCanceled($order->getDiscountCanceled() + $this->getDiscountCanceled());
                $order->setBaseDiscountCanceled($order->getBaseDiscountCanceled() + $this->getBaseDiscountCanceled());

                /* --------------- Update main order table canceled amounts with having suborder discount applied */
                if($this->getDiscountAmount() > 0){
                    $discount_amount = $this->getDiscountAmount();
                    $discount_amount = $this->getBaseTotalCanceled();
                }else{
                    foreach ($order->getItemsCollection() as $item) {
                        if ( $this->getVendorOrderId() === $item->getVendorOrderId() ) {
                            $discount_amount = $item->getDiscountAmount();
                            $base_discount_amount = $item->getBaseDiscountAmount();
                        }
                    }
                }

                /* Grand total was not reflected with discount amounts */
                $this_total_canceled = ($this->getGrandTotal() - $this->getTotalPaid() - $discount_amount );
                $this_basetotal_canceled = ($this->getBaseGrandTotal() - $this->getBaseTotalPaid() - $base_discount_amount) ;

                $order->setTotalCanceled($order->getTotalCanceled() + $this_total_canceled);
                $order->setBaseTotalCanceled($order->getBaseTotalCanceled() + $this_basetotal_canceled);
                /* Update main order table canceled amounts with having suborder discount applied ---------------  */

                // $order->setTotalCanceled($order->getTotalCanceled() + $this->getTotalCanceled());
                // $order->setBaseTotalCanceled($order->getBaseTotalCanceled() + $this->getBaseTotalCanceled());
            }
            
            $this->setStatus(self::STATUS_CANCELED);
        /**
         * @todo status history management
         * $this->_setStatusHistory(self::STATUS_CANCELED);
         */
        } else {
            throw new \Exception(__('Order does not allow to be canceled.'));
        }

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function canConfirm($item = '')
    {
        if ($item) {
            if ($item->getParentItem()) {
                $itemStatus = $item->getParentItem()->getStatusId();
            } else {
                $itemStatus = $item->getStatusId();
            }
            if ((strtolower($itemStatus) == \Magento\Sales\Model\Order\Item::STATUS_CANCELED)                
                || $this->getOriginalOrder()->getIsConfirmed() == false
            ) {
                return false;
            }
        }
        return ($this->getData('is_confirmed') == false);
    }
}