<?php
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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
 * @package Magedelight_User
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\User\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Mddata extends AbstractHelper
{
    protected $curl;
    protected $storeManager;
    protected $configWritter;
    protected $messageManager;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $request;
    protected $configInterface;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWritter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
     * @param \Magedelight\User\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWritter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magedelight\User\Helper\Data $helper
    ) {
        $this->curl = $curl;
        $this->messageManager = $messageManager;
        $this->configWritter = $configWritter;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->request = $request;
        $this->configInterface = $configInterface;
        $this->helper = $helper;
        
        parent::__construct($context);
    }

    public function getAllowedDomainsCollection()
    {
        $mappedDomains = [];
        $websites = [];
        $selected = [];
        $allWebsites = [];

        $url = $this->storeManager->getStore()->getBaseUrl();
        $serial = $this->scopeConfig->getValue('vendorauthorization/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->scopeConfig->getValue('vendorauthorization/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isEnabled = $this->scopeConfig->getValue('vendorauthorization/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $selectedWebsites = $this->scopeConfig->getValue('vendorauthorization/general/select_website', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (strpos($url, 'localhost') === false && strpos($url, '127.0.0.1') === false) {
            if ($serial == '' && $activation == '') {
                $this->messageManager->addError("Serial and Activation keys not found.Please enter valid keys for '".$this->helper->getExtensionDisplayName()."' extension.");
            }
            if ($activation == '') {
                $this->messageManager->addError("Activation key not found.Please enter valid activation key for '".$this->helper->getExtensionDisplayName()."' extension.");
            }

            if ($serial == '') {
                $this->messageManager->addError("Serial key not found.Please enter valid serial key for '".$this->helper->getExtensionDisplayName()."' extension.");
            }
            
            $parsedUrl = parse_url($url);
            $domain = str_replace(['www.', 'http://', 'https://'], '', $parsedUrl['host']);
            $hash = $serial.''.$domain;
            $keys['sk'] = $serial;
            $keys['ak'] = $activation;
            $keys['domain'] = $domain;
            $keys['product_name'] = $this->helper->getExtensionDisplayName();
            $keys['ek'] = $this->helper->getExtensionKey();
            $keys['sw'] = $selectedWebsites;
            $field_string = http_build_query($keys);

            $curlPostUrl = 'https://www.magedelight.com/ktplsys/index/validate?'.$field_string;

            try {
                $this->configWritter->save('vendorauthorization/license/data', 1);
                $this->curl->post($curlPostUrl, $keys);
                $response = $this->curl->getBody();
                $mappedDomains = json_decode($response);
                if (is_object(json_decode($response)) &&
                    null !== json_decode($response) &&
                    isset(get_object_vars($mappedDomains)['curl_success']) &&
                    get_object_vars($mappedDomains)['curl_success'] == 1
                ) {
                    $this->configWritter->save('vendorauthorization/license/data', 0);
                    
                    if (is_object($mappedDomains)) {
                        $mappedDomains = get_object_vars($mappedDomains);
                    }

                    if ($isEnabled == 'No') {
                        $this->messageManager->addNotice($mappedDomains['msg']);
                    }

                    if (!isset($mappedDomains['domains'])) {
                        $this->configWritter->save('vendorauthorization/general/enable', 0);
                    }

                    if (isset($mappedDomains['domains'])) {
                        $post = get_object_vars($this->request->getPost());
                        if (isset($post['groups']['general']['fields']['select_website']['value'])) {
                            $websites = $post['groups']['general']['fields']['select_website']['value'];
                            if (sizeof($websites) > 0 && !empty($websites[0])) {
                                $updateSelected = '';
                                if (count($websites) > 0) {
                                    foreach ($websites as $web) {
                                        $devPart = strchr($web, '.', true);
                                        $maindomain = str_replace($devPart.'.', '', $web);

                                        if (in_array($web, $mappedDomains['domains']) ||
                                           in_array($maindomain, $mappedDomains['domains'])
                                        ) {
                                            $selected[] = $web;
                                        }
                                    }
                                }
                                $updateSelected = implode(',', $selected);
                                $this->configWritter->save('vendorauthorization/general/select_website', $updateSelected);
                                if (empty($updateSelected)) {
                                    $this->configWritter->save('vendorauthorization/general/enable', 0);
                                    $this->configWritter->save('vendorauthorization/license/data', 0);
                                    $this->configWritter->save('vendorauthorization/general/select_website', '');
                                }
                            }
                        } else {
                            if (!empty($selectedWebsites)) {
                                $websites = explode(',', $selectedWebsites);
                                foreach ($websites as $web2) {
                                    $devPart1 = strchr($web2, '.', true);
                                    $maindomain1 = str_replace($devPart1.'.', '', $web2);

                                    if (in_array($web2, $mappedDomains['domains']) || in_array($maindomain1, $mappedDomains['domains'])) {
                                        $selected[] = $web2;
                                    }
                                }

                                $updateSelected2 = implode(',', $selected);
                                $this->configWritter->save('vendorauthorization/general/select_website', $updateSelected2);
                                if (empty($updateSelected2)) {
                                    $this->configWritter->save('vendorauthorization/general/enable', 0);
                                    $this->configWritter->save('vendorauthorization/license/data', 0);
                                }
                            }
                        }

                        if (empty($mappedDomains['domains'])) {
                            $this->configWritter->save('vendorauthorization/license/data', 0);
                            
                            $this->configWritter->save('vendorauthorization/general/enable', 0);
                            $this->configWritter->save('vendorauthorization/license/serial_key', '');
                            $this->configWritter->save('vendorauthorization/license/activation_key', '');
                            $this->configWritter->save('vendorauthorization/general/select_website', '');

                            $this->messageManager->addError("Invalid activation and serial key for '".$this->helper->getExtensionDisplayName()."'.");
                        } else {
                            $this->configWritter->save('vendorauthorization/license/data', 1);
                        }

                        $confWebsites = $this->storeManager->getWebsites();
                        if (sizeof($confWebsites) > 0) {
                            foreach ($confWebsites as $website) {
                                foreach ($website->getStores() as $store) {
                                    $wedsiteId = $website->getId();
                                    $webUrl = $this->scopeConfig->getValue('web/unsecure/base_url', 'website', $website->getCode());
                                    $parsedUrl = parse_url($webUrl);
                                    $websiteUrl = str_replace(['www.', 'http://', 'https://'], '', $parsedUrl['host']);

                                    $devPart = strchr($websiteUrl, '.', true);
                                    $maindomain = str_replace($devPart.'.', '', $websiteUrl);

                                    $allWebsites[] = $websiteUrl;
                                    $allWebsites[] = $maindomain;

                                    if (!in_array($websiteUrl, $mappedDomains['domains']) &&
                                       !in_array($maindomain, $mappedDomains['domains'])) {
                                        $this->configInterface
                                            ->saveConfig('vendorauthorization/general/enable', 0, 'websites', $website->getId());
                                    }

                                    if ((in_array($websiteUrl, $selected) || in_array($maindomain, $selected))) {
                                        if (isset($post['groups']['general']['fields']['select_website']['value'])) {
                                            if (isset($post['groups']['general']['fields']['enable']['value'])) {
                                                if ($post['groups']['general']['fields']['enable']['value']) {
                                                    $this->configInterface
                                                        ->saveConfig('vendorauthorization/general/enable', 1, 'websites', $website->getId());
                                                    $this->configInterface
                                                        ->saveConfig('vendorauthorization/general/enable', 1, 'stores', $store->getId());
                                                } else {
                                                    $this->configInterface
                                                        ->saveConfig('vendorauthorization/general/enable', 0, 'websites', $website->getId());
                                                    $this->configInterface
                                                        ->saveConfig('vendorauthorization/general/enable', 0, 'stores', $store->getId());
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $responseArray = [];
                        foreach ($allWebsites as $key => $domain) {
                            if (in_array($domain, $mappedDomains['domains'])) {
                                $responseArray[] = ['value' => $domain, "label" => $domain];
                            }
                        }

                        if (!sizeof($responseArray) && count($mappedDomains['domains']) > 0) {
                            $this->messageManager->addNotice('You didn\'t purchase license for domain(s) configured on this Magento setup');
                            $this->configWritter->save('vendorauthorization/general/enable', 0);
                            $this->configWritter->save('vendorauthorization/license/data', 0);
                        }

                        if ((count($responseArray) > 0) && !sizeof($selected)) {
                            $this->messageManager->addNotice('Please select website(s) to enable the extension.');
                            $this->configWritter->save('vendorauthorization/general/enable', 0);
                            $this->configWritter->save('vendorauthorization/license/data', 0);
                        }
                    }
                }

                $types = ['config','full_page'];

                foreach ($types as $type) {
                    $this->cacheTypeList->cleanType($type);
                }

                foreach ($this->cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }

            return $mappedDomains;
        }
    }

    public function getCurrentScope()
    {
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeCode = 0;
        if ($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $scopeCode = $this->storeManager->getStore($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_STORE))->getCode();
        } elseif ($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $this->storeManager->getWebsite($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE))->getCode();
        } else {
            $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = 0;
        }

        return $scopeType;
    }
}
