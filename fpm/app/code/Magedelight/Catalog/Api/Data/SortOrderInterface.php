<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api\Data;

/**
 * Product request interface.
 */
interface SortOrderInterface
{

    const KEY_KEY = 'key';
    const KEY_LABEL = 'label';

    /**
     * Get Key
     *
     * @return string|null
     */
    public function getKey();

    /**
     * set Key
     *
     * @return string|null
     */
    public function setKey($key);

    /**
     * Get Label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * set Label
     *
     * @return string|null
     */
    public function setLabel($label);
}
