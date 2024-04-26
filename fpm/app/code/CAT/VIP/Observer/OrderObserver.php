<?php
namespace CAT\VIP\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use CAT\VIP\Model\VipOrdersFactory;
 
class OrderObserver implements ObserverInterface
{
	protected $dataHelper;
	protected $vipOrdersFactory;
	protected $customerSession;

	public function __construct(\CAT\VIP\Helper\Data $dataHelper , \CAT\VIP\Model\VipOrdersFactory $vipOrdersFactory , \Magento\Customer\Model\Session $customerSession) {
        $this->dataHelper = $dataHelper;
        $this->vipOrdersFactory = $vipOrdersFactory;
        $this->customerSession = $customerSession;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
					
	   if($this->dataHelper->getConfig('vip/general/enable')){
	       
	       $Products = $observer->getEvent()->getOrder()->getAllItems();
	       foreach($Products as $Product){

	       		if($this->dataHelper->isvipProduct($Product->getProductId(),$Product->getVendorId())) {
	
		       		// Create vip order
	       			$vipdata['customer_id'] = $observer->getEvent()->getOrder()->getCustomerId();
	       			$vipdata['product_id'] = $Product->getProductId();
	       			$vipdata['vendor_id'] = $Product->getVendorId();
	       			$vipdata['qty'] = $Product->getQtyOrdered();
	       			$vipdata['order_id'] = $observer->getEvent()->getOrder()->getIncrementId();
	       			$model = $this->vipOrdersFactory->create();
	       			$model->setData($vipdata);
	       			try {
							$model->save();

						} catch (\Magento\Framework\Exception\LocalizedException $e) {

							$this->messageManager->addError($e->getMessage());

						} catch (\RuntimeException $e) {

							$this->messageManager->addError($e->getMessage());

						}

	       		}
	       }
 		}
    }
}