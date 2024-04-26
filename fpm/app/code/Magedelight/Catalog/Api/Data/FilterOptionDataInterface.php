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
 * Filter Option interface.
 */
interface FilterOptionDataInterface
{
    const KEY_LABEL = 'label';
    const KEY_COUNT = 'count';
    const KEY_OPTION_ID = 'id';

    /**
     * Get Label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set Label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get Count
     *
     * @return int
     */
    public function getCount();

    /**
     * Set Label
     *
     * @param int $count
     * @return $this
     */
    public function setCount($count);

    /**
     * Get option id
     *
     * @return int
     */
    public function getId();

    /**
     * Set option id
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);
}
