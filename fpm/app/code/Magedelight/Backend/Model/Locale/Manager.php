<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Model\Locale;

/**
 * Locale manager model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Manager
{
    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    protected $_session;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;
    
    /**
     * @var \Magedelight\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * constructor
     *
     * @param \Magedelight\Vendor\Model\Session $session
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magedelight\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(
        \Magedelight\Vendor\Model\Session $session,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\TranslateInterface $translator,
        \Magedelight\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_session = $session;
        $this->_authSession = $authSession;
        $this->_translator = $translator;
        $this->_backendConfig = $backendConfig;
    }

    /**
     * Switch backend locale according to locale code
     *
     * @param string $localeCode
     * @return $this
     */
    public function switchBackendInterfaceLocale($localeCode)
    {
        $this->_session->setSessionLocale(null);
        
        $this->_authSession->getUser()->setInterfaceLocale($localeCode);
        
        $this->_translator->setLocale($localeCode)->loadData(null, true);
        
        return $this;
    }

    /**
     * Get general interface locale
     *
     * @return string
     */
    public function getGeneralLocale()
    {
        return $this->_backendConfig->getValue('general/locale/code');
    }

    /**
     * Get user interface locale stored in session data
     *
     * @return string
     */
    public function getUserInterfaceLocale()
    {
        $userData = $this->_authSession->getUser();
        $interfaceLocale = \Magento\Framework\Locale\Resolver::DEFAULT_LOCALE;
        
        if ($userData && $userData->getInterfaceLocale()) {
            $interfaceLocale = $userData->getInterfaceLocale();
        } elseif ($this->getGeneralLocale()) {
            $interfaceLocale = $this->getGeneralLocale();
        }
        
        return $interfaceLocale;
    }
}
