<?php
namespace Ktpl\Pushnotification\Cron;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class RecentlyViewedNotificationSend
{
	const XML_PATH_ENABLE = 'pushnotification/template/recently_view_product_notification';

	public function __construct(
        \Ktpl\Pushnotification\Model\KtplRecentViewProductRepository $KtplRecentViewProductRepository,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplRecentViewProduct\CollectionFactory $recentlyviewdCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $deviceCollectionFactory,
        DateTimeFactory $dateTimeFactory,
        \Magento\Catalog\Model\ProductFactory $productModal,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory
    ) {
         $this->KtplRecentViewProductRepository = $KtplRecentViewProductRepository;
         $this->recentlyviewdCollection = $recentlyviewdCollection;
         $this->scopeConfig = $scopeConfig;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->deviceCollectionFactory = $deviceCollectionFactory;
        $this->productModal = $productModal;
        $this->fireBaseFactory = $fireBaseFactory;
    }

	public function execute()
	{
        $notificationEnabled = $this->scopeConfig->getValue( self::XML_PATH_ENABLE,
                                                                ScopeInterface::SCOPE_STORE
                                                            );
		if($notificationEnabled)
		{
		    $recentlyviewdCollection = $this->getRecentlyViewdCollection();

            if($recentlyviewdCollection->getSize() > 0)
            {
                $recentlyViewTemplate = $this->scopeConfig->getValue(
                                                        'pushnotification/template/recently_view_product',
                                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                                    );

                foreach ($recentlyviewdCollection as $devicedata) {
                    $productId = $devicedata->getProductId();
                    $token = $devicedata->getDeviceTokenId();

                    $productDetail = $this->productModal->create()->load($productId);

                    $firebase = $this->fireBaseFactory->create();
                    $deviceCollection = $this->deviceCollectionFactory->create();

                    $wholedevicedata = $deviceCollection->addFieldToFilter('device_token', $token)->getFirstItem();
                    if(!empty($wholedevicedata->getData()))
                    {
                        if (strtolower($wholedevicedata->getDeviceType()) === 'android') {
                            $deviceType = "Android";
                        }elseif (strtolower($wholedevicedata->getDeviceType()) === 'ios') {
                            $deviceType = "IOS";
                        }else{
                            $deviceType = "Android";
                        }

                        $firebase->setType('recently')
                            ->setTypeId($productId)
                            ->setRecentProductName($productDetail->getName())
                            ->setMessage($recentlyViewTemplate)
                            ->callFirebaseApiStandard($token,$wholedevicedata->getCustomerId(), $deviceType);

                        $devicedata->setData('send_status',1)->save();
                    }

                }
            }
			//return $this;
		}		

	}

    public function getRecentlyViewdCollection()
    {
        $recentlyviewdCollection = $this->recentlyviewdCollection->create();
        $recentlyviewdCollection->addFieldToFilter('send_status', 0);
        $fromDate = date('Y-m-d H:i:s', strtotime('-48 hour'));
        $toDate = date('Y-m-d H:i:s', strtotime($this->dateTimeFactory->create()->gmtDate('Y-m-d H:i:s')));
        $recentlyviewdCollection->addFieldToFilter('added_at', array(
            'from' => $fromDate,
            'to' => $toDate,
            'date' => true,
        ));

        $recentlyviewdTime = $this->getRecentlyViewdTime();
        $recentlyviewdCollection->getSelect()->where("timestampdiff(MINUTE,added_at,NOW()) >= ".$recentlyviewdTime);
        //echo $recentlyviewdCollection->getSelect()->__toString(); die();
//        echo '<pre>';
//        print_r($recentlyviewdCollection);
//        die();

        return $recentlyviewdCollection;

    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function getRecentlyViewdTime()
    {
        $recentlyviewdTime = $this->scopeConfig->getValue('pushnotification/template/recentlyviewd_time',
                                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                                                    );;
        if (empty($recentlyviewdTime)) {
            $recentlyviewdTime = 30;
        }

        return $recentlyviewdTime;
    }
}