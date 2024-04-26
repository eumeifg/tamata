<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Api\Data;

interface AssociativeArrayItemInterface
{
    const KEY = "key";
    const KEY_VALUE = "value";

    /**
     * Get key
     *
     * @return int|string
     */
    public function getKey();

    /**
     * Set key
     * @param int|string $key
     * @return $this
     */
    public function setKey($key);

    /**
     * Get value
     *
     * @return int|string
     */
    public function getValue();

    /**
     * Set value
     *
     * @param int|string $value
     * @return $this
     */
    public function setValue($value);
}
