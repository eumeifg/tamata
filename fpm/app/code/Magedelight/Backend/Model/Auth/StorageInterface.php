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
namespace Magedelight\Backend\Model\Auth;

/**
 *
 * @author Rocket Bazaar Core Team
 */
/**
 * Seller Panel Auth Storage interface
 */
interface StorageInterface
{
    /**
     * Perform login specific actions
     *
     * @return $this
     * @abstract
     * @api
     */
    public function processLogin();

    /**
     * Perform login specific actions
     *
     * @return $this
     * @abstract
     * @api
     */
    public function processLogout();

    /**
     * Check if user is logged in
     *
     * @return bool
     * @abstract
     * @api
     */
    public function isLoggedIn();

    /**
     * Prolong storage lifetime
     *
     * @return void
     * @abstract
     * @api
     */
    public function prolong();
}
