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

/**
 * Config Fields interface.
 * @api
 */
interface ConfigFieldInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const FIELD = 'field';
    const TITLE = 'title';
    
    /**
     * Get Field
     * @return string
     */
    public function getField();

    /**
     * Set Field
     * @param string $field
     * @return $this
     */
    public function setField($field);

    /**
     * Get Title
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);
}
