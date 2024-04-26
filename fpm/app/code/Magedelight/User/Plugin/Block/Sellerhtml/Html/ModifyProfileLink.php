<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Plugin\Block\Sellerhtml\Html;

/**
 * @author Rocket Bazaar Core Team
 */
class ModifyProfileLink
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     *
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magedelight\Backend\Model\Auth\Session $authSession
    ) {
        $this->authSession = $authSession;
    }
    
    /**
     *
     * @param \Magedelight\Vendor\Block\Sellerhtml\Html\HeaderLink $subject
     * @param string $result
     * @param string $param
     * @param string $alias
     * @return string
     */
    public function afterGetLinkData(\Magedelight\Vendor\Block\Sellerhtml\Html\HeaderLink $subject, $result, $param, $alias = '')
    {
        if ($this->authSession->getUser()->getIsUser() == 1 && $alias == 'vendor.profile' && $param == 'action_url') {
            return 'rbuser/account';
        }
        return $result;
    }
}
