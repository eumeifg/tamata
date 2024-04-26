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

use Magento\Framework\View\Element\Template\Context;
use Magedelight\SocialLogin\Helper\Data;

class Sociallogin extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Context                $context
     * @param ObjectManagerInterface $ObjectManagerInterface
     * @param Data                   $helper
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    public function getSortedLoginList()
    {
        return $this->_helper->getSortedLoginList();
    }

    public function getDisplayCount()
    {
        return $this->_helper->getDisplayCount();
    }

    public function isloggedin()
    {
        return $this->_helper->isloggedin();
    }

    public function isActive($login)
    {
        $social = trim($login);
        if ($this->_helper->isEnabled($social) && $this->_helper->ismoduleEnabled()) {
            return true;
        } else {
            return false;
        }
    }

    public function getLoginUrl($login)
    {
        $social = trim($login);
        return $this->_helper->getLoginUrl($social);
    }
    public function getIsenabledCustomStyle()
    {
        return $this->_helper->getIsenabledCustomStyle();
    }

    public function getConfigColor()
    {
        return $this->_helper->getConfigColor();
    }

    public function getConfigCss()
    {
        return $this->_helper->getConfigCss();
    }

    public function getFontawesome($loginvalue)
    {
        return $this->_helper->getFontawesome($loginvalue);
    }

    public function getConfigFontColor()
    {
        return $this->_helper->getConfigFontColor();
    }

    public function getButtonBgColor($login)
    {
        return $this->_helper->getButtonBgColor($login);
    }

    public function getButtonFontColor($login)
    {
        return $this->_helper->getButtonFontColor($login);
    }

    public function getIconColor($login)
    {
        return $this->_helper->getIconColor($login);
    }

    public function getIconBgColor($login)
    {
        return $this->_helper->getIconBgColor($login);
    }
}
