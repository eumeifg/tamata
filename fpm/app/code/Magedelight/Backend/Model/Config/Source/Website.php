<?php

namespace Magedelight\Backend\Model\Config\Source;

class Website implements \Magento\Framework\Option\ArrayInterface
{

    protected $logger;

    protected $storeManager;

    protected $helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Backend\Helper\Mddata $helper
    ) {
        $this->_logger = $logger;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        $websites = $this->_storeManager->getWebsites();
        $websiteUrls = [];
        foreach ($websites as $website) {
            foreach ($website->getStores() as $store) {
                $wedsiteId = $website->getId();
                $storeObj = $this->_storeManager->getStore($store);
                $storeId = $storeObj->getId();
                $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $parsedUrl = parse_url($url);
                $parsedUrl = str_replace(['www.', 'http://', 'https://'], '', $parsedUrl['host']);
                if (!in_array($parsedUrl, $websiteUrls)) {
                    $websiteUrls[] = $parsedUrl;
                }
            }
        }
        
        $mappedDomainsArr = $this->helper->getAllowedDomainsCollection();
        $responseArray = [];
        
        try {
            if (!empty($mappedDomainsArr)) {
                $i =0;

                foreach ($websiteUrls as $key => $domain) {
                    $devPart = strchr($domain, '.', true);
                    $maindomain = str_replace($devPart.'.', '', $domain);
                    
                    if (!in_array($domain, $responseArray) &&
                        (
                            in_array($domain, $mappedDomainsArr['domains']) ||
                            in_array($maindomain, $mappedDomainsArr['domains'])
                        )
                    ) {
                        $responseArray[] = ['value' => $domain, "label" => $domain];
                        $i++;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }

        return $responseArray;
    }
}
