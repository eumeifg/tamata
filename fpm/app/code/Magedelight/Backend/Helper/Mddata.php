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
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Backend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Mddata extends AbstractHelper
{
    protected $_curl;
    protected $_storeManager;
    protected $configWritter;
    protected $messageManager;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;
    protected $request;
    protected $_configInterface;

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
        \Magedelight\Backend\Helper\Data $helper
    ) {
        $this->_curl = $curl;
        $this->messageManager = $messageManager;
        $this->configWritter = $configWritter;
        $this->_storeManager = $storeManager;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->request = $request;
        $this->_configInterface = $configInterface;
        $this->helper = $helper;
        
        parent::__construct($context);
    }

    public function getAllowedDomainsCollection()
    {
        $mappedDomains = [];
        $websites = [];
        $selected = [];
        $allWebsites = [];

        $url = $this->_storeManager->getStore()->getBaseUrl();
        $serial = $this->scopeConfig->getValue('md_backend/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->scopeConfig->getValue('md_backend/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isEnabled = $this->scopeConfig->getValue('md_backend/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $selectedWebsites = $this->scopeConfig->getValue('md_backend/general/select_website', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (strpos($url, 'localhost') === false && strpos($url, '127.0.0.1') === false && strpos($url, 'local.rb-release') === false) {
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
                $this->configWritter->save('md_backend/license/data', 1);
                $this->_curl->post($curlPostUrl, $keys);
                $response = $this->_curl->getBody();
                $mappedDomains = json_decode($response);
                if (is_object(json_decode($response)) &&
                    null !== json_decode($response) &&
                    isset(get_object_vars($mappedDomains)['curl_success']) &&
                    get_object_vars($mappedDomains)['curl_success'] == 1
                ) {
                    $this->configWritter->save('md_backend/license/data', 0);
                    
                    if (is_object($mappedDomains)) {
                        $mappedDomains = get_object_vars($mappedDomains);
                    }

                    if ($isEnabled == 'No') {
                        $this->messageManager->addNotice($mappedDomains['msg']);
                    }

                    if (!isset($mappedDomains['domains'])) {
                        $this->configWritter->save('md_backend/general/enable', 0);
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
                                $this->configWritter->save('md_backend/general/select_website', $updateSelected);
                                if (empty($updateSelected)) {
                                    $this->configWritter->save('md_backend/general/enable', 0);
                                    $this->configWritter->save('md_backend/license/data', 0);
                                    $this->configWritter->save('md_backend/general/select_website', '');
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
                                $this->configWritter->save('md_backend/general/select_website', $updateSelected2);
                                if (empty($updateSelected2)) {
                                    $this->configWritter->save('md_backend/general/enable', 0);
                                    $this->configWritter->save('md_backend/license/data', 0);
                                }
                            }
                        }

                        if (empty($mappedDomains['domains'])) {
                            $this->configWritter->save('md_backend/license/data', 0);
                            
                            $this->configWritter->save('md_backend/general/enable', 0);
                            $this->configWritter->save('md_backend/license/serial_key', '');
                            $this->configWritter->save('md_backend/license/activation_key', '');
                            $this->configWritter->save('md_backend/general/select_website', '');

                            $this->messageManager->addError("Invalid activation and serial key for '".$this->helper->getExtensionDisplayName()."'.");
                        } else {
                            $this->configWritter->save('md_backend/license/data', 1);
                        }

                        $confWebsites = $this->_storeManager->getWebsites();
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
                                        $this->_configInterface
                                            ->saveConfig('md_backend/general/enable', 0, 'websites', $website->getId());
                                    }

                                    if ((in_array($websiteUrl, $selected) || in_array($maindomain, $selected))) {
                                        if (isset($post['groups']['general']['fields']['select_website']['value'])) {
                                            if (isset($post['groups']['general']['fields']['enable']['value'])) {
                                                if ($post['groups']['general']['fields']['enable']['value']) {
                                                    $this->_configInterface
                                                        ->saveConfig('md_backend/general/enable', 1, 'websites', $website->getId());
                                                    $this->_configInterface
                                                        ->saveConfig('md_backend/general/enable', 1, 'stores', $store->getId());
                                                } else {
                                                    $this->_configInterface
                                                        ->saveConfig('md_backend/general/enable', 0, 'websites', $website->getId());
                                                    $this->_configInterface
                                                        ->saveConfig('md_backend/general/enable', 0, 'stores', $store->getId());
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
                            $this->configWritter->save('md_backend/general/enable', 0);
                            $this->configWritter->save('md_backend/license/data', 0);
                        }

                        if ((count($responseArray) > 0) && !sizeof($selected)) {
                            $this->messageManager->addNotice('Please select website(s) to enable the extension.');
                            $this->configWritter->save('md_backend/general/enable', 0);
                            $this->configWritter->save('md_backend/license/data', 0);
                        }
                    }
                }

                $types = ['config','full_page'];

                foreach ($types as $type) {
                    $this->_cacheTypeList->cleanType($type);
                }

                foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
            } catch (\Exception $e) {
                $this->_logger->info($e->getMessage());
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
            $scopeCode = $this->_storeManager->getStore($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_STORE))->getCode();
        } elseif ($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)) {
            $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $this->_storeManager->getWebsite($this->request->getParam(\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE))->getCode();
        } else {
            $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = 0;
        }

        return $scopeType;
    }
}
