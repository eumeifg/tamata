<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
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
 * @package Magedelight_SocialLogin
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\SocialLogin\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context as HelperContext;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Magedelight\SocialLogin\Model\ResourceModel\Social\CollectionFactory;
use Magento\Framework\App;

class Data extends \Magento\Framework\Url\Helper\Data
{
    const XML_PATH_REDIRECTION = 'sociallogin/general/redirection';
    const XML_PATH_MODULE_ENABLED = 'sociallogin/general/enable';
    const XML_PATH_DISPLAY_COUNT = 'sociallogin/general/maxallow';
    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';
    const XML_PATH_WELCOME_EMAIL_TEMPLATE ='sociallogin/general/welcome_email_template';
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    const XML_PATH_EMAIL_SENDER_NAME = 'trans_email/ident_general/name';
    const XML_PATH_WELCOME_PERMISSION = 'sociallogin/general/welcome';

    // @codingStandardsIgnoreStart
    protected $coreRegistry;
    protected $layout;
    protected $storeManager;
    protected $scopeConfig;
    protected $collectionFactory;
    protected $customerFactory;
    protected $redirect;
    protected $session;
    // @codingStandardsIgnoreEnd

    /**
     * @param Context                                            $context
     * @param StoreManagerInterface                              $storeManager
     * @param Registry                                           $coreRegistry
     * @param LayoutInterface                                    $layout
     * @param ScopeConfigInterface                               $scopeConfig
     * @param CollectionFactory                                  $collectionFactory
     * @param CustomerFactory                                    $customerFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\Response\RedirectInterface  $redirect
     */
    public function __construct(
        HelperContext $context,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        LayoutInterface $layout,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Customer $customerFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        App\Response\RedirectInterface $redirect,
        \Magento\Framework\View\LayoutFactory $layoutfactory,
        \Magento\Framework\Controller\Result\JsonFactory $JsonFactory,
        \Magento\Framework\DataObject $dataobject,
        \Magento\Framework\App\Http\Context $httpcontext,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->redirect = $redirect;
        $this->_session = $session;
        $this->layoutfactory = $layoutfactory;
        $this->JsonFactory = $JsonFactory;
        $this->dataobject = $dataobject;
        $this->httpcontext = $httpcontext;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }
    
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

	public function getRedirectionConfig()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REDIRECTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getRedirection()
    {
        $resultdirect = $this->getredirectUrl($this->scopeConfig->getValue(
            self::XML_PATH_REDIRECTION,
            ScopeInterface::SCOPE_STORE
        ));

        return $resultdirect;
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    public function customerEditUrl()
    {
        return $this->_storeManager->getStore()->getUrl('customer/account/edit');
    }

    public function ismoduleEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MODULE_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getredirectUrl($social_redirect)
    {
        $url = "";
        $path = "";
        switch ($social_redirect) {
            case 'homepage':
                $url = $this->getBaseUrl();
                $path = "/";
                break;
            case 'current':
                $url = $this->getCurrentUrl();
                // Remove domain name from current url
                $path = parse_url($url, PHP_URL_PATH);
                $path = ltrim($path, '/');
                // If popup is disabled, redirection type will be Current page and customer may login from login page, then customer should redirect to My Account page 
                if (strpos($url, 'customer/account/login') !== false) {
                    $path = 'customer/account/login';
                }
                break;
            default:
                $url = $this->getBaseUrl() . 'customer/account';
                $path = 'customer/account';
        }
        // Get query parameters from current url
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
        // Added query parameter for refreshing customer section after login
        $queryParams['mdlogin'] = '1';
        // Return new url with new query parameters
        $newUrl = $this->_getUrl($path, ['_query' => $queryParams]);
        return $newUrl;
    }

    public function getDisplayPosition()
    {
        return $this->scopeConfig->getValue('sociallogin/general/displayposition', ScopeInterface::SCOPE_STORE);
    }

    public function getButtonBgColor($login)
    {
        return $this->scopeConfig->getValue('sociallogin/'.$login.'/button_bg_color', ScopeInterface::SCOPE_STORE);
    }

    public function getButtonFontColor($login)
    {
        return $this->scopeConfig->getValue('sociallogin/'.$login.'/button_font_color', ScopeInterface::SCOPE_STORE);
    }

    public function getIconColor($login)
    {
        return $this->scopeConfig->getValue('sociallogin/'.$login.'/icon_color', ScopeInterface::SCOPE_STORE);
    }

    public function getIconBgColor($login)
    {
        return $this->scopeConfig->getValue('sociallogin/'.$login.'/icon_bg_color', ScopeInterface::SCOPE_STORE);
    }

    //Check if custom style is enabled
    public function getIsenabledCustomStyle()
    {
        return $this->scopeConfig->getValue('sociallogin/general/custom_style', ScopeInterface::SCOPE_STORE);
    }

    //To fetch custom color from backend for popup header and button
    public function getConfigColor()
    {
        return $this->scopeConfig->getValue('sociallogin/general/custom_color', ScopeInterface::SCOPE_STORE);
    }

    //To fetch custom font color from backend for popup header and button
    public function getConfigFontColor()
    {
        return $this->scopeConfig->getValue('sociallogin/general/custom_font_color', ScopeInterface::SCOPE_STORE);
    }

    //To fetch custom css from backend
    public function getConfigCss()
    {
        return $this->scopeConfig->getValue('sociallogin/general/custom_css', ScopeInterface::SCOPE_STORE);
    }

    public function getlayoutFactory()
    {
        return $this->layoutfactory;
    }

    public function getjsonFactory()
    {
        return $this->JsonFactory;
    }

    public function getresponseObject()
    {
        return $this->dataobject;
    }

    public function isloggedin()
    {
        $context =$this->httpcontext;
        /** @var bool $isLoggedIn */
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);

        return $isLoggedIn;
    }

    public function getCustomerExist($id, $type)
    {
        $user = $this->collectionFactory->create()
            ->addFieldToFilter('social_id', $id)
            ->addFieldToFilter('type', $type);
            // @codingStandardsIgnoreStarts
            // ->getFirstItem();
        // @codingStandardsIgnoreEnd
        $usercount = $user->getSize();
        $socialUser = $user->getData();
        if ($usercount > 0) {

            if ($socialUser[0]['social_id'] == $id) {
                return $socialUser[0]['customer_id'];
            } else {
                return;
            }

            // if ($user->getSocialId() == $id) {
            //     return $user->getCustomerId();
            // } else {
            //     return;
            // }
        }
    }

    public function getCustomerByEmail($email, $websiteId = null)
    {
        $customer = $this->customerFactory;
        if (!$websiteId) {
            $customer->setWebsiteId($this->_storeManager->getWebsite()->getId());
        } else {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    public function getSortedLoginList()
    {
        $loginlist = ['facebook','twitter','google','instagram','linkedin','foursquare','reddit',
            'flickr','vimeo','tumblr','yahoo','windows','amazon','soundcloud','dropbox'];
        $sortedloginlist = [];

        foreach ($loginlist as $login) {
            if ($this->scopeConfig->getValue('sociallogin/'.$login.'/enable', ScopeInterface::SCOPE_STORE)) {
                $socialsort = $this->scopeConfig->getValue(
                    'sociallogin/'.$login.'/sort_order',
                    ScopeInterface::SCOPE_STORE
                );
                $sortedloginlist[$login] = !empty($socialsort) ? $socialsort : 0;
            }
        }

        uasort(
            $sortedloginlist,
            function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            }
        );

        return $sortedloginlist;
    }

    public function getFontawesome($loginvalue)
    {
        if ($this->scopeConfig->getValue('sociallogin/'.$loginvalue.'/enable', ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig->getValue('sociallogin/'.$loginvalue.'/font_code');
        }
    }

    public function getDisplayCount()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DISPLAY_COUNT, ScopeInterface::SCOPE_STORE);
    }

    public function getCurrentUrl()
    {
        $beforeUrl = $this->redirect->getRefererUrl();
        return $beforeUrl; // Give the current url of recently viewed page
    }

    //Sociallogin credentials gathering

    public function isEnabled($social)
    {
        return $this->scopeConfig->getValue('sociallogin/'.trim($social).'/enable', ScopeInterface::SCOPE_STORE);
    }

    public function getSortorder($social)
    {
        return $this->scopeConfig->getValue('sociallogin/'.trim($social).'/sort_order', ScopeInterface::SCOPE_STORE);
    }

    public function getConsumerId($social)
    {
        return $this->scopeConfig->getValue('sociallogin/'.trim($social).'/consumerid', ScopeInterface::SCOPE_STORE);
    }

    public function getConsumerSecret($social)
    {
        return $this->scopeConfig->getValue(
            'sociallogin/'.trim($social).'/consumersecret',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAuthUrl($social)
    {
        return $this->_getUrl('mdsocial/'.$social.'/login', ['_secure' => true]);
    }

    public function getLoginUrl($social)
    {
        $sociallogin = trim($social);
        if ($sociallogin === 'flickr') {
            return $this->_getUrl('mdsocial/'.trim($sociallogin).'/login?step=1', ['_secure' => $this->isSecure()]);
        } else {
            return $this->_getUrl('mdsocial/'.trim($sociallogin).'/login?auth=1', ['_secure' => $this->isSecure()]);
        }
    }

    public function isSecure()
    {
        $isSecure = $this->scopeConfig->getValue(self::XML_PATH_SECURE_IN_FRONTEND);

        return $isSecure;
    }

    public function getWelcomeEmailPath()
    {
        $welcome_path = $this->scopeConfig->getValue(self::XML_PATH_WELCOME_EMAIL_TEMPLATE);

        return $welcome_path;
    }

    public function getWelcomeEmailSender()
    {
        $welcome_email_sender = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER);

        return $welcome_email_sender;
    }

    public function getWelcomeEmailSenderName()
    {
        $welcome_email_sender = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER_NAME);

        return $welcome_email_sender;
    }

    public function getSender()
    {
        $sender ['name'] = $this->getWelcomeEmailSenderName();
        $sender ['email'] = $this->getWelcomeEmailSender();

        return $sender;
    }

    public function getWelcomePermission()
    {
        $welcomePermission = $this->scopeConfig->getValue(self::XML_PATH_WELCOME_PERMISSION);

        return $welcomePermission;
    }
    public function sendWelcomeEmail($welcomeObject)
    {
        $this->inlineTranslation->suspend();
        try {
            $postObject = $this->getresponseObject();
            $postObject->setData($welcomeObject);

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $welcome = $this->_transportBuilder
                ->setTemplateIdentifier($this->getWelcomeEmailPath())
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['customer' => $postObject])
                ->setFrom($this->getSender())
                ->addTo($welcomeObject['email'])
                ->setReplyTo($this->getWelcomeEmailSender())
                ->getTransport();
            $welcome->sendMessage();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return;
        $this->inlineTranslation->resume();
    }
}
