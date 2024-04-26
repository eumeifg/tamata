<?php
namespace Ktpl\Pushnotification\Cron;

use Magento\Store\Model\ScopeInterface;

class RecentlyViewed
{
	const XML_PATH_ENABLE = 'pushnotification/recentlyviewcron/cron/enable';

	public function __construct(
        \Ktpl\Pushnotification\Model\KtplRecentViewProductRepository $KtplRecentViewProductRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
         $this->KtplRecentViewProductRepository = $KtplRecentViewProductRepository;
         $this->scopeConfig = $scopeConfig;
    }

	public function execute()
	{
		die('Do not process');

		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/recently_viewed_cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(__METHOD__);
		$enable =  $this->scopeConfig->getValue( self::XML_PATH_ENABLE, 
												 ScopeInterface::SCOPE_STORE
        										);
		if($enable == 1)
		{
			$this->KtplRecentViewProductRepository->delete();
			return $this;
		}		

	}
}