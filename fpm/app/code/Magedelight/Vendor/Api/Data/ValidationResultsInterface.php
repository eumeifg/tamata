<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api\Data;

interface ValidationResultsInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const VALID = 'valid';
    const MESSAGES = 'messages';
    /**#@-*/

    /**
     * Check if the provided data is valid.
     *
     * @api
     * @return bool
     */
    public function isValid();

    /**
     * Set if the provided data is valid.
     *
     * @api
     * @param bool $isValid
     * @return $this
     */
    public function setIsValid($isValid);

    /**
     * Get error messages as array in case of validation failure, else return empty array.
     *
     * @api
     * @return string[]
     */
    public function getMessages();

    /**
     * Set error messages as array in case of validation failure.
     *
     * @api
     * @param string[] $messages
     * @return string[]
     */
    public function setMessages(array $messages);
}
