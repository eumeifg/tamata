<?php

namespace Ktpl\Pushnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class RecentlyViewProductObserver implements ObserverInterface
{
    protected $fireBaseFactory;
    protected $scopeConfig;
    protected $ktplPushNTFactory;
    protected $faviconIcon;
    protected $assetRepo;
    public function __construct(
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ktpl\Pushnotification\Model\Firebase $fireBase,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $deviceCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productModal,
        \Ktpl\Pushnotification\Model\KtplRecentViewProductRepository $recentViewProductRepository,
        \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory $ktplPushNTFactory,
        \Magento\Theme\Model\Favicon\Favicon $faviconIcon,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->fireBaseFactory = $fireBaseFactory;
        $this->scopeConfig = $scopeConfig;
        $this->fireBase = $fireBase;
        $this->deviceCollectionFactory = $deviceCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productModal = $productModal;
        $this->recentViewProductRepository = $recentViewProductRepository;
        $this->ktplPushNTFactory = $ktplPushNTFactory;
        $this->faviconIcon = $faviconIcon;
        $this->assetRepo = $assetRepo;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $notificationEnabled = $this->scopeConfig->getValue('pushnotification/template/recently_view_product_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($notificationEnabled) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/recentlyViewProductObserver.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info("recentlyViewProductObserver");
            $recentlyViewProduct = $observer->getEvent()->getData();
            $deviceTokenId = $recentlyViewProduct['kp_token'];
            $storeId = $this->storeManager->getStore()->getId();
            if (!$storeId) {
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }

            $deviceCollection = $this->deviceCollectionFactory->create();
            $joinConditions = 'main_table.device_token = krvpl.device_token_id';
            $deviceCollection->getSelect()->join(
                ['krvpl' => 'ktpl_recent_view_product_list'],
                $joinConditions,
                []
            )->columns("krvpl.product_id")->where("krvpl.store_id=" . $storeId . " and krvpl.added_at >= NOW() - INTERVAL 1 DAY and krvpl.device_token_id = '" . $deviceTokenId . "'");

            $logger->info("query");
            // $logger->info($deviceCollection->getSelect());

            if ($deviceCollection->getSize() > 0) {

                $product = $observer->getEvent()->getProduct();
                foreach ($deviceCollection->getData() as $value) {
                    $productId = $value['product_id'];
                    $token = $value['device_token'];

                    $productDetail = $this->productModal->create()->load($productId);
                    $logger->info($productDetail->getName());
                    $firebase = $this->fireBaseFactory->create();
                    $recentlyViewTemplate = $this->scopeConfig->getValue('pushnotification/template/recently_view_product', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $logger->info($recentlyViewTemplate);
                    $message = str_replace('{{product_name}}', $productDetail->getName(),
                        $recentlyViewTemplate);

                    if ($value['device_token'] == $deviceTokenId) {
                        $firebase->setObserver($product)
                            ->setRecentProductName($productDetail->getName())
                            ->setType('recently')
                            ->setTypeId($productId)
                            ->setMessage($recentlyViewTemplate)
                            ->send();
                        $this->recentViewProductRepository->deleteById($productId);
                        $ktplPushNTFObj = $this->ktplPushNTFactory->create();
                        $ktplPushNTFObj->setTitle("Recently Viewed Products");
                        $ktplPushNTFObj->setDescription($firebase->parseMessageVariables(null, $recentlyViewTemplate));
                        $ktplPushNTFObj->setImageUrl($this->assetRepo->getUrl($this->faviconIcon->getDefaultFavicon()));
                        // $ktplPushNTFObj->setCustomerEmail($order->getCustomerEmail());
                        $ktplPushNTFObj->setCustomerEmail(null);
                        $ktplPushNTFObj->setTypePromotion("recently_viewed");
                        $ktplPushNTFObj->setPromotionId($productId);
                        $ktplPushNTFObj->setStatus(1);
                        $ktplPushNTFObj->save();
                    }

                }
            }

        }

    }

}
