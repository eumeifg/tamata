<?php
/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\User\Observer;

use Magento\Framework\Event\ObserverInterface;

class Mdfrd implements ObserverInterface
{
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\User\Helper\Data $helper,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->urlBuilder = $context->getUrl();
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent()->getName();
        if ($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
            $keys['serial_key'] = $this->scopeConfig->getValue('vendorauthorization/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $keys['activation_key'] = $this->scopeConfig->getValue('vendorauthorization/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!empty($keys['serial_key']) && !empty($keys['activation_key'])) {
                $url = $this->urlBuilder->getCurrentUrl();
                $parsedUrl = parse_url($url);
                $keys['host'] = $parsedUrl['host'];
                $keys['ip'] = $_SERVER['SERVER_ADDR'];
                $keys['new_mechanism'] = 1;
                $keys['product_name'] = $this->helper->getExtensionDisplayName();
                $keys['extension_key'] = $this->helper->getExtensionKey();
                $field_string = http_build_query($keys);
                $ch = curl_init('https://www.magedelight.com/ktplsys/?'.$field_string);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
                try {
                    curl_exec($ch);
                    curl_close($ch);
                } catch (\Exception $e) {
                    //$this->messageManager->addError($e->getMessage());
                }
            }
        }
    }
}
