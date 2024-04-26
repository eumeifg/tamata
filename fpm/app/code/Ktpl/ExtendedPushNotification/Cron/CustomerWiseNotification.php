<?php
namespace Ktpl\ExtendedPushNotification\Cron;

use Magento\Store\Model\ScopeInterface;

class CustomerWiseNotification
{
	const XML_PATH_ENABLE = 'pushnotification/customerwisenotification/cron_enable';

	public function __construct(       
        \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory $ktplPushNTFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
         $this->ktplPushNTFactory = $ktplPushNTFactory;
         $this->scopeConfig = $scopeConfig;
         $this->logger      = $logger;
    }

	public function execute()
	{
		 	
	 	try {
		 	
			$customerGarbageClenCronEnable =  $this->scopeConfig->getValue( self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE);
			
			$to = date("Y-m-d h:i:s"); // current date
	        $from = strtotime('-15 day', strtotime($to));
	        $from = date('Y-m-d h:i:s', $from); // 15 days before
	        
				if($customerGarbageClenCronEnable)
				{
					$ktplPushNTFObj = $this->ktplPushNTFactory->create()
		                ->getCollection()                
		                ->addFieldToFilter('created_at', array('to'=>$from));       
		               		           
			            foreach ($ktplPushNTFObj as $item) {
			                $item->delete();
			            }                
				}
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
	}
}