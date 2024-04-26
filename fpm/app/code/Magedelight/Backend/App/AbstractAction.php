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
namespace Magedelight\Backend\App;

/**
 * Description of AbstractAction
 *
 * @author Rocket Bazaar Core Team
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Name of "is URLs checked" flag
     */
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'sellerhtml';

    /**
     * Authorization level of a basic (Main) vendor session
     */
    const VENDOR_RESOURCE = 'Magedelight_Vendor::main';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = [];

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magedelight\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_sellerPanelUrl;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var bool
     */
    protected $_canUseBaseUrl;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     */
    public function __construct(\Magedelight\Backend\App\Action\Context $context)
    {
        parent::__construct($context);
        $this->_authorization = $context->getAuthorization();
        $this->_auth = $context->getAuth();
        $this->_helper = $context->getHelper();
        $this->_sellerPanelUrl = $context->getBackendUrl();
        $this->_formKeyValidator = $context->getFormKeyValidator();
        $this->_localeResolver = $context->getLocaleResolver();
        $this->_canUseBaseUrl = $context->getCanUseBaseUrl();
        $this->_session = $context->getSession();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::VENDOR_RESOURCE);
    }

    /**
     * Retrieve vendor session model object
     *
     * @return \Magedelight\Vendor\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Define active menu item in menu block
     *
     * @param string $itemId current active menu item
     * @return $this
     */
    protected function _setActiveMenu($itemId)
    {
        $menuBlock = $this->_view->getLayout()->getBlock('menu');
        $menuBlock->setActive($itemId);
        $parents = $menuBlock->getMenuModel()->getParentItems($itemId);
        foreach ($parents as $item) {
            $this->_view->getPage()->getConfig()->getTitle()->prepend($item->getTitle());
        }
        return $this;
    }

    /**
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    protected function _addBreadcrumb($label, $title, $link = null)
    {
        $this->_view->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    protected function _addContent(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'content');
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    protected function _addLeft(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'left');
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    protected function _addJs(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @param string $containerName
     * @return $this
     */
    private function _moveBlockToContainer(\Magento\Framework\View\Element\AbstractBlock $block, $containerName)
    {
        $this->_view->getLayout()->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_helper->isEnabled()) {
            if ($this->_auth->isLoggedIn()) {
                $this->_auth->logout();
                $this->getMessageManager()->addWarningMessage(__('Seller Panel seems to be inactive. Please, try again after some time.'));
                return $this->_redirect('rbvendor/index/index');
            }
        }
        
        if ($request->isDispatched() && $request->getActionName() !== 'denied' && !$this->_isAllowed()) {
            $this->_response->setStatusHeader(403, '1.1', 'Forbidden');
            if (!$this->_session->isLoggedIn()) {
                return $this->_redirect('rbvendor');
            }
            $this->_view->loadLayout(['default', 'sellerhtml_denied'], true, true, false);
            $this->_view->renderLayout();
            $this->_request->setDispatched(true);
            return $this->_response;
        }

        if ($this->_isUrlChecked()) {
            $this->_actionFlag->set('', self::FLAG_IS_URLS_CHECKED, true);
        }

        $this->_processLocaleSettings();

        return parent::dispatch($request);
    }
    
    /**
     * Check whether url is checked
     *
     * @return bool
     */
    protected function _isUrlChecked()
    {
        return !$this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED)
        && !$this->getRequest()->isForwarded()
        && !$this->_getSession()->getIsUrlNotice(true)
        && !$this->_canUseBaseUrl;
    }

    /**
     * Check url keys. If non valid - redirect
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if ($this->_session->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_formKeyValidator->validate($this->getRequest());
                $_keyErrorMsg = __('Invalid Form Key. Please refresh the page.');
            } elseif ($this->_sellerPanelUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        'Magento\Framework\Json\Helper\Data'
                    )->jsonEncode(
                        ['error' => true, 'message' => $_keyErrorMsg]
                    )
                );
            } else {
                $this->_redirect($this->_sellerPanelUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * Set session locale,
     * process force locale set through url params
     *
     * @return $this
     */
    protected function _processLocaleSettings()
    {
        $forceLocale = $this->getRequest()->getParam('locale', null);
        if ($this->_objectManager->get('Magento\Framework\Validator\Locale')->isValid($forceLocale)) {
            $this->_getSession()->setSessionLocale($forceLocale);
        }

        if ($this->_getSession()->getLocale() === null) {
            $this->_getSession()->setLocale($this->_localeResolver->getLocale());
        }

        return $this;
    }

    /**
     * Set redirect into response
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param   string $path
     * @param   array $arguments
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_getSession()->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        $this->getResponse()->setRedirect($this->getUrl($path, $arguments));
        return $this->getResponse();
    }

    /**
     * Forward to action
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     * @return void
     */
    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->_getSession()->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        return parent::_forward($action, $controller, $module, $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_helper->getUrl($route, $params);
    }

    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        if (is_array($this->_publicActions) && in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return true;
        }

        $secretKey = $this->getRequest()->getParam(\Magedelight\Backend\Model\UrlInterface::SECRET_KEY_PARAM_NAME, null);
        if (!$secretKey || $secretKey != $this->_sellerPanelUrl->getSecretKey()) {
            return false;
        }
        return true;
    }
    
    /**
     *
     * @return string
     */
    public function getTab()
    {
        return $this->getRequest()->getParam('tab');
    }
    
    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function getRedirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
