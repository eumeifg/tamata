<?php

namespace Magedelight\SocialLogin\Observer;

use Magento\Framework\Event\ObserverInterface;

class Util implements ObserverInterface
{
     /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     *  @var storeManager
     */
    protected $_store;

    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     */
    protected $_context;

    protected $messageManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->messageManager = $context->getMessageManager();
        $this->_store = $store;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $serial = $this->_scopeConfig->getValue('sociallogin/license/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $activation = $this->_scopeConfig->getValue('sociallogin/license/activation_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isEnable = $this->_scopeConfig->getValue('sociallogin/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);//check module enavle
        
        if ($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
            if ($serial == '') {
                if($isEnable == 1 ){
                    $this->messageManager->addError(__("Serial key not found.Please enter valid serial key for 'Social Media Login' extension."));
                }
            }
            
            if ($activation == '') {
                if($isEnable == 1 ){
                    $this->messageManager->addError(__("Activation key not found.Please enter valid activation key for 'Social Media Login' extension."));
                }
            }
        }
    }
    // @codingStandardsIgnoreEnd
}
