<?php 

namespace MDC\PickupPoints\Helper;

use Magento\Store\Model\ScopeInterface; 
/**
 * 
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	const XML_PATH_PIKCUP_POINTS_ENABLE = 'pickups_points/general/enable';
	
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		$this->_storeManager = $storeManager;
		$this->scopeConfig = $scopeConfig;
	}

	public function isPikcupPointsEnabled(){

		 
		$pickupPointStatus = $this->scopeConfig->getValue(self::XML_PATH_PIKCUP_POINTS_ENABLE,ScopeInterface::SCOPE_STORE);

		$pickupPointsEnabled = false;
		
	 	if($pickupPointStatus){

	 		$pickupPointsEnabled  = true;
	 	}else{
	 		$pickupPointsEnabled  = false; 
	 	}

        return $pickupPointsEnabled;
	}
}