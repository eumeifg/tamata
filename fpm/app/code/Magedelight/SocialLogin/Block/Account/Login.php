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





namespace Magedelight\SocialLogin\Block\Account;

class Login extends \Magento\Customer\Block\Form\Login
{
    /**
     * Login constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = [])
    {
        $this->requestInterface = $context->getRequest();
        parent::__construct($context, $customerSession, $customerUrl, $data);
    }

    public function _prepareLayout()
    {
        $routeName      = $this->requestInterface->getRouteName();
        $controllerName = $this->requestInterface->getControllerName();
        $actionName     = $this->requestInterface->getActionName();
        if ($routeName == 'customer' && $controllerName == 'account' && $actionName == 'login') {
            $this->pageConfig->getTitle()->set(__('Customer Login'));
        }
        return $this;
    }
}
