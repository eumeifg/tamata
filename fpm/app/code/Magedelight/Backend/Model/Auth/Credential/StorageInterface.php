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
namespace Magedelight\Backend\Model\Auth\Credential;

/**
 * Seller Panel Auth Credential Storage interface
 *
 * @author      Rocket Bazaar Core Team
 * @api
 */
interface StorageInterface
{
    /**
     * Authenticate process.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate($username, $password);

    /**
     * Login action. Check if given username and password are valid
     *
     * @param string $username
     * @param string $password
     * @return $this
     * @abstract
     */
    public function login($username, $password);

    /**
     * Reload loaded (already authenticated) credential storage
     *
     * @return $this
     * @abstract
     */
    public function reload();

    /**
     * Check if user has available resources
     *
     * @return bool
     * @abstract
     */
    public function hasAvailableResources();

    /**
     * Set user has available resources
     *
     * @param bool $hasResources
     * @return $this
     * @abstract
     */
    public function setHasAvailableResources($hasResources);
}
