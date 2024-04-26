<?php

namespace Ktpl\Pushnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class NotificationOrderStateChange implements ObserverInterface
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

        $notificationEnabled = $this->scopeConfig->getValue('pushnotification/template/order_status_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($notificationEnabled) {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/NotificationOrderStateChange.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info("NotificationOrderStateChange");

            $order = $observer->getEvent()->getOrder();
            $status = $order->getStatus();
            $state = $order->getState();
            //$order = $invoice->getOrder();
            //echo $order->getCustomerFirstName();die;

            if ($state != 'new' && $state != 'processing' && $state != 'complete') {
                $firebase = $this->fireBaseFactory->create();
                $invoiceTemplate = $this->scopeConfig->getValue('pushnotification/template/order_status_change', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $logger->info("orderTemplate" . $invoiceTemplate);
                $logger->info("IncrementId" . $order->getIncrementId());
                $logger->info("CustomerEmail" . $order->getCustomerEmail());

                $firebase->setObserver($order)
                    ->setOrderId($order->getIncrementId())
                    ->setMessage($invoiceTemplate)
                    ->setCustomers($order->getCustomerEmail())
                    ->send();

                $logger->info("firebase sent" . $order->getCustomerEmail());
                $ktplPushNTFObj = $this->ktplPushNTFactory->create();
                $ktplPushNTFObj->setTitle("Order Status Change");
                $ktplPushNTFObj->setDescription($firebase->parseMessageVariables(null, $invoiceTemplate));
                $ktplPushNTFObj->setImageUrl($this->assetRepo->getUrl($this->faviconIcon->getDefaultFavicon()));
                $ktplPushNTFObj->setCustomerEmail($order->getCustomerEmail());
                $ktplPushNTFObj->setTypePromotion("order_status_changed");
                $ktplPushNTFObj->setPromotionId($order->getEntityId());
                $ktplPushNTFObj->setStatus(1);
                $ktplPushNTFObj->save();
            }

        }
    }
}
