<?php

/**
* Magedelight
* Copyright (C) 2018 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category Magedelight
* @package Magedelight_SocialLogin
* @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/

namespace Magedelight\SocialLogin\Model\Config\Source;

class Website implements \Magento\Framework\Option\ArrayInterface
{ 
    protected $logger;

    protected $storeManager;

    protected $helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\SocialLogin\Helper\Mddata $helper
    )
    {
        $this->_logger = $logger;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray() {
        $websites = $this->_storeManager->getWebsites();
        $websiteUrls = array();
        foreach ($websites as $website) {
            foreach ($website->getStores() as $store) {
                $wedsiteId = $website->getId();
                $storeObj = $this->_storeManager->getStore($store);
                $storeId = $storeObj->getId();
                $url = $storeObj->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $parsedUrl = parse_url($url);
                $parsedUrl = str_replace(array('www.', 'http://', 'https://'), '', $parsedUrl['host']);
                if (!in_array($parsedUrl, $websiteUrls)) {
                    $websiteUrls[] = $parsedUrl;
                }
            }
        }

        $mappedDomainsArr = $this->helper->getAllowedDomainsCollection();
        $responseArray = array();

        try {
            if (!empty($mappedDomainsArr)) {
                $i = 0;

                foreach ($websiteUrls as $key => $domain) {
                    $devPart = strchr($domain, '.', true);
                    $maindomain = str_replace($devPart . '.', '', $domain);

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