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
namespace Magedelight\User\Api\Data;

/**
 *
 * @author Rocket Bazaar Core Team
 */
/**
 * Admin user interface.
 *
 * @api
 */
interface UserInterface
{
    /**
     * Get ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get first name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set first name.
     *
     * @param string $firstName
     * @return $this
     */
    public function setName($firstName);

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);
}
