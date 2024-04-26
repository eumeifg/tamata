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
 * Backend locale model
 */
class Resolver extends \Magento\Framework\Locale\Resolver
{
    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    protected $_session;

    /**
     * @var \Magedelight\Backend\Model\Locale\Manager
     */
    protected $_localeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Validator\Locale
     */
    protected $_localeValidator;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param \Magedelight\Vendor\Model\Session $session
     * @param Manager $localeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Validator\Locale $localeValidator
     * @param null $locale
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $defaultLocalePath,
        $scopeType,
        \Magedelight\Vendor\Model\Session $session,
        \Magedelight\Backend\Model\Locale\Manager $localeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Validator\Locale $localeValidator,
        $locale = null
    ) {
        $this->_session = $session;
        $this->_localeManager = $localeManager;
        $this->_request = $request;
        $this->_localeValidator = $localeValidator;
        parent::__construct($scopeConfig, $defaultLocalePath, $scopeType, $locale);
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        $forceLocale = $this->_request->getParam('locale', null);
        if (!$this->_localeValidator->isValid($forceLocale)) {
            $forceLocale = false;
        }

        $sessionLocale = $this->_session->getSessionLocale();
        $userLocale = $this->_localeManager->getUserInterfaceLocale();

        $localeCodes = array_filter([$forceLocale, $sessionLocale, $userLocale]);

        if (count($localeCodes)) {
            $locale = reset($localeCodes);
        }

        return parent::setLocale($locale);
    }
}
