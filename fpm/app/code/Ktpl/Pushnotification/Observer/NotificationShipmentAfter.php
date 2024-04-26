<?php

namespace Ktpl\Pushnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class NotificationShipmentAfter implements ObserverInterface
{
    protected $fireBaseFactory;
    protected $scopeConfig;
    protected $ktplPushNTFactory;
    protected $faviconIcon;
    protected $assetRepo;

    public function __construct(
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory $ktplPushNTFactory,
        \Magento\Theme\Model\Favicon\Favicon $faviconIcon,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->fireBaseFactory = $fireBaseFactory;
        $this->scopeConfig = $scopeConfig;
        $this->ktplPushNTFactory = $ktplPushNTFactory;
        $this->faviconIcon = $faviconIcon;
        $this->assetRepo = $assetRepo;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $notificationEnabled = $this->scopeConfig->getValue('pushnotification/template/new_shipping_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($notificationEnabled) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/NotificationShipmentAfter.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info("NotificationShipmentAfter");

            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            //echo $shipment->getCustomerEmail();die;
            $firebase = $this->fireBaseFactory->create();
            $orderTemplate = $this->scopeConfig->getValue('pushnotification/template/new_shipping', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //echo $orderTemplate;die;

            $logger->info("orderTemplate" . $orderTemplate);
            $logger->info("IncrementId" . $order->getIncrementId());
            $logger->info("CustomerEmail" . $order->getCustomerEmail());

            $firebase->setObserver($shipment)
                ->setOrderId($order->getIncrementId())
                ->setMessage($orderTemplate)
                ->setCustomers($order->getCustomerEmail())
                ->send();

            $logger->info("firebase sent" . $order->getCustomerEmail());
            $ktplPushNTFObj = $this->ktplPushNTFactory->create();
            $ktplPushNTFObj->setTitle("Order Shipping");
            $ktplPushNTFObj->setDescription($firebase->parseMessageVariables(null, $orderTemplate));
            $ktplPushNTFObj->setImageUrl($this->assetRepo->getUrl($this->faviconIcon->getDefaultFavicon()));
            $ktplPushNTFObj->setCustomerEmail($order->getCustomerEmail());
            $ktplPushNTFObj->setTypePromotion("order_shipped");
            $ktplPushNTFObj->setPromotionId($order->getEntityId());
            $ktplPushNTFObj->setStatus(1);
            $ktplPushNTFObj->save();
        }

    }
}
