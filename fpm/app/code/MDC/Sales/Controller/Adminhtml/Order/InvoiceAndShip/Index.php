<?php

namespace MDC\Sales\Controller\Adminhtml\Order\InvoiceAndShip;

use Magento\Backend\App\Action;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

class Index extends \Magento\Backend\App\Action
{
	 
   
    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
    */
    protected $vendorOrderRepository;

    /**
     * @var \MDC\Sales\Order\Invoice\Save
    */
    protected $invoiceSaveModel;
	
	 /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor
     */
    protected $discountProcessor;

    /**
     * @var \Magedelight\Sales\Model\Sales\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magedelight\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Framework\Registry
    */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
    */
    protected $logger;
	

	public function __construct(
		Action\Context $context,                
      	\Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magedelight\Sales\Model\Order\SplitOrder\DiscountProcessor $discountProcessor,
        \Magedelight\Sales\Model\Sales\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
     	\Magedelight\Sales\Helper\Data $salesHelper,
     	\Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
     	ShipmentSender $shipmentSender,
     	\Magento\Framework\Registry $registry, 
        \Psr\Log\LoggerInterface $logger
	)
	{		        
        $this->vendorOrderRepository = $vendorOrderRepository;
		$this->orderRepository = $orderRepository;
		$this->discountProcessor = $discountProcessor;
		$this->invoiceService = $invoiceService;
		$this->transaction = $transaction;
		$this->salesHelper = $salesHelper;		
		$this->shipmentLoader = $shipmentLoader;		
		$this->shipmentSender = $shipmentSender;	
		$this->registry = $registry;	
     	$this->logger = $logger;      
        parent::__construct($context);
	}

	public function execute(){

	 try {
		$resultRedirect = $this->resultRedirectFactory->create();

		 $vendorOrderIds = [];
		 if ($this->getRequest()->isAjax()) {

		 	$vendorOrderId = $this->getRequest()->getPost('vendor_order_id');
		 	$vendorOrderIds = $vendorOrderId;
		 }else{
		 	$vendorOrderId = $this->getRequest()->getParam('vendor_order_id');
			$vendorOrderIds[] = $vendorOrderId;
		 }

		$orderId = $this->getRequest()->getParam('order_id');
		$vendorId = $this->getRequest()->getParam('do_as_vendor');
		
        /* Prepare and generate Invoice */
		foreach ($vendorOrderIds as $key => $subOrderId) {
			 $invoiceGenerated = $this->generateInvoice($subOrderId);		
		
			if ($invoiceGenerated) {
				$shipmentGenerated = $this->generateShipment($subOrderId);
			}
		}
		/* Prepare and generate Invoice */		 

		  $this->messageManager->addSuccessMessage(__('The invoice & shipment has been created.'));
		
    		if (!$this->getRequest()->isAjax() ) 
    		{     
    			$this->_redirect('sales/order/view', ['order_id' => $orderId]);
    		}
    		 	   
	    }catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());

            if (!$this->getRequest()->isAjax() ) 
            {
                $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }             
        } catch (\Exception $e) {
            // $this->messageManager->addErrorMessage(__('We can\'t save the invoice and shipment right now.'));
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());
            if (!$this->getRequest()->isAjax() ) 
            {
                $this->_redirect('sales/order/view', ['order_id' => $orderId]);
            }  
        }
	}

	public function generateInvoice($vendorOrderId){

		$vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
		 
		$orderId = $vendorOrder->getOrderId();
		$vendorId = $vendorOrder->getVendorId();
	 	$order = $this->orderRepository->get($orderId);
	 	$lastItemToInvoice = $this->checkIfTheLastItemToInvoice($order);

		if ($vendorOrder->canInvoice()) {
            
            $items = [];
            $data = [];
        
            foreach ($vendorOrder->getItems() as $item) {
                    $liableEntitiesForDiscount[$item->getVendorOrderId()] =
                        $this->discountProcessor->calculateVendorDiscountAmount($item, $item->getVendorId());
            }

            foreach ($order->getItemsCollection() as $item) {
               
                if ( $vendorOrderId === $item->getVendorOrderId() ) {

                    if( null !== $item->getParentItemId() ){

                     $items[$item->getParentItemId()] =  $item->getQtyOrdered();
                    }else{
                        $items[$item->getId()] =  $item->getQtyOrdered();
                    }           
                }
            }
         
            $invoiceItems = $items;

            $invoice = $this->invoiceService->prepareInvoice(
                    $order,
                    $invoiceItems,
                    $vendorOrder->getVendorOrderId()
                );
            if($invoice){
                $invoice = $this->invoiceService->processInvoiceData(
                    $invoice,
                    $order,
                    $invoiceItems,
                    $vendorOrder,
                    $this->salesHelper->getConfig('commission/payout/shipping_liability', ScopeInterface::SCOPE_WEBSITE)
                );

                if (!$invoice) {
                    throw new LocalizedException(__('We can\'t save the invoice right now.'));
                }

                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }
                $this->registry->register('current_invoice', $invoice);

                $invoice->register();

                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());

                if ($vendorOrder->getId()) {
                    $liableEntityForDiscount = VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                    if (!empty($liableEntitiesForDiscount) &&
                        array_key_exists($vendorOrder->getId(), $liableEntitiesForDiscount)) {
                        $liableEntityForDiscount = ($liableEntitiesForDiscount[$vendorOrder->getId()]) ?
                            VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_SELLER : VendorOrder::LIABLE_ENTITY_FOR_DISCOUNT_ADMIN;
                    }
                    $vendorOrder->registerInvoice($invoice, $liableEntityForDiscount);
                    $vendorOrder->setData('main_order', $invoice->getOrder());
                    $transactionSave->addObject($vendorOrder);
                }
                $transactionSave->save();

                $this->_eventManager->dispatch('vendor_order_invoice_generate_after', ['order' => $vendorOrder]);

                if ($lastItemToInvoice) {
                    $refundedSc = $order->getBaseCustomerBalanceAmount() - ($order->getBaseCustomerBalanceInvoiced() + $order->getBaseCustomerBalanceRefunded());
                    if ($refundedSc > .0001) {
                        $this->_eventManager->dispatch('refund_store_credit_order_cancel_after', ['order' => $order, "sub_order_total"=> $refundedSc, 'vendor_order_id' => $vendorOrderId]);
                    }
                }

                $this->registry->unregister('current_invoice');

                return true;
     
                }else{
                    return false;
                }
        }else{

            return true;           
        }

	}

	public function generateShipment($vendorOrderId){
		 
		$vendorOrder = $this->vendorOrderRepository->getById($vendorOrderId);
		 
		$orderId = $vendorOrder->getOrderId();
		$vendorId = $vendorOrder->getVendorId();
	 	$order = $this->orderRepository->get($orderId);      

 		// if (!$vendorOrder->canShip()) {
   //              throw new \Magento\Framework\Exception\LocalizedException(
   //                  __('The order does not allow to create a shipment.')
   //              );
   //     	 }

	 	$items = [];
 		 foreach ($order->getItemsCollection() as $item) {
           
            if ( $vendorOrderId === $item->getVendorOrderId() ) {

                if( null !== $item->getParentItemId() ){

                 $items[$item->getParentItemId()] =  $item->getQtyOrdered();
                }else{
                    $items[$item->getId()] =  $item->getQtyOrdered();
                }           
            }
        }

        $data['vendor_id'] = $vendorId;
        $data['vendor_order_id'] = $vendorOrderId;
        $data['items'] = $items;

        $this->shipmentLoader->setOrderId($orderId);        
        $this->shipmentLoader->setShipment($data);        
        $shipment = $this->shipmentLoader->load($vendorOrderId);

        if($shipment){

            $shipment->setData('vendor_id',$vendorId);
            $shipment->register();
            $vendorOrder->setData('main_order', $shipment->getOrder());
            $vendorOrder->setStatus(VendorOrder::STATUS_SHIPPED);
            $shipment->setVendorOrder($vendorOrder);
            $shipment->setVendorOrderId($vendorOrderId);
            $this->_saveShipment($shipment);

            $this->_eventManager->dispatch('vendor_order_shipment_generate_after', ['order' => $vendorOrder]);
            
            $this->_eventManager->dispatch('sales_order_shipment_save_after_pushnotification', ['shipment' => $shipment]);
            
            $this->registry->unregister('current_shipment');

            return true;
        }else{
        	return false;
        } 
	}

	protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->addObject(
            $shipment->getVendorOrder()
        )->save();

        return $this;
    }

    public function checkIfTheLastItemToInvoice($order) {
        $i = 0; $j = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getQtyOrdered() > ($item->getQtyCanceled() + $item->getQtyRefunded() + $item->getQtyInvoiced())) {
                $j = 1; $j += $i; $i++;
            }
        }
        if ($j === 1) { return true; }
        return false;
    }
}