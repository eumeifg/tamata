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
namespace Magedelight\Backend\Model;

/**
 *
 * @author Rocket Bazaar Core Team
 */

interface UrlInterface extends \Magento\Framework\UrlInterface
{
    /**
     * Secret key query param name
     */
    const SECRET_KEY_PARAM_NAME = 'key';

    /**
     * xpath to startup page in configuration
     */
    const XML_PATH_STARTUP_MENU_ITEM = 'vendor/startup/menu_item_id';

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null);

    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function useSecretKey();

    /**
     * Enable secret key using
     *
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function turnOnSecretKey();

    /**
     * Disable secret key using
     *
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function turnOffSecretKey();

    /**
     * Refresh vendor panel menu cache etc.
     *
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function renewSecretUrls();

    /**
     * Find vendor panel start page url
     *
     * @return string
     */
    public function getStartupPageUrl();

    /**
     * Set custom auth session
     *
     * @param \Magedelight\Backend\Model\Auth\Session $session
     * @return \Magedelight\Backend\Model\UrlInterface
     */
    public function setSession(\Magedelight\Vendor\Model\Session $session);

    /**
     * Return vendor area front name, defined in configuration
     *
     * @return string
     */
    public function getAreaFrontName();

    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function findFirstAvailableMenu();
}
