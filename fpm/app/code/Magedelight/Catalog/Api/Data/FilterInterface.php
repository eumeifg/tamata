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

interface FilterInterface
{
    
    const KEY_LABEL = 'label';
    const KEY_CODE = 'code';
    const KEY_OPTIONS = 'options';

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
     * Get Code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set Label
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get list of products rendered information
     *
     * @return \Magedelight\Catalog\Api\Data\FilterOptionDataInterface[]
     */
    public function getOptions();

    /**
     * Set list of products rendered information
     *
     * @api
     * @param \Magedelight\Catalog\Api\Data\FilterOptionDataInterface[] $options
     * @return $this
     */
    public function setOptions($options);
}
