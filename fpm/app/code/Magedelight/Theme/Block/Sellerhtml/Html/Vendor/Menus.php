<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Block\Sellerhtml\Html\Vendor;

use Magedelight\Backend\Block\Template;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Logo page header block
 */
class Menus extends Template
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magedelight\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * Name of cookie that holds burger cookie
     */
    const BURGER_COOKIE_NAME = 'burger-menu-expanded';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CookieManagerInterface $cookieManager
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CookieManagerInterface $cookieManager,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $data);
        $this->_vendorHelper = $vendorHelper;
        $this->_moduleManager = $moduleManager;
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get form key cookie
     *
     * @return string
     */
    public function getBurgerCookie()
    {
        return $this->cookieManager->getCookie(self::BURGER_COOKIE_NAME);
    }

    public function getMenus()
    {
        return $this->_layout->getChildBlocks($this->getNameInLayout());
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $subMenuActive = str_replace(',', '', $this->getRequest()->getParam('tab', '0,0'));

        $menuSortOrder = [];
        $vendorGroups = [];

        /* added to check whether there are any shipping methods available - starts here*/
        $activeCarriers = count($this->getShippingMethods());

        if ($activeCarriers <= 0) {
            $this->_layout->unsetElement('vendorShippingMethod');
        }
        /* added to check whether there are any shipping methods available - ends here*/

        /* Check Vendor Promotions Is Enable - start here*/
        $vendorPromotionsIsEnable =$this->scopeConfig->getValue('vendorpromotion/general/enable');
        if (!$vendorPromotionsIsEnable) {
            $this->_layout->unsetElement('vendorPromotions');
        }

        /* Check Vendor Promotions Is Enable - end here*/
        foreach ($this->getMenus() as $menu) {
            $menuSortOrder[$menu->getSortOrder()][$menu->getVendorGroup()][$menu->getNameInLayout()] = $menu;
            $vendorGroups[] = $menu->getVendorGroup();
        }
        ksort($menuSortOrder);
        $countArray = array_count_values($vendorGroups);
        $html = '<ul class="nav sidebar-menu" id="nav-sidebar-menu">';

        $i = 1;
        $j = 0;
        $k = 0;

        $mainMenus = [];
        $subMenus = [];

        $allowedResources = null;
        if ($this->_isOutputEnabled('Magedelight_User')) {
            $this->_userFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magedelight\User\Model\UserFactory::class
            );
            $allowedResources = $this->_userFactory->create()->getAllowedResourcesByRole();
        }

        foreach ($menuSortOrder as $menuBlcok) {
            foreach ($menuBlcok as $key => $vendorGroup) {
                foreach ($vendorGroup as $menuItem) {
                    if (!$menuItem->getParentId()) {
                        if (is_array($allowedResources)) {
                            if (in_array($menuItem->getResourceId(), $allowedResources)) {
                                $mainMenus[] = $menuItem;
                            }
                        } else {
                            $mainMenus[] = $menuItem;
                        }
                    } else {
                        $subMenus[$menuItem->getParentId()][$menuItem->getSortOrder()][$menuItem->getLabel()] = $menuItem;
                    }
                }
            }
        }

        $active = '';
        foreach ($mainMenus as $mainMenu) {
            if (!$mainMenu->getParentId() && !$mainMenu->getChildId()) {
                $tabPath = $k . $j;
                if ($subMenuActive == $tabPath) {
                    $active = 'active';
                }

                $html .= '<li class="' . $active . '">'
                    . '<a class="single-menu" href="' . $this->getUrl($mainMenu->getPath(), ['tab' => $k . ',' . $j]) . '"';
                $html .= $mainMenu->getLabel() ? ' title="' . $this->escapeHtml(__($mainMenu->getLabel())) . '"' : '';
                $html .= $this->getAttributesHtml() . '>';
                $html .= '<span class="' . $mainMenu->getIconClass() . '" ></span>';
                $html .= '<span class="sidebar-title" >' . $mainMenu->getLabel() . '</span>';
                $html .= '</a></li>';
                $k++;
                $active = '';
            } else {
                $html .= '<li>'
                    . '<a href="' . $mainMenu->getPath() . '"';
                $html .= 'class="' . $mainMenu->getAccordionToggle() . '"';
                $html .= $mainMenu->getLabel() ? ' title="' . $this->escapeHtml(
                    (string) new \Magento\Framework\Phrase($mainMenu->getLabel())
                ) . '"' : '';
                $html .= $this->getAttributesHtml() . '>';
                $html .= '<span class="' . $mainMenu->getIconClass() . '" ></span>';
                $html .= '<span class="sidebar-title" >' . $mainMenu->getLabel() . '</span>';
                $html .= '<span class="caret"></span>';
                $html .= '</a>';
                $html .= '<ul class="nav sub-nav" style="">';

                $subMenuItems = array_intersect_key($subMenus, array_flip([$mainMenu->getChildId()]));
                foreach ($subMenuItems as $subMenuItem) {
                    ksort($subMenuItem);
                    foreach ($subMenuItem as $vendorMenus) {
                        ksort($vendorMenus);
                        foreach ($vendorMenus as $menuItem) {
                            $tabPath = $k . $j;
                            if ($subMenuActive == $tabPath) {
                                $active = 'active';
                            }
                            if ($menuItem->getIsVendorEnabled()) {
                                if (!$this->_vendorHelper->getConfigValue($menuItem->getIsVendorEnabled())) {
                                    continue;
                                }
                            }
                            $html .= '<li class="' . $active . '">'
                                . '<a href="' . $this->getUrl($menuItem->getPath(), ['tab' => $k . ',' . $j]) . '"';
                            $html .= 'class="' . $menuItem->getAccordionToggle() . '"';
                            $html .= $menuItem->getLabel() ? ' title="' . $this->escapeHtml(
                                (string) new \Magento\Framework\Phrase($menuItem->getLabel())
                            ) . '"' : '';
                            $html .= $this->getAttributesHtml() . '>';
                            $html .= $menuItem->getLabel();
                            $html .= '</a></li>';
                            $j++;
                            $active = '';
                        }
                    }
                }

                $html .= '</ul>';
                $html .= '';
                $html .= '</li>';

                $k++;
                $j = 0;
            }
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Whether a module output is permitted by the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    protected function _isOutputEnabled($moduleName)
    {
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * Get all active shipping methods to check whether to display
     * shipping method menu in vendor or not
     */
    public function getShippingMethods()
    {
        $activeCarriers = $this->shipconfig->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $methods = [];
        $carrierTitle = '';
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $isActiveForVendor = $this->scopeConfig->getValue(
                        'carriers/' . $carrierCode . '/is_active_for_vendor'
                    );
                    if ($isActiveForVendor) {
                        $code= $carrierCode . '_' . $methodCode;
                        $options[]=['value'=>$code,'label'=>$method];
                        $carrierTitle =$this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
                        $methods[]=['value'=>$options,'label'=>$carrierTitle];
                    }
                }
            }
        }
        return $methods;
    }
}
