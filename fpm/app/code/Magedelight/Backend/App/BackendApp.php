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
 * Backend Application which uses Magento Backend authentication process
 */
class BackendApp
{
    /**
     * @var null
     */
    private $cookiePath;

    /**
     * @var null
     */
    private $startupPage;

    /**
     * @var null
     */
    private $aclResourceName;

    /**
     * @param string $cookiePath
     * @param string $startupPage
     * @param string $aclResourceName
     */
    public function __construct(
        $cookiePath,
        $startupPage,
        $aclResourceName
    ) {
        $this->cookiePath = $cookiePath;
        $this->startupPage = $startupPage;
        $this->aclResourceName = $aclResourceName;
    }

    /**
     * Cookie path for the application to set cookie to
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->cookiePath;
    }

    /**
     * Startup Page of the application to redirect after login
     *
     * @return string
     */
    public function getStartupPage()
    {
        return $this->startupPage;
    }

    /**
     * ACL resource name to authorize access to
     *
     * @return string
     */
    public function getAclResource()
    {
        return $this->aclResourceName;
    }
}
