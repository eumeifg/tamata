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

class Util implements ObserverInterface
{

    /**
     * @var \Magedelight\User\Helper\Data
     */
    protected $helper;

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var storeManager
     */
    protected $store;

    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\Store $store
     * @param \Magedelight\User\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Store $store,
        \Magedelight\User\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->store = $store;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $serial = $this->scopeConfig->getValue('vendorauthorization/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->scopeConfig->getValue('vendorauthorization/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
            if ($serial == '') {
                $this->messageManager->addError(__("Serial key not found.Please enter valid serial key for '".$this->helper->getExtensionDisplayName()."' extension."));
            }
            
            if ($activation == '') {
                $this->messageManager->addError(__("Activation key not found.Please enter valid activation key for '".$this->helper->getExtensionDisplayName()."' extension."));
            }
        }
    }
}
