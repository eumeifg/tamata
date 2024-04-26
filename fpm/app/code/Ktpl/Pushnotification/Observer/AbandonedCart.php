<?php

namespace Ktpl\Pushnotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ktpl\Pushnotification\Model\KtplAbandonCartFactory;

class AbandonedCart implements ObserverInterface
{

  protected $fireBaseFactory;
  protected $scopeConfig;
  public function __construct(

    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $deviceCollectionFactory,
    KtplAbandonCartFactory $ktplAbandonCartFactory
  )
  {
    $this->storeManager = $storeManager;
    $this->deviceCollectionFactory = $deviceCollectionFactory;
    $this->ktplAbandonCartFactory = $ktplAbandonCartFactory;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {

  	  $abandonedCartdata = $observer->getEvent()->getData();
  	  $kpQuoteId = $abandonedCartdata['kp_quote_id'];
  	  $kpQuoteItemId = $abandonedCartdata['kp_quote_item_id'];
      $kpCartCustomerId = $abandonedCartdata['kp_quote_customer_id'];

      $deviceCollection = $this->deviceCollectionFactory->create()
      							->addFieldToSelect('device_token')
      							->addFieldToFilter('main_table.customer_id', 
      									array('eq' => $kpCartCustomerId));
						
      $deviceData = $deviceCollection->getData(); 
      
      foreach ($deviceData as $devicevalue) {
			$deviceTokenId = $devicevalue['device_token'];
      }
	        
      $model =  $this->ktplAbandonCartFactory->create();
      $model->setData('device_token_id', $deviceTokenId);
      $model->setData('quote_id', $kpQuoteId);
      $model->setData('quote_item_id', $kpQuoteItemId);

      $storeId = $this->storeManager->getStore()->getId();
      if (!$storeId) {
          $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
      }

      $model->setData('store_id', $storeId);
      $model->setStoreId($storeId);
      $model->save();
   
  }

}
