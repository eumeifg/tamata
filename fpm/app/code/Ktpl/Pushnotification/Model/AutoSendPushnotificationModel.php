<?php


namespace Ktpl\Pushnotification\Model;


class AutoSendPushnotificationModel
{
    protected $_date;

    public function __construct(
        \Ktpl\Pushnotification\Model\KtplPushnotificationsFactory $ktplNotificationsFactory,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
    ) {
        $this->ktplNotificationsFactory = $ktplNotificationsFactory;
        $this->fireBaseFactory = $fireBaseFactory;
        $this->_date =  $date;
    }

    public function sendFirebaseNotifications(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/auto_send_pushnotification.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        try{

            $toBeSentNotifications = $this->ktplNotificationsFactory->create()->getCollection();
            $toBeSentNotifications->addFieldToFilter('is_sent',1);
            $notification = $toBeSentNotifications->getFirstItem();

            $date = $notification->getUpdatedAt();
            $currentDate = strtotime($date);
            $futureDate = $currentDate + (60*(int)$notification->getBatchInterval());
            $futureDate = date("Y-m-d H:i:s", $futureDate);
            /*echo "===>".date('Y-m-d H:i:s');
            echo "=====>".$futureDate;  die();*/

            if ($notification->getId() && strtotime(date('Y-m-d H:i:s')) >= strtotime($futureDate)):
                /* Started sending notification*/
                // $notification->setIsSent(2);
                // $notification->save();

                $firebase = $this->fireBaseFactory->create();
                $firebase->setTitle($notification->getTitle())
                ->setMessage($notification->getDescription())
                ->setType($notification->getTypePromotion())
                ->setTypeId($notification->getPromotionId())
                ->setImage($notification->getImageUrl())
                ->setDevicePerBatch($notification->getDevicePerBatch())
                ->setCurrentPageValue($notification->getCurrentPage());

                if( $notification->getSendToCustomerGroup() && null !== $notification->getSendToCustomerGroup() ) {

                    $firebase->setCustomerGroups($notification->getSendToCustomerGroup());
                    $notification->setIsSent(3);

                } elseif ( $notification->getSendToCustomer() && null !== $notification->getSendToCustomer() ) {

                    $firebase->setCustomers($notification->getSendToCustomer());
                    $notification->setIsSent(3);
                } else {
                    $firebase->setAllDevice('all');
                    if ($notification->getCurrentPage() >= $notification->getTotalCount()){
                        $notification->setIsSent(3);
                    }
                }

                $firebase->send();

                /* Notification sent */

                $notification->setCurrentPage($notification->getCurrentPage() + 1);
                $notification->setUpdatedAt(date('Y-m-d H:i:s'));
                $notification->save();
            endif;

        }catch(\Exception $e) {

             $logger->info(print_r($e->getMessage(), true));
        }
    }

}

