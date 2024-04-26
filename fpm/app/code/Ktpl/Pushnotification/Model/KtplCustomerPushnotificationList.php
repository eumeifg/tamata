<?php

namespace Ktpl\Pushnotification\Model;

use Ktpl\Pushnotification\Api\KtplCustomerPushnotificationListInterface;
/**
 * 
 */
class KtplCustomerPushnotificationList implements KtplCustomerPushnotificationListInterface
{
	 /**
     * @var \Ktpl\Pushnotification\Model\KtplPushnotificationsFactory
     */
    protected $pushNotifyFactory;

    /**
     * @var \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory
     */
    protected $ktplPushNTFactory;

     /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
	
	function __construct(
		\Ktpl\Pushnotification\Model\KtplPushnotificationsFactory $pushNotifyFactory,
        \Ktpl\ExtendedPushNotification\Model\KtplPushNotificationTransactionalFactory $ktplPushNTFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
	)
	{
		$this->pushNotifyFactory = $pushNotifyFactory;
        $this->ktplPushNTFactory = $ktplPushNTFactory;
        $this->customerFactory = $customerFactory;
         
	}

	/**
     * Get All Push Notification List By Customer Id
     * @param int $customerId The Customer ID.
     * @return \Ktpl\Pushnotification\Api\Data\KtplPushnotificationDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function getNotificationList($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $items = [];
        $counter = 0;
        
        try {
            if (!$customer) {
                $invalid = [
                    [
                        "message" => __('Customer not exist'),
                    ],
                ];
                return $invalid;
            }
            
            $to = date("Y-m-d h:i:s"); // current date
            $from = strtotime('-15 day', strtotime($to));
            $from = date('Y-m-d h:i:s', $from); // 15 days before            
            $pushFactory = $this->pushNotifyFactory->create()->getCollection();
            // $pushFactory->addFieldToFilter('send_to_customer', ['finset' => $customer->getEmail()])
            $pushFactory->addFieldToFilter(['send_to_customer','send_to_customer_group'],
                    [
                        ['finset' => $customer->getEmail()],
                        ['finset' => $customer->getGroupId()],
                    ]
                )
                ->addFieldToFilter('created_at', array('from'=>$from))               
                ->addFieldToFilter('is_sent', ['in' => array(0,3)]); 
            $ktplPushNTFObj = $this->ktplPushNTFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_email', ['eq' => $customer->getEmail()])
                // ->addFieldToFilter('created_at', ['lteq' => $to])
                // ->addFieldToFilter('created_at', ['gteq' => $from]);
                // ->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to));
                ->addFieldToFilter('created_at', array('from'=>$from));                
           
   

            /* get pushnotification which is sends by selecting to All */
           
            $sortByDateDesc = array();
            
            foreach ($items as $key => $value){
                $sortByDateDesc[] = strtotime($value->getNotificationCreatedAt());
            }
            array_multisort($sortByDateDesc, SORT_DESC, $items);
           
        } catch (\Exception $exception) {
            throw new LocalizedException(__(
                'Could not save the Pushnotifications: %1',
                $exception->getMessage()
            ));
        }
         
    }

    public function parseLocalNotificationVariables($customerId,$message)
    {
        $customerObject = $this->customerFactory->create()->load($customerId);

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

    public function getReplaceableVariables()
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
}