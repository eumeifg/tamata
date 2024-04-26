<?php

namespace Ktpl\Ajaxscroll\Block;

use Magento\Framework\View\Element\Template;

class Ajaxscroll extends Template {

    protected $_config;
    protected $_gototop;
    protected $_btnload;
    protected $_moduleManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getConfig($config_path = '') {
        if ($config_path) {
            $this->_config = $this->_scopeConfig->getValue('ajaxscroll/settings');
            if(array_key_exists($config_path,$this->_config)){
                return $this->_config[$config_path];
            }
        }
        return;
    }

    public function loadingIcon() {
        $loading = $this->getConfig('loading_icon');
        if ($loading) {
            return $this->getMediaUrl() . 'ajaxscroll/' . $loading;
        }
        return false;
    }

    public function getConfigGototop($config_path = '') {
        $this->_gototop = $this->_scopeConfig->getValue('ajaxscroll/gototop');
        if ($this->_gototop['enabled_gototop'] && array_key_exists($config_path,$this->_gototop)){
            return $this->_gototop[$config_path];
        }
        return false;
    }

    public function getConfigButton($config_path = '') {
        $this->_btnload = $this->_scopeConfig->getValue('ajaxscroll/btn_loadmore');
        return $this->_btnload[$config_path];
    }
    
    public function getIsCustomerLoggedIn() {
        if($this->customerSession->isLoggedIn()){
            return true;
        }
        return false;
    }

}
