<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Cron;

use Magento\Framework\App\ResourceConnection;

/**
 * @author Rocket Bazaar Core Team
 * Created at 12 May, 2016 05:05:27 PM
 */
class CreateSellerAppLink
{
    const FIREBASE_WEB_API_KEY = 'open_in_app/general/firebase_web_api_key';
    const FIREBASE_DEEP_LINK_URL = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magedelight\Vendor\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * VacationScheduler constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $logger
     */

    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger,
        \Magedelight\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_resource = $resource;
        $this->_dateTime = $date;
        $this->logger = $logger;
        $this->_vendorFactory = $vendorFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    /**
     * Add products to changes list with price which depends on date
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->_getConnection();
        $currentDate = $this->_dateTime->gmtDate("Y-m-d");

        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $vendorCollection = $this->_vendorFactory->create()->getCollection()
            ->addFieldToFilter('app_link', array('null' => true));
        foreach ($vendorCollection as $vendor) {
            $vendorUrl = $baseUrl . 'microsite/products/vendor/vid/' . $vendor->getVendorId();
            $appLink = $this->createDeepLinkForSeller($vendorUrl);
            if($appLink){
                $connection->update(
                    $this->_resource->getTableName('md_vendor'),
                    [
                        'app_link' => $appLink,
                    ],
                    [
                        'vendor_id = ?' => $vendor->getVendorId()
                    ]
                );
            }
        }
    }

    protected function createDeepLinkForSeller($longUrl)
    {
        $webAPIKey = $this->_scopeConfig->getValue(self::FIREBASE_WEB_API_KEY);
        $url = self::FIREBASE_DEEP_LINK_URL . $webAPIKey;
        $data = array(
            "dynamicLinkInfo" => array(
                "domainUriPrefix" => "https://tamatago.page.link",
                "link" => $longUrl,
                "androidInfo" => array(
                    "androidPackageName" => "com.tamata.retail.app"
                ),
                "iosInfo" => array(
                    "iosBundleId" => "com.tamata.app.retail"
                )
            )
        );
        $headers = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $response = json_decode($response, true);

        if (isset($response['error'])) {
            $this->logger->error("dynamic link create web:");
            $this->logger->error($response['error']['message']);
            return false;
        } else {
            return $response['shortLink'];
        }
    }
}
