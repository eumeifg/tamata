<?php

declare (strict_types=1);

namespace Ktpl\Pushnotification\Model;

use Exception;
use Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface;
use Ktpl\Pushnotification\Api\KtplPushnotificationListInterface;
use Ktpl\Pushnotification\Model\Data\KtplPushnotificationData;
use Ktpl\Pushnotification\Model\Data\KtplPushnotificationDataFactory;
use Ktpl\Pushnotification\Model\Data\KtplPushnotificationObjFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Theme\Model\Favicon\Favicon;

class KtplPushnotificationList implements KtplPushnotificationListInterface
{
    /**
     * @var KtplPushnotificationsFactory
     */
    protected $pushNotifyFactory;

    /**
     * @var KtplPushnotificationData
     */
    protected $pushNotifyData;

    /**
     * @var KtplPushnotificationObjFactory
     */
    protected $pushNotifyObjData;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Favicon
     */
    protected $faviconIcon;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @param KtplPushnotificationsFactory $pushNotifyFactory
     * @param KtplPushnotificationDataFactory $pushNotifyData
     * @param KtplPushnotificationObjFactory $pushNotifyObjData
     * @param CustomerFactory $customerFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param Favicon $faviconIcon
     * @param Repository $assetRepo
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        Favicon                         $faviconIcon,
        Repository                      $assetRepo,
        CustomerFactory                 $customerFactory,
        DataObjectFactory               $dataObjectFactory,
        TimezoneInterface               $timezoneInterface,
        KtplPushnotificationsFactory    $pushNotifyFactory,
        KtplPushnotificationObjFactory  $pushNotifyObjData,
        KtplPushnotificationDataFactory $pushNotifyData
    )
    {
        $this->faviconIcon = $faviconIcon;
        $this->assetRepo = $assetRepo;
        $this->customerFactory = $customerFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->pushNotifyFactory = $pushNotifyFactory;
        $this->pushNotifyObjData = $pushNotifyObjData;
        $this->pushNotifyData = $pushNotifyData;
    }

    /**
     * Get All Push Notification List By Customer Id
     * @param int $customerId The Customer ID.
     * @return KtplPushnotificationDataInterface
     * @throws LocalizedException.
     */
    public function getNotificationList($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $pushNotifyDataObj = $this->pushNotifyData->create();
        try {
            if (!$customer) {
                $invalid = [["message" => __('Customer not exist')]];
                return $invalid;
            }

            $to = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
            $from = strtotime('-15 day', strtotime($to));
            $from = date('Y-m-d h:i:s', $from); // 15 days before
            $pushFactory = $this->pushNotifyFactory->create()->getCollection();
            // $pushFactory->addFieldToFilter('send_to_customer', ['finset' => $customer->getEmail()])
            $pushFactory->addFieldToSelect(
                ['id', 'title', 'description', 'promotion_id', 'type_promotion', 'image_url', 'created_at']
            )->addFieldToFilter(
                ['send_to_customer', 'send_to_customer_group'],
                [
                    ['finset' => $customer->getEmail()],
                    ['finset' => $customer->getGroupId()],
                ]
            )->addFieldToFilter('created_at', array('from' => $from))->addFieldToFilter('is_sent', ['in' => array(0, 3)]);

            $items1 = $this->createNotificationArray($pushFactory, $customer);

            /* get pushnotification which is sends by selecting to All */
            $pushFactoryofAll = $this->pushNotifyFactory->create()->getCollection();
            $pushFactoryofAll->addFieldToSelect(
                ['id', 'title', 'description', 'promotion_id', 'type_promotion', 'image_url', 'created_at']
            )->addFieldToFilter('send_to_customer_group', ['null' => true])
                ->addFieldToFilter('send_to_customer', ['null' => true])
                ->addFieldToFilter('created_at', array('from' => $from))
                ->addFieldToFilter('is_sent', ['in' => array(0, 3)]);

            $items2 = $this->createNotificationArray($pushFactoryofAll, $customer);

            /* get pushnotification which is sends by selecting to All */

            $sortByDateDesc = [];
            $items = array_merge($items1, $items2);
            foreach ($items as $key => $value) {
                $sortByDateDesc[] = strtotime($value->getNotificationCreatedAt());
            }
            array_multisort($sortByDateDesc, SORT_DESC, $items);

        } catch (Exception $exception) {
            throw new LocalizedException(__(
                'Could not save the Push notifications: %1',
                $exception->getMessage()
            ));
        }
        $pushNotifyDataObj->setNotifications($items);
        return $pushNotifyDataObj;
    }

    /**
     * @param $customerObject
     * @param $message
     * @return array|mixed|string|string[]
     */
    public function parseLocalNotificationVariables($customerObject, $message)
    {
        foreach ($this->getReplaceableVariables() as $value) {
            if (strpos($message, $value) !== false) {
                switch ($value) {
                    case '{{first_name}}':
                        $message = str_replace($value, $customerObject->getFirstname(), $message);
                        break;
                    case '{{last_name}}':
                        $message = str_replace($value, $customerObject->getLastname(), $message);
                        break;
                    case '{{full_name}}':
                        $message = str_replace($value, $customerObject->getFullname(), $message);
                        break;
                }
            }
        }

        return $message;
    }

    /**
     * @return string[]
     */
    public function getReplaceableVariables(): array
    {
        return [
            '{{first_name}}',
            '{{last_name}}',
            '{{full_name}}',
            '{{order_id}}',
            '{{invoice_id}}',
            '{{shipping_id}}',
            '{{product_name}}',
            '{{cart_id}}'
        ];
    }

    /**
     * @param $pushNotice
     * @param $customer
     * @return array
     */
    public function createNotificationArray($pushNotice, $customer): array
    {
        $items = [];
        foreach ($pushNotice as $model) {
            $pushNotifyData = $this->pushNotifyObjData->create();
            $pushNotifyData->setNotificationId($model->getId());
            $pushNotifyData->setNotificationTitle($this->parseLocalNotificationVariables($customer, $model->getTitle()));
            $pushNotifyData->setNotificationDescription($this->parseLocalNotificationVariables($customer, $model->getDescription()));
            $pushNotifyData->setRedirectionTypeid($model->getPromotionId());
            $nType = "";
            if ($model->getTypePromotion() == "category") {
                $nType = "category_promotions";
            }
            if ($model->getTypePromotion() == "microsite") {
                $nType = "microsite_promotions";
            }
            if ($model->getTypePromotion() == "product") {
                $nType = "product_promotions";
            }
            if ($model->getTypePromotion() == "offer_page") {
                $nType = "offer_page";
            }
            $pushNotifyData->setNotificationType($nType);
            $pushNotifyData->setNotificationImgUrl($model->getImageUrl());
            $pushNotifyData->setNotificationCreatedAt($model->getCreatedAt());
            $items[] = $pushNotifyData;
        }
        return $items;
    }
}
