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

interface ConfigurableOptionDataInterface
{

    const ID = 'id';
    const LABEL = 'label';
    const SWATCH = 'swatch';

    /**
     * Get Option Id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Option Id
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Option Label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set Option Label
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label);

    /**
     * Get Product Options
     *
     * @return string
     */
    public function getSwatchValue();

    /**
     * Set Product Options
     * @param string|null $swatch
     * @return $this
     */
    public function setSwatchValue($swatch);
}
