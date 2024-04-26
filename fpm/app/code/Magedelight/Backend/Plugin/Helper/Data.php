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

namespace Magedelight\Backend\Plugin\Helper;

class Data
{
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterIsEnabled($subject, $result)
    {
        if ($result) {
            $currentUrl = $this->storeManager->getStore()->getBaseUrl();
            $domain = $this->getDomainName($currentUrl);
            if (strpos($domain, 'localhost') === false && strpos($domain, '127.0.0.1') === false && strpos($domain, 'local.rb-release') === false) {
                $selectedWebsites = $this->scopeConfig->getValue('md_backend/general/select_website');
                $websites = explode(',', $selectedWebsites);
                if (in_array($domain, $websites) && $this->scopeConfig->getValue('md_backend/license/data')) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                return true;
            }
        }
        return $result;
    }

    public function getDomainName($domain)
    {
        $string = '';

        $withTrim = str_replace(["www.","http://","https://"], '', $domain);

        /* finding the first position of the slash  */
        $string = $withTrim;

        $slashPos = strpos($withTrim, "/", 0);

        if ($slashPos != false) {
            $parts = explode("/", $withTrim);
            $string = $parts[0];
        }
        return $string;
    }
}
