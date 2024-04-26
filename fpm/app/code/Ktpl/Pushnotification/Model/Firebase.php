<?php

namespace Ktpl\Pushnotification\Model;

use Firebase\JWT\JWT;

class Firebase
{
	private $tokens = ['android' => [], 'ios' => []];
	private $observer = null;
	private $customerGroups = null;
	private $customers = null;
	private $title = null;
	private $message = null;
	private $image = null;
	private $type = null;
	private $typeId = null;
    private $devicePerBatch = null;
    private $currentPageValue = null;
    private $allDevices = null;
	protected $customerFactory;
	private $globalCustomers;

	public function __construct(
		\Ktpl\Pushnotification\Model\ResourceModel\KtplDevicetokens\CollectionFactory $deviceCollectionFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Store\Model\StoreManagerInterface $storeManager

	) {
		$this->deviceCollectionFactory = $deviceCollectionFactory;
		$this->scopeConfig = $scopeConfig;
		$this->customerCollectionFactory = $customerCollectionFactory;
		$this->customerFactory = $customerFactory;
		$this->_filesystem = $filesystem;
		$this->mediaDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
		$this->storeManager = $storeManager;
	}

	public function setObserver($observer)
	{
		$this->observer = $observer;
		return $this;
	}

	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	public function setRecentProductName($productName)
	{
		$this->recentProductName = $productName;
		return $this;
	}

	public function setCartId($cartId)
	{
		$this->cartId = $cartId;
		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function setTypeId($typeId)
	{
		$this->typeId = $typeId;
		return $this;
	}

	public function parseMessageVariables($customerId = null, $message)
	{
		try {
			if($this->observer) {
				$observerCustomerId = $this->observer->getCustomerId();
			} else {
				$observerCustomerId = '';
			}
			$customerObject = $this->getCachedCustomer($customerId ? $customerId[0] : $observerCustomerId);
		} catch(\Exception $e) {
			$customerObject = new \Magento\Framework\DataObject();
			$customerObject->setData([
				'firstname' => '',
				'lastname' => '',
				'fullname' => ''
			]);
		}
		$customerObject = $this->customerFactory->create()->load($customerId ? $customerId : $this->observer->getCustomerId());
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
					case '{{order_id}}':
						$message = str_replace($value, $this->orderId, $message);
						break;
					case '{{invoice_id}}':
						$message = str_replace($value, $this->observer->getIncrementId(), $message);
						break;
					case '{{shipping_id}}':
						$message = str_replace($value, $this->observer->getIncrementId(), $message);
						break;
					case '{{product_name}}':
						$message = str_replace($value, $this->recentProductName, $message);
						break;
					case '{{cart_id}}':
						$message = str_replace($value, $this->typeId, $message);
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

	public function getCachedCustomer($customerId)
	{
		try {
			if (!isset($this->globalCustomers[$customerId])) {
				$this->globalCustomers[$customerId] = $this->customerFactory->create()->load($customerId);
			}

			return $this->globalCustomers[$customerId];
		} catch(\Exception $e) {
			return false;
		}

	}

	public function setImage($image)
	{
		$this->image = $image;
		return $this;
	}

	public function setCustomers($customers)
	{
		$this->customers = $customers;
		return $this;
	}

	public function setCustomerGroups($customerGroups)
	{
		$this->customerGroups = $customerGroups;
		return $this;
	}

    public function setDevicePerBatch($devicePerBatch)
    {
        $this->devicePerBatch = $devicePerBatch;
        return $this;
    }

    public function setCurrentPageValue($currentPageValue)
    {
        $this->currentPageValue = $currentPageValue;
        return $this;
    }

    public function setAllDevice($allDevices)
    {
        $this->allDevices = $allDevices;
        return $this;
    }

	public function send()
	{
     	if ($this->customers || $this->customerGroups || $this->allDevices) {
     		$this->setDeviceTokens();

     		$variableFound = false;
			foreach ($this->getReplaceableVariables() as $variable) {
				if (strpos($this->message, $variable) !== FALSE) {
					$variableFound = true;
				}
				if (strpos($this->title, $variable) !== FALSE) {
					$variableFound = true;
				}
			}
			if (!$variableFound) {
					$fcmResponseIOS = $this->callFirebaseApiIOS(array_keys($this->tokens['ios']));
					$fcmResponseAndroid = $this->callFirebaseApiAndroid(array_keys($this->tokens['android']));

			} else {
				$fcmResponseAndroid = [];
				$fcmResponseIOS = [];
				foreach (['android', 'ios'] as $platform) {
					if($platform == '1'){
                        $platform = 'android';
                    }
					if ($this->tokens[$platform]) {
						foreach ($this->tokens[$platform] as $_token => $customerId) {
							if ( $platform === 'android') {
								$fcmResponseAndroid[] = $this->callFirebaseApiAndroid($_token, $customerId);
							}
							if ($platform === 'ios') {
								$fcmResponseIOS[] = $this->callFirebaseApiIOS($_token, $customerId);
							}

						}
					}
				}
			}

	    }else{

	    	$variableFound = false;
			foreach ($this->getReplaceableVariables() as $variable) {
				if (strpos($this->message, $variable) !== FALSE) {
					$variableFound = true;
				}
				if (strpos($this->title, $variable) !== FALSE) {
					$variableFound = true;
				}
			}
			if (!$variableFound) {

					$this->callFirbaseApiTopic();

			} else {

				 // $this->callFirbaseApiTopic($customerId);
				 $this->callFirbaseApiTopic();
			}

	    }


	}

	public function setDeviceTokens()
	{
		$customerIds = [];
		if ($this->customers) {
			$customers = $this->customers;
			$customers = explode(',', $customers);
			$customers = array_values(array_filter(array_map('trim', $customers)));

			if ($customers) {
				$customerCollection = $this->customerCollectionFactory->create();
				$customerCollection->addAttributeToSelect('entity_id')
					->addAttributeToFilter('email', ['in' => $customers]);

				if ($customerCollection->getSize() > 0) {
					foreach ($customerCollection as $item) {
						$customerIds[] = $item->getId();
					}
				}
			}
		}

		if ($this->customerGroups) {
			$customerGroups = $this->customerGroups;
			$customerGroups = explode(',', $customerGroups);
			$customerGroups = array_values(array_filter(array_map('trim', $customerGroups)));

			if ($customerGroups) {
				$customerCollection = $this->customerCollectionFactory->create();
				$customerCollection->addAttributeToSelect('entity_id')
					->addAttributeToFilter('group_id', ['in' => $customerGroups]);

				$customerCollection->getSelect()->join(
					['ktpl_devicetokens' => $customerCollection->getResource()->getTable('ktpl_devicetokens')],
                	'ktpl_devicetokens.customer_id = e.entity_id',
                	NULL
            	)->group('e.entity_id');

				if ($customerCollection->getSize() > 0) {
					foreach ($customerCollection as $item) {
						$customerIds[] = $item->getId();
					}
				}
			}
		}

		$deviceCollection = $this->deviceCollectionFactory->create();
		if ($this->customers || $this->customerGroups) {
			$customerIds = array_values(array_unique($customerIds));
			if ($customerIds) {
				$deviceCollection->addFieldToFilter('customer_id', ['in' => $customerIds]);
			} else {
				return false;
			}
		}

		$deviceCollection->addFieldToFilter('main_table.status', '1');
		$deviceCollection->getSelect()->group('device_token');
        //pagination

        if ($this->allDevices) {
            $deviceCollection->getSelect()->limit($this->devicePerBatch, $this->devicePerBatch * $this->currentPageValue);
        }

		if ($deviceCollection->count() > 0) {
			foreach ($deviceCollection as $item) {
				$_token = $item->getDeviceToken();
				switch (strtolower($item->getDeviceType())) {
					case 'android':
						$this->tokens['android'][$_token] = $item->getCustomerId();
						break;
					case 'ios':
						$this->tokens['ios'][$_token] = $item->getCustomerId();
						break;
					case '1':
						$this->tokens['android'][$_token] = $item->getCustomerId();
						break;
					default:
						$this->tokens['android'][$_token] = $item->getCustomerId();
						break;

				}
			}
		}
	}

	public function callFirebaseApiAndroid($tokens, $customerId = null)
	{
		$deviceType = "Android";
		return $this->callFirebaseApiStandard($tokens, $customerId, $deviceType);
	}

	public function callFirebaseApiIOS($tokens, $customerId = null)
	{
		$deviceType = "IOS";
		return $this->callFirebaseApiStandard($tokens, $customerId, $deviceType);
	}

	public function callFirebaseApiStandard($alltokens, $customerId = null, $deviceType)
	{

			if($alltokens){

				$title = $this->title;
	            $message = $this->message;
	            $type = $this->type;
	            $typeId = $this->typeId;

	            if ($customerId) {
	                $title = $this->parseMessageVariables($customerId, $this->title);
	                $message = $this->parseMessageVariables($customerId, $this->message);
	            }
	            $url = 'https://fcm.googleapis.com/fcm/send';
	            $api_key = $this->scopeConfig->getValue('pushnotification/firebase/server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


	            if (is_string($alltokens)) {
	                $alltokens = [$alltokens];
	            }

	            $tokensChunk = array_chunk($alltokens,995);
	            $alldata = [];

				foreach ($tokensChunk as $key => $tokens) {

					$data = ['request' => [], 'response' => []];
					$data['request'] = [
						'tokens' => $tokens,
						'title' => $title,
						'message' => $message,
						'image' => $this->image
					];


				    if($deviceType == "IOS" || $deviceType == "Android" || $deviceType == "1" || $deviceType == 1)
				    {
					    $fields = [
					        'registration_ids' => $tokens,
					        'notification' => [
					        	'title' => $title,
					            "body" => $message
					        ],
					        'data' =>[
								'type'=> $type,
								'id'=> $typeId
							]
				    	];
				    }
				    if (strpos($message, '{{cart_id}}') !== false) {
					    $message = str_replace("{{cart_id}}",$typeId,$message);
					}
				    /*if($deviceType == "Android" || $deviceType == "1" || $deviceType == 1)
				    {
				    	$fields = [
					        'registration_ids' => $tokens,
					        'data' =>[
					        	'title' => $title,
					            "body" => $message,
								'type'=> $type,
								'id'=> $typeId
							]
				    	];
				    }*/

				    if ($this->image) {
				    	$fields['notification']['image'] = $this->image;
				    }

				    $headers = array(
				        'Content-Type:application/json',
				        'Authorization:key='.$api_key
				    );

				    $ch = curl_init();
				    curl_setopt($ch, CURLOPT_URL, $url);
				    curl_setopt($ch, CURLOPT_POST, true);
				    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				    $result = curl_exec($ch);

				    if ($result === FALSE) {
				        $data['response'] = 'FCM Send Error: ' . curl_error($ch);
				    } else {
				    	$data['response'] = $result;
				    }
				    curl_close($ch);
	                $alldata[] = $data;
				}
                var_dump($alldata);
            return $alldata;

			}

		return false;
	}

	public function callFirbaseApiTopic($customerId = null){

		$title = $this->title;
        $message = $this->message;
        $type = $this->type;
        $typeId = $this->typeId;

        $sendToTopic = $this->scopeConfig->getValue('pushnotification/promotional_notification_topic/send_to_topic', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            /* ------------- GET AUTH 2.0 ACCESS TOKEN */
            $service_account_email = "firebase-adminsdk-sxhww@tamata-go.iam.gserviceaccount.com";
			$private_key = "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDoeeUFE6Vi0c+O\nYgSNYRRPEF7n0mprb3xjgIrAsShwhImR2LlZU8EAaBFhGOs6d4YbzDtYJjzNb4bU\nFv631quuoV6MwSHgPCuEBUP/l5j92REeNdQCjqnt9qIM/dzGAH9ozZ4Njgu83L5C\nweJysjLa/WSE/SwAR1O1DTJBuIbE+EVXzQtI0U9zpxERkk11EGAB/cCAkoybvYkd\n2OSNDzULXv6Ir37/I8znodovXzmL/5zKEdIt2fHPQPUlmeSI1cT0Dz+CwXkJN5kg\n45koJ1MeTwwBuPvfdn1HzSqdNEA+LEfJDFPSEFa4iAvWHBXB+7kGjlJmvFE9kQ9z\n0M9/WfPLAgMBAAECggEAGJkxRPi/srWdnyS685S2l+kVCbWHgiexQzwKMnpsW3+C\nqPaEIjINBXR9hkcjOLQF8jIJg3nETD6FRzLJr/0cfGVhwkX7RiVHu3ftKoHJc+7A\nc3BhpwWecGn82qAP+Ll7wcSj+S4zCsBWt2yZf+ZgIDDYroO1YLAraMhlLXQPeo7Q\n5HhRmbiTBqcRMb1kvbFuSVch8mrsbnF1UPcxbEgRVgP50khYXH4xwsG2pDROZRz4\nLsN79rEqoTtpFRlk5pZ6r5wzV1ncnJYnNhMX1x21nVUiGEXA7PN+ty1m37DoUpVQ\nuhyZ6+gaDaYgaZFxT8DbiE/9aX1QRUtst27xXLz1NQKBgQD/tSMHUjwZJra0vR1x\nnC+WtVAwSEL5LjTepqApm3mpHXuKtr6kmkfzuRoBkSLP9QP0idQU4mtOY9uvQpM8\nmFDApP8Wl2QpYsVHEqb7DRgA2eM3XrlrgUejiC0idXZ3dfkXAW6Oa9DdxHNAA3r0\n9wHhEMbC6osLRD1TZfigLmzRdwKBgQDovfTTLRWLYrKtoMKpamEqXMbXPwQa3L/+\no5Ot1tHdSnuhN+FvXAiZx2LJD4kosWIzjFIXN7a5oPXiGaU2xYQQumJ3wOJXqb2o\nJ+H9Zhwo7/I/YqOrqQ2n+m3qUq8JF2/aGgVlP8ea2jIkkFBI2+CsmE4h9BvL7Dub\n55x1/J1lTQKBgFxzklGgUuhJDf/07ENaU/5qWa7LQaO0KBtkJT5F30vcxAEYJ399\n5IvjHdr5LZwAm0h72LkgT+nMwu3v5Soga/nq7euuGuG3u1oCDWtxhX8xSCyJzAJp\ngIxIvQDbdVSly8ScmOtXYfH8iCrZ10fgUsab+TEZ+eG972pK0QShJGjrAoGAeiA7\nGJiepn5Kzer+WlGU3NrEB+xsJgBRyrdg5aZEhz7vMoCVDY8YgEDsqB471ZuGsQ/f\nf4lfm3ZHr7XPSLdJ/lEfHEGdQ2yxNUyAPCxZeG24CuSih4/0t4EkLgHnEPk4WxSu\nYhuejLYT+7WyOxkNguOElDJ4Z3/1I3DcW9kLaj0CgYEAnOVNio0Gez2x3t6+2X5b\nEzG7vMIHn0mpLAzd89OCW5KFtTa/Tegp2rMGgtXJ3TBqprBRkb95U+kLAJKstGvM\nfH863Oq2xva6VEqa6FIy8faFuqZFcgKaFSjNjkU4fCifMoozOybDkBHEXzwHc0jC\nm1XrH5cQlO8YdiQ8tlQskSE=\n-----END PRIVATE KEY-----\n";

				$now_seconds = time();

				// create a JWT sign

				$payload = array(
				    "iss" => $service_account_email,
				    "scope" => "https://www.googleapis.com/auth/cloud-platform",
				    "aud" => "https://oauth2.googleapis.com/token",
				    "iat" => $now_seconds,
				    "exp" => $now_seconds+(60*60)  // Maximum expiration time is one hour
			  	);

			$jwtSign = JWT::encode($payload, $private_key, "RS256");


			$authTokenHeader =  array('Content-Type: application/x-www-form-urlencoded', 'Content-Length: 0');
			$authCh = curl_init();
		    curl_setopt($authCh, CURLOPT_URL,"https://oauth2.googleapis.com/token?grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=".$jwtSign);
		    curl_setopt($authCh, CURLOPT_POST, true);
		    curl_setopt($authCh, CURLOPT_HTTPHEADER, $authTokenHeader);
		    curl_setopt($authCh, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($authCh, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($authCh, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($authCh, CURLOPT_VERBOSE, true);
		    $authBearerTokenResult = curl_exec($authCh);

		        if ($authBearerTokenResult === FALSE) {
			        $authCallResponse = curl_error($ch);
			    } else {
			    	$authCallResponse = $authBearerTokenResult;
			    }

			curl_close($authCh);

		   	$auth2TokenArray = json_decode($authCallResponse,true);

		   	if($auth2TokenArray['access_token']){
		   		$authBearerToken = $auth2TokenArray['access_token'];
		   	}
            /* GET AUTH 2.0 ACCESS TOKEN -------------- */


            /* ------------ Send messages to topics */
            $sendToTopicUrl = "https://fcm.googleapis.com/v1/projects/tamata-go/messages:send";

		    $headers = array('Content-Type: application/json',
						'Authorization: Bearer '.$authBearerToken);

		    $topicMessage =  [
				        'message' => [
				        	'topic' =>  $sendToTopic,
				        	'notification' => [
					        	'title' => $title,
					            "body" => $message,
					            "image" => $this->image
				        	],
				        	'data' =>[
				        		'title' => $title,
					            "body" => $message,
								'type'=> $type,
								'id'=> $typeId
							]
				        ]
			    	];

			$allTopicSentdata = [];

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $sendToTopicUrl);
		    curl_setopt($ch, CURLOPT_POST, true);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($topicMessage));
		    $topicSentResult = curl_exec($ch);

		    if ($topicSentResult === FALSE) {
		        $topicSendData['response'] = 'FCM Send Error: ' . curl_error($ch);
		    } else {
		    	$topicSendData['response'] = $topicSentResult;
		    }
		    curl_close($ch);
            $allTopicSentdata[] = $topicSendData;

            return $allTopicSentdata;
            /* Send messages to topics ------------------ */

	}

	public function verifyDevicesIds(){

		$count = $this->scopeConfig->getValue('pushnotification/device_token_check/device_token_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$deviceCollection = $this->deviceCollectionFactory->create();
		$deviceCollection->getSelect()->order('main_table.updated_at', 'asc')->limit($count);
		$api_key = $this->scopeConfig->getValue('pushnotification/firebase/server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		foreach ($deviceCollection as $item) {
			$_token = $item->getDeviceToken();

			// Verify With Firebase
			$url = 'https://iid.googleapis.com/iid/info/'.$_token;

			$headers = array(
			        'Content-Type:application/json',
			        'Authorization:key='.$api_key
			    );
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			if ($result === FALSE) {
			    $data['response'] = 'FCM Send Error: ' . curl_error($ch);
			} else {
				$data = json_decode($result,true);
			}
			curl_close($ch);
			$alldata = $data;
			if(!isset($alldata['error'])){
				$item->setUpdatedAt(date('Y-m-d H:i:s'));
            	$item->save();
			}
			else{
				$item->delete();
			}
		}
	}
}
