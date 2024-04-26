<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Helper;

use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Runner\Exception;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    public $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $blacklistFactory;

    /**
     * @var string
     */
    protected $temp_id;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\EmailscheduleFactory
     */
    protected $emailScheduleFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $emailQueueFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\ReportsFactory
     */
    protected $reportsFactory;

    /**
     * @var \Magedelight\Abandonedcart\Model\RuleFactory
     */
    protected $rulesFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $senderResolver;

    /**
     * Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    protected $fireBaseFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magedelight\Abandonedcart\Model\EmailscheduleFactory $emailScheduleFactory
     * @param \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory
     * @param \Magedelight\Abandonedcart\Model\ReportsFactory $reportsFactory
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory
     * @param \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magedelight\Abandonedcart\Block\Adminhtml\UrlBuilder $urlInterface
     * @param \Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Escaper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magedelight\Abandonedcart\Model\EmailscheduleFactory $emailScheduleFactory,
        \Magedelight\Abandonedcart\Model\HistoryFactory $historyFactory,
        \Magedelight\Abandonedcart\Model\ReportsFactory $reportsFactory,
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailQueueFactory,
        \Magedelight\Abandonedcart\Model\BlacklistFactory $blacklistFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magedelight\Abandonedcart\Block\Adminhtml\UrlBuilder $urlInterface,
        \Magedelight\Abandonedcart\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        SerializerInterface $serializer,
        \Ktpl\Pushnotification\Model\FirebaseFactory $fireBaseFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->emailScheduleFactory = $emailScheduleFactory;
        $this->emailQueueFactory = $emailQueueFactory;
        $this->historyFactory = $historyFactory;
        $this->reportsFactory = $reportsFactory;
        $this->blacklistFactory = $blacklistFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->quoteCollectionFactory = $collectionFactory;
        $this->senderResolver = $senderResolver;
        $this->rulesFactory = $ruleFactory;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->frontendUrlBuilder = $urlInterface;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->_messageManager = $messageManager;
        $this->quoteFactory = $quoteFactory;
        $this->priceHelper = $priceHelper;
        $this->_escaper = $escaper;
        $this->serializer = $serializer;
        $this->fireBaseFactory = $fireBaseFactory;
        parent::__construct($context);
        
    }

    /**
     * Get System Config value
     *
     * @return boolean
     */
    public function isAbandonedcartEnabled()
    {
        $isAbandonedcartActive = $this->getConfig('abandonedcart_section/general/active');

        return $isAbandonedcartActive;
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     *
     * @param $storeId, $quoteId, $customerEmail
     * @return string
     */
    public function getAbandonedcartRestoreUrl($storeId, $quoteId, $customerEmail, $pids)
    {
        $pids = implode(',', $pids);
        return $this->frontendUrlBuilder->getUrl(
            'abandonedcart/index/restore',
            $storeId,
            ['quote_id' => $quoteId, 'email' => $customerEmail, 'pids' => $pids]
        );
    }

    /**
     *
     * @param $storeId, $customerEmail
     * @return string
     */
    public function getUnsubscribeUrl($storeId, $customerEmail)
    {
        $url = $this->frontendUrlBuilder->getUrl(
            'abandonedcart/index/unsubscribe',
            $storeId,
            ['email' => $customerEmail, 'store_id' => $storeId]
        );

        return $url;
    }

    /**
     * [generateTemplate description]  with template file and tempaltes variables values
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email']);

        //Unable to addBcc in earlier version, so sending separate mail for BCC
        if (!empty($this->getSendCopyTo())) {
            $template->addBcc($this->getSendCopyTo());
        }

        return $this;
    }

    /**
     *
     * @param $image, $width, $height
     * @return string
     */
    public function resize($image, $width = null, $height = null)
    {
        if (!empty($image)) {
            $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                    ->getAbsolutePath('catalog/product').$image;

            $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                    ->getAbsolutePath('catalog/product/abandonedcart/'.$width).$image;
            //create image factory...
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize($width, $height);
            //destination folder
            $destination = $imageResized;
            //save image
            $imageResize->save($destination);

            $resizedURL = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                'catalog/product/abandonedcart/'.$width.$image;

            return $resizedURL;
        } else {
            return true;
        }
    }

    /**
     * [sendInvoicedOrderEmail description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    /* abandonedcart send mail method*/
    public function sendAbandonedCartMail($templateId, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        if (!$this->isAbandonedcartEnabled()) {
            $this->_messageManager->addError(__('Please enable extension to send Abandoned Cart Mails'));
            $this->_logger->info(__('Please enable extension to send Abandoned Cart Mails'));

            return false;
        }
        $this->temp_id = $templateId;
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();

        return true;
    }

    /**
     *
     * @param $config_path
     * @return string|int|array
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $storeId
     *
     * @return array
     */
    public function getBlacklistArray($websiteId, $email)
    {
        if (!is_array($websiteId)) {
            $websiteId = explode(',', $websiteId);
        }

        $blacklistCollection = $this->blacklistFactory->create()->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('email', $email);
        $blacklistArr = [];
        foreach ($blacklistCollection as $blacklist) {
            $blacklistArr[] = $blacklist->getEmail();
        }

        return $blacklistArr;
    }

    /**
     * @param $storeId
     *
     * @return array
     */
    public function getBlacklistArrayByStoreId($storeId)
    {
        $websiteId = $this->getWebsiteByStoreId($storeId);

        $blacklistCollection = $this->blacklistFactory->create()->getCollection()
            ->addFieldToFilter('website_id', ['in', $websiteId]);
        $blacklistArr = [];
        foreach ($blacklistCollection as $blacklist) {
            if (!in_array($blacklist->getEmail(), $blacklistArr)) {
                $blacklistArr[] = $blacklist->getEmail();
            }
        }

        return $blacklistArr;
    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function getAbandonedcartTime()
    {
        $abandonedTime = $this->getConfig('abandonedcart_section/general/abandonedcart_time');
        if (empty($abandonedTime)) {
            $abandonedTime = 30;
        }

        return $abandonedTime;
    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function getKeepHistoryTime()
    {
        $historyTime = $this->getConfig('abandonedcart_section/general/keep_history_time');

        if (empty($historyTime)) {
            $historyTime = 60;
        }

        return $historyTime;
    }

    /**
     * Get system config set return path
     *
     * @return boolean
     */
    public function getIfRemoveHistory()
    {
        return $this->getConfig('abandonedcart_section/general/remove_history');
    }

    /**
     * Get system config set return path
     *
     * @return boolean
     */
    public function getIfGuestUsersToSend()
    {
        return $this->getConfig('abandonedcart_section/general/send_email_to_guest');
    }

    /**
     * Get system config set return path
     *
     * @return string
     */
    public function getTestEmailReceiver()
    {
        return $this->getConfig('abandonedcart_section/test_email/recipient_email');
    }

    /**
     * Get system config set return path
     *
     * @return string
     */
    public function getEmailSender()
    {
        return $this->getConfig('abandonedcart_section/general/identity');
    }
    /**
     * Get system config set return path
     *
     * @return arrau
     */
    public function getSenderData()
    {
        $sender = $this->getEmailSender();
        $data = $this->senderResolver->resolve($sender);

        return $data;
    }

    /**
     * Get system config set return path
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->getConfig('abandonedcart_section/test_email/active');
    }

    /**
     * Get system config set return path
     *
     * @return string
     */
    public function getSendCopyTo()
    {
        return $this->getConfig('abandonedcart_section/general/send_copy_to');
    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function isSetToStopAfterVisit()
    {
        return $this->getConfig('abandonedcart_section/general/stop_after_visiting');
    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function isRuleApplyMultipleTime()
    {
        return $this->getConfig('abandonedcart_section/general/apply_rule_more_one');
    }

    /**
     * Get system config set return path
     *
     * @return boolean
     */
    public function isUnsubscribeLinkEnabledToAdd()
    {
        return $this->getConfig('abandonedcart_section/general/add_unsubscribe_link');
    }

    /**
     * Get system config set return path
     *
     * @return int
     */
    public function getApplyRulesNumberOfTimes()
    {
        return $this->getConfig('abandonedcart_section/general/apply_rule_number');
    }

    /**
     *
     * @param $config_path
     * @return string|boolean|int|array
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     *
     * @param $ruleId
     * @return object
     */
    public function getScheduleDataByAbandonedCartRuleId($ruleId)
    {
        $scheduler = $this->emailScheduleFactory->create()->getCollection()
            ->addFieldToFilter('abandoned_cart_rule_id', $ruleId);

        return $scheduler;
    }

    /**
     *
     * @return array
     */
    public function getProcessedQuotesArray()
    {
        $daysToCheck = '-'.($this->getAbandonedCartDelayTime() + 1).' day';
        $processedQuotesHistory = $this->historyFactory->create()->getCollection()
            ->addFieldToFilter('is_ordered', 0)
            ->addFieldToFilter(
                'status',
                ["neq" => \Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::CART_UPDATED]
            )
            ->addFieldToFilter('creation_time', [
                'from'     => strtotime($daysToCheck, time()),
                'to'       => time(),
                'datetime' => true
            ]);

        $processedQuotes = [];

        foreach ($processedQuotesHistory as $history) {
            if (!in_array($history->getQuoteId(), $processedQuotes) && !empty($history->getQuoteId())) {
                $processedQuotes[] = $history->getQuoteId();
            }
        }
        $scheduledQuotes = $this->emailQueueFactory->create()->getCollection();
        foreach ($scheduledQuotes as $queue) {
            if (!in_array($queue->getQuoteId(), $processedQuotes) && !empty($queue->getQuoteId())) {
                $processedQuotes[] = $queue->getQuoteId();
            }
        }
        /*echo '<pre>';
        print_r($processedQuotes);
        die();*/
        return $processedQuotes;
    }

    /**
     *
     * @return boolean
     */
    public function sendTestMail()
    {
        $senderData = $this->getSenderData();
        /* Receiver Detail  */
        $receiverInfo = [
            'name'  => 'Veronica Costello',
            'email' => ($this->getTestEmailReceiver() !="")?$this->getTestEmailReceiver():"roni_cost@example.com",
        ];

        /* Sender Detail  */
        $senderInfo = [
            'name'  => $senderData['name'],
            'email' => $senderData['email'],
        ];

        $productsCollection = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->getCollection()
            ->addAttributeToFilter('status', ['in' => [1]])
            ->setPageSize(20);

        $productsCollection->getSelect()->orderRand();
        $productsArr = [];
        $imagewidth   = $this->getImageWidthInEmail();
        $imageheight  = $this->getImageHeightInEmail();
        $total = 0;
        $i = 0;
        $formattedPrice = $this->priceHelper;
        foreach ($productsCollection as $product) {
            $_product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->load($product->getId());
            $productsArr[] = [
                'product_name' => $_product->getName(),
                'product_id'   => $_product->getId(),
                'product_url'  => $_product->getProductUrl(),
                'price'        => $formattedPrice->currency($_product->getPrice(), true, false),
                'qty'          => 1,
                'thumbnail_url'=> $this->resize($_product->getThumbnail(), $imagewidth, $imageheight),
            ];
            $total += $_product->getPrice();

            if ($i == 3) {
                break;
            }
            $i++;
        }

        $emailTemplateVariables = [];
        $emailTempVariables['name']             = $receiverInfo['name'];
        $emailTempVariables['email']            = $receiverInfo['email'];
        $emailTempVariables['test_email']       = 1;
        $emailTempVariables['items']            = $productsArr;
        $emailTempVariables['subtotal']         = $formattedPrice->currency($total, true, false);
        $emailTempVariables['restore_url']      = $this->frontendUrlBuilder->getUrl(
            'abandonedcart/index/restore',
            null,
            null
        );
        $emailTempVariables['quote_id']         = '';
        $emailTempVariables['queue_id']         = '';

        if ($this->isUnsubscribeLinkEnabledToAdd()) {
            $emailTempVariables['unsubscribe_url']  = $this->frontendUrlBuilder->getUrl(
                'abandonedcart/index/unsubscribe',
                null,
                null
            );
        }

        $emailTempVariables['abandonedcart_promo_code']  = 'FSDJK23AFD4F';

        $senderData = $this->getSenderData();

        $this->inlineTranslation->suspend();

        $transport = $this->_transportBuilder->setTemplateIdentifier('md_abandonedcart_first_mail')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars($emailTempVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'])
            ->setReplyTo($senderInfo['email'])
            ->getTransport();
        try {
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param serialzed data
     * @return unserilized array
     */
    public function getUnserializeData($serializedData)
    {
        $unserializeData =  $this->serializer->unserialize($serializedData);

        return $unserializeData;
    }

    /**
     *
     * @param $quoteId
     * @return array
     */
    public function getQuoteItems($quoteId)
    {
        $imagewidth   = $this->getImageWidthInEmail();
        $imageheight  = $this->getImageHeightInEmail();
        $quote = $this->quoteFactory->create()->load($quoteId);
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $variables['quote_items'][$item->getId()]   =
                [
                    'product_name' => $item->getProduct()->getName(),
                    'product_id'   => $item->getProduct()->getId(),
                    'product_url'  => $item->getProduct()->getProductUrl(),
                    'product_sku'  => $item->getProduct()->getSku(),
                    'product_type' => $item->getProduct()->getTypeId(),
                    'price'        => $this->priceHelper->currency($item->getPrice(), true, false),
                    'qty'          => $item->getQty(),
                    'thumbnail_url'=> $this->resize($item->getProduct()->getThumbnail(), $imagewidth, $imageheight),
                ];
        }

        $variables['subtotal'] = $this->priceHelper->currency($quote->getSubtotal(), true, false);

        return $variables;
    }

    /**
     *
     * @param $queue
     * @return boolean
     */
    public function prepareAndSendAbandonedcartMail($queue)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/prepareAndSendAbandonedcartMail.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);


        $queueObj = $this->emailQueueFactory->create()->load($queue->getAbandonedcartEmailId());
        $variables =  $this->serializer->unserialize($queue->getVariables());

        $emailTemplateVariables = [];
        $emailTempVariables['name']             = $variables['customer_name'];
        $emailTempVariables['email']            = $queue->getEmail();
        $emailTempVariables['items']            = $variables['quote_items'];
        $emailTempVariables['subtotal']         = $variables['quote_subtotal'];
        $emailTempVariables['quote_id']         = $queue->getQuoteId();
        $emailTempVariables['queue_id']         = $queue->getAbandonedcartEmailId();

        if (strpos($variables['restore_url'], '?') !== false) {
            $emailTempVariables['restore_url']  =$variables['restore_url'].'&queue_id='.$emailTempVariables['queue_id'];
        } else {
            $emailTempVariables['restore_url']  =$variables['restore_url'].'?queue_id='.$emailTempVariables['queue_id'];
        }

        if ($this->isUnsubscribeLinkEnabledToAdd()) {
            $emailTempVariables['unsubscribe_url']  = $variables['unsubscribe_url'];
        }
        $conditions = explode(',', $variables['cancel_condition']);
        $isAnyOutStock = false;
        $isAllOutStock = true;
        if (isset($emailTempVariables['items'])) {
            foreach ($emailTempVariables['items'] as $product) {
                $StockState = $this->_objectManager->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
                $isAvailable = $StockState->getStockStatus($product['product_id'])->getStockStatus();
                if (!$isAvailable) {
                    $isAnyOutStock = true;
                } else {
                    $isAllOutStock = false;
                }
            }
        }

        if (in_array(4, $conditions) && $isAllOutStock) {
            $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::ALL_OUTSTOCK);
            $this->saveToHistory($queueObj);

            return false;
        }

        if (in_array(3, $conditions) && $isAnyOutStock) {
            $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::SOME_OUTSTOCK);
            $this->saveToHistory($queueObj);

            return false;
        }

        if ($queue->getSendCoupon() && !empty($queue->getCartpriceRuleId())) {
            $couponCode = $this->createOneCoupon($queue->getCartpriceRuleId());
            $emailTempVariables['abandonedcart_promo_code']  = $couponCode;
        }

        $content = $queue->getEmailContent();
        $senderData = $this->getSenderData();

        /* Receiver Details  */
        $receiverInfo = [
            'name'  => $this->_escaper->escapeHtml($variables['customer_name']),
            'email' => $this->_escaper->escapeHtml($queue->getEmail())
        ];

        /* Sender Detail  */
        $senderInfo = [
            'name'  => $this->_escaper->escapeHtml($senderData['name']),
            'email' => $this->_escaper->escapeHtml($senderData['email'])
        ];

        /* Assign values for your template variables  */
        $sendMail = false;
        $blacklist = $this->getBlacklistArrayByStoreId($queueObj->getStoreId());

        if (!in_array($queueObj->getEmail(), $blacklist)) {
            if ($this->isSetToStopAfterVisit()) {
                $checkIfRestored = $this->getHistoryCollection()
                    ->addFieldToFilter('quote_id', $queueObj->getQuoteId())
                    ->addFieldToFilter('is_sent', 1)
                    ->getFirstItem();
                if ($checkIfRestored->getData()) {
                    return false;
                }
            }
            /*Firebase */

            
            // $logger->info("prepareAndSendAbandonedcartMail");

             $notificationEnabled = $this->scopeConfig->getValue('pushnotification/template/abandoned_carts_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

             if($notificationEnabled){

                $abandonedcartTemplate = $this->scopeConfig->getValue('pushnotification/template/abandoned_carts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $logger->info($abandonedcartTemplate);
                $message = str_replace('{{card_id}}', $queueObj->getQuoteId(), 
                                           $abandonedcartTemplate);   
                $firebase = $this->fireBaseFactory->create(); 

                $logger->info($queueObj->getQuoteId());
                $logger->info($message);
                $firebase->setType('recently')
                  ->setTypeId($queueObj->getQuoteId())
                  ->setMessage($message)
                  ->setCustomers($queue->getEmail())
                  ->send(); 
             }
               

            /* END */

            $sendMail = $this->sendAbandonedCartMail(
                $queue->getTemplateId(),
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            if ($sendMail) {
                $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::YES);
                $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::MAIL_SENT);
                $queueObj->setIsSent(true);
                $queueObj->save();
            } else {
                $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
                $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::SENDING_ERROR);
            }
        } else {
            $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::BLACKLISTED);
            $this->saveToHistory($queueObj);

            return false;
        }

        $this->saveToHistory($queueObj);
        return true;
    }

    /**
     *
     * @param $queueObj
     * @return boolean
     */
    public function saveToHistory($queueObj)
    {
        $historyObj = $this->historyFactory->create();
        $HistoryCollection=$historyObj->getCollection()
            ->addFieldToFilter('template_id', $queueObj->getTemplateId())
            ->addFieldToFilter('customer_id', $queueObj->getCustomerId())
            ->addFieldToFilter('quote_id', $queueObj->getQuoteId())
            ->getFirstItem();

        if (($HistoryCollection->getData() instanceof Countable) &&
            (is_array($HistoryCollection->getData()) ) &&
            count($HistoryCollection->getData() > 0)) {
            $historyObj->setData(
                [
                    'first_name'            => $queueObj->getFirstName(),
                    'last_name'             => $queueObj->getLastName(),
                    'email'                 => $queueObj->getEmail(),
                    'template_id'           => $queueObj->getTemplateId(),
                    'variables'             => $queueObj->getVariables(),
                    'template_code'         => $queueObj->getTemplateCode(),
                    'customer_id'           => $queueObj->getCustomerId(),
                    'email_content'         => $queueObj->getEmailContent(),
                    'schedule_id'           => $queueObj->getScheduleId(),
                    'queue_id'              => $queueObj->getAbandonedcartEmailId(),
                    'quote_id'              => $queueObj->getQuoteId(),
                    'send_coupon'           => $queueObj->getSendCoupon(),
                    'cartprice_rule_id'     => $queueObj->getCartpriceRuleId(),
                    'schedule_at'           => $queueObj->getScheduleAt(),
                    'reference_id'          => $queueObj->getReferenceId(),
                    'status'                => $queueObj->getStatus(),
                    'is_sent'               => $queueObj->getIsSent(),
                    'is_restored'           => $queueObj->getIsRestored(),
                    'is_ordered'            => $queueObj->getIsOrdered(),
                    'store_id'              => $queueObj->getStoreId(),
                ]
            );
            $historyObj->setId($HistoryCollection->getData('history_id'))->save();
            $queueObj->delete();
            return true;
        } else {
            $historyObj->setData(
                [
                    'first_name'            => $queueObj->getFirstName(),
                    'last_name'             => $queueObj->getLastName(),
                    'email'                 => $queueObj->getEmail(),
                    'template_id'           => $queueObj->getTemplateId(),
                    'variables'             => $queueObj->getVariables(),
                    'template_code'         => $queueObj->getTemplateCode(),
                    'customer_id'           => $queueObj->getCustomerId(),
                    'email_content'         => $queueObj->getEmailContent(),
                    'schedule_id'           => $queueObj->getScheduleId(),
                    'queue_id'              => $queueObj->getAbandonedcartEmailId(),
                    'quote_id'              => $queueObj->getQuoteId(),
                    'send_coupon'           => $queueObj->getSendCoupon(),
                    'cartprice_rule_id'     => $queueObj->getCartpriceRuleId(),
                    'schedule_at'           => $queueObj->getScheduleAt(),
                    'reference_id'          => $queueObj->getReferenceId(),
                    'status'                => $queueObj->getStatus(),
                    'is_sent'               => $queueObj->getIsSent(),
                    'is_restored'           => $queueObj->getIsRestored(),
                    'is_ordered'            => $queueObj->getIsOrdered(),
                    'store_id'              => $queueObj->getStoreId(),
                ]
            );
            $historyObj->save();
            $queueObj->delete();
            return true;
        }
    }

    /**
     *
     * @param $queueObj
     * @return boolean
     */
    public function saveToEmailQueueHistory($queueObj)
    {
        $historyObj = $this->historyFactory->create();
        $HistoryCollection=$historyObj->getCollection()
            ->addFieldToFilter('template_id', $queueObj->getTemplateId())
            ->addFieldToFilter('customer_id', $queueObj->getCustomerId())
            ->addFieldToFilter('quote_id', $queueObj->getQuoteId())
            ->getFirstItem();
        if (($HistoryCollection->getData() instanceof Countable) &&
            (is_array($HistoryCollection->getData()) ) &&
            count($HistoryCollection->getData() > 0)) {
            $historyObj->setData(
                [
                    'first_name' => $queueObj->getFirstName(),
                    'last_name' => $queueObj->getLastName(),
                    'email' => $queueObj->getEmail(),
                    'template_id' => $queueObj->getTemplateId(),
                    'variables' => $queueObj->getVariables(),
                    'template_code' => $queueObj->getTemplateCode(),
                    'customer_id' => $queueObj->getCustomerId(),
                    'email_content' => $queueObj->getEmailContent(),
                    'schedule_id' => $queueObj->getScheduleId(),
                    'queue_id' => $queueObj->getAbandonedcartEmailId(),
                    'quote_id' => $queueObj->getQuoteId(),
                    'send_coupon' => $queueObj->getSendCoupon(),
                    'cartprice_rule_id' => $queueObj->getCartpriceRuleId(),
                    'schedule_at' => $queueObj->getScheduleAt(),
                    'reference_id' => $queueObj->getReferenceId(),
                    'status' => 10,
                    'is_sent' => $queueObj->getIsSent(),
                    'is_restored' => $queueObj->getIsRestored(),
                    'is_ordered' => $queueObj->getIsOrdered(),
                    'store_id' => $queueObj->getStoreId(),
                ]
            );
            $historyObj->setId($HistoryCollection->getData('history_id'))->save();
            $productData = json_decode($queueObj->getVariables(), true);

            if (($productData['quote_items'] instanceof Countable) &&
                (is_array($productData['quote_items'])) &&
                count($productData['quote_items'] > 0)) {
                $reportsObj = $this->reportsFactory->create();
                foreach ($productData['quote_items'] as $key => $value) {
                    $reportsObj->setData(
                        [
                            'first_name' => $queueObj->getFirstName(),
                            'last_name' => $queueObj->getLastName(),
                            'email' => $queueObj->getEmail(),
                            'customer_id' => $queueObj->getCustomerId(),
                            'quote_id' => $queueObj->getQuoteId(),
                            'product_id' => $productData['quote_items'][$key]['product_id'],
                            'product_name' => $productData['quote_items'][$key]['product_name'],
                            'product_sku' => $productData['quote_items'][$key]['product_sku'],
                            'product_qty' => $productData['quote_items'][$key]['qty'],
                            'price' => $productData['quote_items'][$key]['price'],
                            'reference_id' => $queueObj->getReferenceId(),
                            'status' => 10,
                            'store_id' => $queueObj->getStoreId(),
                        ]
                    );
                    $reportsObj->save();
                }
            }
            return true;
        } else {
            $historyObj->setData(
                [
                    'first_name' => $queueObj->getFirstName(),
                    'last_name' => $queueObj->getLastName(),
                    'email' => $queueObj->getEmail(),
                    'template_id' => $queueObj->getTemplateId(),
                    'variables' => $queueObj->getVariables(),
                    'template_code' => $queueObj->getTemplateCode(),
                    'customer_id' => $queueObj->getCustomerId(),
                    'email_content' => $queueObj->getEmailContent(),
                    'schedule_id' => $queueObj->getScheduleId(),
                    'queue_id' => $queueObj->getAbandonedcartEmailId(),
                    'quote_id' => $queueObj->getQuoteId(),
                    'send_coupon' => $queueObj->getSendCoupon(),
                    'cartprice_rule_id' => $queueObj->getCartpriceRuleId(),
                    'schedule_at' => $queueObj->getScheduleAt(),
                    'reference_id' => $queueObj->getReferenceId(),
                    'status' => 10,
                    'is_sent' => $queueObj->getIsSent(),
                    'is_restored' => $queueObj->getIsRestored(),
                    'is_ordered' => $queueObj->getIsOrdered(),
                    'store_id' => $queueObj->getStoreId(),
                ]
            );
            $historyObj->save();
            $productData = json_decode($queueObj->getVariables(), true);
            $reportsObj = $this->reportsFactory->create();
            foreach ($productData['quote_items'] as $key => $value) {
                $reportsObj->setData(
                    [
                        'first_name' => $queueObj->getFirstName(),
                        'last_name' => $queueObj->getLastName(),
                        'email' => $queueObj->getEmail(),
                        'customer_id' => $queueObj->getCustomerId(),
                        'quote_id' => $queueObj->getQuoteId(),
                        'product_id' => $productData['quote_items'][$key]['product_id'],
                        'product_name' => $productData['quote_items'][$key]['product_name'],
                        'product_sku' => $productData['quote_items'][$key]['product_sku'],
                        'product_qty' => $productData['quote_items'][$key]['qty'],
                        'price' => $productData['quote_items'][$key]['price'],
                        'reference_id' => $queueObj->getReferenceId(),
                        'status' => 10,
                        'store_id' => $queueObj->getStoreId(),
                    ]
                );
                $reportsObj->save();
            }
            return true;
        }
    }

    /**
     * Get system config
     *
     * @param $storeId
     * @return int
     */
    public function getWebsiteByStoreId($storeId)
    {
        return $this->storeManager->getStore($storeId)->getWebsite()->getWebsiteId();
    }

    /**
     *
     * @param $ruleId
     * @return string
     */
    public function createOneCoupon($ruleId)
    {
        $ruleModel = $this->rulesFactory->create();
        $ruleModel->load($ruleId);
        try {
            $couponData = [
                'rule_id'   => $ruleId,
                'qty'       => 1,
                'length'    => '12',
                'format'    => 'alphanum',
                'prefix'    => '',
                'suffix'    => '',
                'dash'      => 0
            ];
            /** @var $generator \Magento\SalesRule\Model\Coupon\Massgenerator */
            $generator = $this->_objectManager->get(\Magento\SalesRule\Model\Coupon\Massgenerator::class);
            if (!$generator->validateData($couponData)) {
                return false;
            } else {
                $generator->setData($couponData);
                $generator->generatePool();
                $generated = $generator->getGeneratedCount();
                $codes = $generator->getGeneratedCodes();
                return $codes[0];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(Psr\Log\LoggerInterface::class)->critical($e);
            return false;
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return false;
        }
    }

    /**
     *
     * @param $history
     * @return boolean
     */
    public function prepareAndSendAbandonedcartMailFromHistory($history)
    {
         $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/prepareAndSendAbandonedcartMailFromHistory.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

        /* Assign values for template variables  */
        $variables = $this->serializer->unserialize($history->getVariables());
        $emailTemplateVariables = [];
        $emailTempVariables['name']             = $variables['customer_name'];
        $emailTempVariables['email']            = $history->getEmail();
        $emailTempVariables['items']            = $variables['quote_items'];
        $emailTempVariables['subtotal']         = $variables['quote_subtotal'];
        $emailTempVariables['quote_id']         = $history->getQuoteId();
        $emailTempVariables['history_id']       = $history->getHistoryId();

        if (strpos($variables['restore_url'], '?') !== false) {
            $emailTempVariables['restore_url']  = $variables['restore_url'].'&queue_id='.$history->getQueueId();
        } else {
            $emailTempVariables['restore_url']  = $variables['restore_url'].'?queue_id='.$history->getQueueId();
        }

        if ($this->isUnsubscribeLinkEnabledToAdd()) {
            $emailTempVariables['unsubscribe_url']  = $variables['unsubscribe_url'];
        }

        if ($history->getSendCoupon() && !empty($history->getCartpriceRuleId())) {
            $couponCode = $this->createOneCoupon($history->getCartpriceRuleId());
            $emailTempVariables['abandonedcart_promo_code']  = $couponCode;
        }

        $conditions = explode(',', $variables['cancel_condition']);
        $isAnyOutStock = false;
        $isAllOutStock = true;
        foreach ($emailTempVariables['items'] as $product) {
            $StockState = $this->_objectManager->get(\Magento\CatalogInventory\Api\StockRegistryInterface::class);
            $isAvailable = $StockState->getStockStatus($product['product_id'])->getStockStatus();
            if (!$isAvailable) {
                $isAnyOutStock = true;
            } else {
                $isAllOutStock = false;
            }
        }

        if (in_array(4, $conditions) && $isAllOutStock) {
            $history->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $history->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::ALL_OUTSTOCK);
            $history->save();
            return false;
        }

        if (in_array(3, $conditions) && $isAnyOutStock) {
            $history->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $history->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::SOME_OUTSTOCK);
            $history->save();
            return false;
        }

        $content = $history->getEmailContent();
        $senderData = $this->getSenderData();
        /* Receiver Detail  */
        $receiverInfo = [
            'name'  => $variables['customer_name'],
            'email' => $history->getEmail(),
        ];

        /* Sender Detail  */
        $senderInfo = [
            'name'  => $senderData['name'],
            'email' => $senderData['email'],
        ];

        $sendMail = false;
        $blacklist = $this->getBlacklistArrayByStoreId($history->getStoreId());
        if (!in_array($history->getEmail(), $blacklist)) {

           
            $logger->info("prepareAndSendAbandonedcartMailFromHistory");

            $notificationEnabled = $this->scopeConfig->getValue('pushnotification/template/abandoned_carts_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

             if($notificationEnabled){

                /* firebase */
                $abandonedcartTemplate = $this->scopeConfig->getValue('pushnotification/template/abandoned_carts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $logger->info($abandonedcartTemplate);
                
                $message = str_replace('{{card_id}}', '', $abandonedcartTemplate);   
                $firebase = $this->fireBaseFactory->create(); 

                $logger->info("prepareAndSendAbandonedcartMail");
                $logger->info($queueObj->getQuoteId());

                $logger->info($message);
                $firebase->setType('recently')
                  ->setTypeId($queueObj->getQuoteId())
                  ->setMessage($message)
                  ->setCustomers($history->getEmail())
                  ->send();   
                 /* firebase */
             }
            
            $sendMail = $this->sendAbandonedCartMail(
                $history->getTemplateId(),
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        } else {
            return \Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::BLACKLISTED;
        }

        if (!$sendMail) {
            $history->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $history->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::SENDING_ERROR);
            $history->save();

            return false;
        }

        $history->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::YES);
        $history->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::MAIL_SENT);
        $history->save();

        return true;
    }

    /**
     *
     * @return int
     */
    public function getAbandonedCartDelayTime()
    {
        $abandonedcartTime = $this->getAbandonedcartTime();

        $cartSetToAbandonedinDays = $abandonedcartTime/(60*24);

        $daysToCheck = 0;

        if ($cartSetToAbandonedinDays > 1) {
            $daysToCheck = (int)($cartSetToAbandonedinDays);
        }

        return $daysToCheck;
    }

    /**
     *
     * @return Collection
     */
    public function getAbandonedCartCollection()
    {
        $processedQuotes        = $this->getProcessedQuotesArray();
        $abandonedcartTime      = $this->getAbandonedcartTime();
        $ruleApplyMoreThanOne   = $this->isRuleApplyMultipleTime();
        $now = date('Y-m-d H:i:s');
        $quoteCollection  = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter('is_active', ["eq"=>1]);
        $quoteCollection->addFieldToFilter('subtotal', ["gt"=>0]);
        $quoteCollection->addFieldToFilter('customer_email', ["neq"=>'']);

        if (!$this->getIfGuestUsersToSend()) {
            $quoteCollection->addFieldToFilter('customer_id', ["neq"=>'']);
        }

        if (count($processedQuotes)) {
            $quoteCollection->addFieldToFilter('entity_id', ['nin' => $processedQuotes]);
        }

        $daysToCheck = '-'.($this->getAbandonedCartDelayTime() + 1).' day';

        $quoteCollection->addFieldToFilter('updated_at', [
            'from'     => strtotime($daysToCheck, time()),
            'to'       => time(),
            'datetime' => true
        ]);

        if ($quoteCollection->getSize() == 0) {
            $quoteCollection->addFieldToFilter('created_at', [
                'from'     => strtotime($daysToCheck, time()),
                'to'       => time(),
                'datetime' => true
            ]);
        }

        $quoteCollection->getSelect()->where("timestampdiff(MINUTE,created_at,NOW()) >= ".$abandonedcartTime);

        $quoteCollection->getSelect()->group('entity_id');

        /*echo '<pre>';
        print_r($quoteCollection->getData());
        die();*/

        return $quoteCollection;
    }

    /**
     *
     * @param $quoteData
     * @return object
     */
    public function getQuoteAddress($quoteData)
    {
        $quoteAddress = '';
        if ($quoteData->isVirtual()) {
            $quoteAddress = $quoteData->getBillingAddress();
        } else {
            $quoteAddress = $quoteData->getShippingAddress();
        }

        $qty = 0;
        foreach ($quoteAddress->getAllVisibleItems() as $item) {
            $qty += $item->getQty();
        }
        $quoteAddress->setTotalQty($qty);

        return $quoteAddress;
    }

    /**
     *
     * @return int
     */
    public function getImageWidthInEmail()
    {
        return $this->getConfig('abandonedcart_section/general/image_width');
    }

    /**
     *
     * @return int
     */
    public function getImageHeightInEmail()
    {
        return $this->getConfig('abandonedcart_section/general/image_height');
    }

    /**
     * update abandoned cart and reschedule on cart update
     * @param $quoteData
     * @param $queueObj
     *
     * @return null
     */
    public function getAbandonedCartUpdatedOnQuoteUpdate($queueObj)
    {
        $variableData = $this->getUnserializeData($queueObj->getData('variables'));
        $cancelConditions = explode(',', $variableData['cancel_condition']);

        if (in_array('1', $cancelConditions)) {

            $queueObj->setIsSent(\Magedelight\Abandonedcart\Ui\Component\Listing\Column\IsSentOptions::NO);
            $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailStatus::CART_UPDATED);
            $this->saveToHistory($queueObj);
        } else {
            $queueObj->setStatus(\Magedelight\Abandonedcart\Model\Config\Source\EmailQueueStatus::CART_UPDATED);
            $queueObj->save();
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    //@codingStandardsIgnoreStart
    public function generateAbandonedcartQueue()
    {
        if ($this->isAbandonedcartEnabled()) {
            $quoteCollectionObj = $this->getAbandonedCartCollection();
            $imagewidth   = $this->getImageWidthInEmail();
            $imageheight  = $this->getImageHeightInEmail();

            if ($quoteCollectionObj->getSize()) {
                $ruleCollection  = $this->ruleCollectionFactory->create()
                    ->addFieldToFilter('status', ["eq"=>\Magedelight\Abandonedcart\Model\Rule::STATUS_ENABLED])
                    ->setOrder('priority', 'ASC');

                $ruleData = [];
                $appliedRuleToQuotes = [];
                if ($ruleCollection->getSize()) {
                    $scheduledQuote = [];
                    foreach ($ruleCollection as $rule) {
                        $abandonedCartRuleId = $rule->getData('abandoned_cart_rule_id');
                        $schedulerCollection = $this->getScheduleDataByAbandonedCartRuleId($abandonedCartRuleId);
                        if (!count($schedulerCollection)) {
                            continue;
                        }
                        $ruleStoreidsArr = explode(',', $rule->getStoreIds());
                        $ruleData[$rule->getData('abandoned_cart_rule_id')] = $rule->getData();
                        $ruleData[$rule->getData('abandoned_cart_rule_id')]['customer_groups'] =
                            explode(",", $ruleData[$rule->getData('abandoned_cart_rule_id')]['customers_group_ids']);

                        $allowedGroups = explode(',', $rule->getCustomersGroupIds());
                        foreach ($quoteCollectionObj as $quoteData) {
                            if (!in_array('0', $ruleStoreidsArr) && !in_array(
                                    $quoteData->getStoreId(),
                                    $ruleStoreidsArr
                                )) {
                                continue;
                            }
                            $quoteAddress = $this->getQuoteAddress($quoteData);
                            if (!empty($quoteData->getCustomerId())) {
                                $customerObj = $this->_objectManager->create(\Magento\Customer\Model\Customer::class)->
                                load($quoteData->getCustomerId());
                                $customerGroupId = $customerObj->getGroupId();

                                $firstname = $customerObj->getFirstname();
                                $lastname = $customerObj->getLastname();
                            } else {
                                $firstname = $quoteAddress->getFirstname() ? $quoteAddress->getFirstname() :__('Guest');
                                $lastname = $quoteAddress->getLastname() ? $quoteAddress->getLastname() :'';
                                $customerGroupId = $quoteData->getCustomerGroupId();
                            }

                            if (!in_array($customerGroupId, $allowedGroups)) {
                                continue;
                            }
                            $blacklist = $this->getBlacklistArrayByStoreId($quoteData->getStoreId());
                            if (!in_array($quoteData->getCustomerEmail(), $blacklist)) {
                                if ($rule->getConditions()->validate($quoteAddress)) {
                                    $items = $quoteData->getAllVisibleItems();
                                    $itemsTobeAddedInMail = [];
                                    $pIds = [];
                                    $pNames=[];
                                    $pSku=[];
                                    $variables = [];
                                    foreach ($items as $item) {
                                        if ($rule->getActions()->validate($item)) {
                                            $itemsTobeAddedInMail[] = $item->getId();
                                            $pIds[] = $item->getProduct()->getId();
                                            $pNames[] = $item->getProduct()->getName();
                                            $pSku[] = $item->getProduct()->getSku();
                                            $variables['quote_items'][$item->getId()] =
                                                [
                                                    'product_name' => $item->getProduct()->getName(),
                                                    'product_sku' => $item->getProduct()->getSku(),
                                                    'product_id' => $item->getProduct()->getId(),
                                                    'product_url' => $item->getProduct()->getProductUrl(),
                                                    'product_type' => $item->getProduct()->getTypeId(),
                                                    'price' => $this->priceHelper->currency(
                                                        $item->getPrice(),
                                                        true,
                                                        false
                                                    ),
                                                    'qty' => $item->getQty(),
                                                    'thumbnail_url' => $this->resize(
                                                        $item->getProduct()->getThumbnail(),
                                                        $imagewidth,
                                                        $imageheight
                                                    ),
                                                ];
                                        }
                                    }
                                    $customerFirstname = $firstname;
                                    $customerLastname = $lastname;
                                    $variables['customer_name'] = $customerFirstname . ' ' . $customerLastname;
                                    $variables['quote_subtotal'] = $this->priceHelper->currency(
                                        $quoteData->getSubtotal(),
                                        true,
                                        false
                                    );
                                    $variables['unsubscribe_url'] = $this->getUnsubscribeUrl(
                                        $quoteData->getStoreId(),
                                        $quoteData->getCustomerEmail()
                                    );
                                    $variables['restore_url'] = $this->getAbandonedcartRestoreUrl(
                                        $quoteData->getStoreId(),
                                        $quoteData->getId(),
                                        $quoteData->getCustomerEmail(),
                                        $pIds
                                    );
                                    $variables['cancel_condition'] = $rule->getCancelCondition();
                                    $serializedVariables = $this->serializer->serialize($variables);
                                    if (count($itemsTobeAddedInMail) > 0) {
                                        $finalItemIdsToAbandoned = implode(',', $itemsTobeAddedInMail);
                                        $finalPIds = implode(',', $pIds);
                                        $finalPNames = implode(',', $pNames);
                                        $finalSku = implode(',', $pSku);
                                        foreach ($schedulerCollection as $scheduler) {
                                            $scheduleAfter = "+" . $scheduler
                                                    ->getScheduleHours() . " hour +" .
                                                $scheduler->getScheduleMinute() . " minutes" .
                                                $scheduler->getScheduleSec() . " seconds";
                                            $scheduleAt = date('Y-m-d H:i:s', strtotime(
                                                $scheduleAfter,
                                                strtotime(date('Y-m-d H:i:s'))
                                            ));
                                            $queue = $this->_objectManager
                                                ->create(\Magedelight\Abandonedcart\Model\EmailQueue::class);
                                            $queue->setData(
                                                [
                                                    'first_name' => $firstname,
                                                    'last_name' => $lastname,
                                                    'email' => $quoteData->getCustomerEmail(),
                                                    'customer_id' => $quoteData->getCustomerId(),
                                                    'quote_id' => $quoteData->getId(),
                                                    'quote_item_id' => $finalItemIdsToAbandoned,
                                                    'product_id' => $finalPIds,
                                                    'product_name' => $finalPNames,
                                                    'product_sku' => $finalSku,
                                                    'store_id' => $quoteData->getStoreId(),
                                                    'template_id' => $scheduler->getEmailTemplateId(),
                                                    'template_code' => $this
                                                        ->getTemplateCodeById($scheduler->getEmailTemplateId()),
                                                    'variables' => $serializedVariables,
                                                    'email_content' => $this
                                                        ->getTemplateContentById($scheduler->getEmailTemplateId()),
                                                    'schedule_id' => $scheduler->getScheduleId(),
                                                    'send_coupon' => $scheduler->getSendCoupon(),
                                                    'cartprice_rule_id' => $scheduler->getCartpriceRuleId(),
                                                    'schedule_at' => $scheduleAt,
                                                    'reference_id' => $rule->getAbandonedCartRuleId(),
                                                    'status' => 1,
                                                ]
                                            );
                                            $queue->save();
                                        }

                                    }
                                    $scheduledQuote[] = $quoteData->getId();
                                }
                            }
                        }
                    }
                    $this->getAbandonedHistoryEmailDataByQueueProcess();
                }
            }
            return true;
        } else {
            return false;
        }
    }
    //@codingStandardsIgnoreEnd

    /**
     *
     * @param $id
     * @return String|Text
     */
    public function getTemplateContentById($id)
    {
        $template = $this->_objectManager->create(\Magento\Email\Model\Template::class)->load($id);

        return $template->getTemplateText();
    }

    /**
     *
     * @param $id
     * @return String|Text
     */
    public function getTemplateCodeById($id)
    {
        $template = $this->_objectManager->create(\Magento\Email\Model\Template::class)->load($id);

        return $template->getTemplateCode();
    }

    /**
     *
     * @return collection
     */
    public function getHistoryCollection()
    {
        return $this->historyFactory->create()->getCollection();
    }

    /**
     *
     * @param $id
     * @return null
     */
    public function removeHistoryById($id)
    {
        $this->historyFactory->create()->load($id)->delete();

        return true;
    }

    public function getAbandonedHistoryEmailDataByQueueProcess()
    {

        if ($this->isAbandonedcartEnabled()) {
            $queueCollection = $this->emailQueueFactory->create()
                ->getCollection()
                ->addFieldToFilter('is_sent', 0);
            foreach ($queueCollection as $queue) {
                $this->saveToEmailQueueHistory($queue);
            }
        }
    }

    public function getUpdateQuoteItemsById($queueObj)
    {
        $pIds = [];
        $pNames=[];
        $pSku=[];
        $itemsTobeAddedInMail = [];
        $variableData =  $this->serializer->unserialize($queueObj->getvariables());
        $variables=$this->getQuoteItems($queueObj->getQuoteId());
        $variables['customer_name'] = $queueObj->getfirst_name() . ' ' . $queueObj->getlast_name();
        $variables['quote_subtotal']=$variables['subtotal'];
        $variables['unsubscribe_url'] = $variableData['unsubscribe_url'];
        $variables['restore_url'] = $variableData['restore_url'];
        $variables['cancel_condition'] = $variableData['cancel_condition'];
        $serializedVariables = $this->serializer->serialize($variables);

        $quote = $this->quoteFactory->create()->load($queueObj->getQuoteId());
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $itemsTobeAddedInMail[] = $item->getId();
            $pIds[] = $item->getProduct()->getId();
            $pNames[] = $item->getProduct()->getName();
            $pSku[] = $item->getProduct()->getSku();
            $finalItemIdsToAbandoned = implode(',', $itemsTobeAddedInMail);
        }
        $finalPIds = implode(',', $pIds);
        $finalPNames = implode(',', $pNames);
        $finalSku = implode(',', $pSku);
        $queueObj->setproduct_id($finalPIds);
        $queueObj->setproduct_name($finalPNames);
        $queueObj->setproduct_sku($finalSku);
        $queueObj->setquote_item_id($finalItemIdsToAbandoned);
        $queueObj->save();
        if (($variables['quote_items'] instanceof Countable) && (is_array($variables['quote_items']) ) && count($variables['quote_items'] > 0)) {
            //if (count($variables['quote_items'] > 0)) {
            $reportsObj = $this->reportsFactory->create();
            foreach ($variables['quote_items'] as $key => $value) {
                $reportsObj->setData(
                    [
                        'first_name' => $queueObj->getfirst_name(),
                        'last_name' => $queueObj->getlast_name(),
                        'email' => $queueObj->getemail(),
                        'customer_id' => $queueObj->getcustomer_id(),
                        'quote_id' => $queueObj->getquote_id(),
                        'product_id' => $variables['quote_items'][$key]['product_id'],
                        'product_name' => $variables['quote_items'][$key]['product_name'],
                        'product_sku' => $variables['quote_items'][$key]['product_sku'],
                        'product_qty' => $variables['quote_items'][$key]['qty'],
                        'price' => $variables['quote_items'][$key]['price'],
                        'reference_id' => $queueObj->getreference_id(),
                        'status' => 9,
                        'store_id' => $queueObj->getstore_id()
                    ]
                );
                $reportsObj->save();
            }

        }

        return $serializedVariables;
    }
}
