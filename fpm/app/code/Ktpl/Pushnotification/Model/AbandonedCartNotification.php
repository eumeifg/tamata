<?php

namespace Ktpl\Pushnotification\Model;

class AbandonedCartNotification 
{
    /**
     * Basket prefix for accessing stored quotes
     */
    const CONNECTOR_BASKET_PATH = 'connector/email/getbasket';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Dotdigitalgroup\Email\Model\Catalog\UrlFinder
     */
    private $urlFinder;

    protected $ktplPushNTFactory;
    protected $faviconIcon;
    protected $assetRepo;

    /**
     * Data constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Dotdigitalgroup\Email\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Dotdigitalgroup\Email\Model\Catalog\UrlFinder $urlFinder
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Dotdigitalgroup\Email\Model\Catalog\UrlFinder $urlFinder,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
         \Ktpl\Pushnotification\Model\Firebase $fireBase,
        \Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $deviceCollectionFactory,
         \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory $ktplPushNTFactory,
         \Magento\Theme\Model\Favicon\Favicon $faviconIcon,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        parent::__construct($storeManager ,$productRepository, $scopeConfig, $helper, $dateTime,
                            $urlFinder);

        $this->fireBaseFactory = $fireBaseFactory;
        $this->fireBase = $fireBase;
        $this->deviceCollectionFactory = $deviceCollectionFactory;
        $this->ktplPushNTFactory = $ktplPushNTFactory;
        $this->faviconIcon = $faviconIcon;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $storeId
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function send($quote, $storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $client = $this->helper->getWebsiteApiClient($store->getWebsiteId());
        $this->mobileNotification($quote, $store);
        $payload = $this->getPayload($quote, $store);

        $client->postAbandonedCartCartInsight($payload);
    }

    public function mobileNotification($quote, $store)
    {   
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cartnotification.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("cartnotification");

        $contactIdentifier = $quote->getCustomerId();

        $cartId = $quote->getId();
        $cartUrl =  $this->getBasketUrl($quote->getId(), $store);
        $logger->info($cartId);
        $logger->info($cartUrl);
        $deviceCollection = $this->deviceCollectionFactory->create()
                                    ->addAttributeToSelect('device_token')
                                    ->addFieldToFilter('customer_id', $contactIdentifier);

        $logger->info($deviceCollection->getSize());  
        $logger->info("deviceSize".$deviceCollection->getSize());  
                                    
        if($deviceCollection->getSize() > 0)
        {
            $firebase = $this->fireBaseFactory->create();

            $abandonedCartsTemplate = $this->scopeConfig->getValue('pushnotification/template/abandoned_carts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $logger->info($abandonedCartsTemplate);

            $message = str_replace('{{cart_id}}', $cartUrl, $abandonedCartsTemplate);

            $logger->info($message);
            $firebase->setObserver($product)
            ->setType('cart')
            ->setTypeId($cartId)
            ->setMessage($message)
            ->send();

            $ktplPushNTFObj = $this->ktplPushNTFactory->create();
            $ktplPushNTFObj->setTitle("Abandoned Cart");
            $ktplPushNTFObj->setDescription($message);
            $ktplPushNTFObj->setImageUrl($this->assetRepo->getUrl($this->faviconIcon->getDefaultFavicon()));
            $ktplPushNTFObj->setCustomerEmail($quote->getCustomerEmail());
            $ktplPushNTFObj->setTypePromotion("abandoned_cart");
            $ktplPushNTFObj->setPromotionId($cartId);
            $ktplPushNTFObj->setStatus(1);
            $ktplPushNTFObj->save();
        }

    }
}
