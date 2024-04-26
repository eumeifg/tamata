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

interface ConfigurableAttributeDataInterface
{

    const ID = 'id';
    const CODE = 'code';
    const FRONTEND_INPUT = 'frontend_input';
    const LABEL = 'label';
    const OPTIONS = 'options';

    /**
     * Get Configurable Attributes
     *
     * @return int
     */
    public function getId();

    /**
     * Set Configurable Attributes
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get Attribute Code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set Attribute Code
     * @param string $code
     * @return $this
     */
    public function setCode(string $code);

    /**
     * Get Attribute Input Type
     *
     * @return string
     */
    public function getFrontendInput();

    /**
     * Set Attribute Input Type
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput(string $frontendInput);

    /**
     * Get Product Label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set Product Label
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label);

    /**
     * Get Product Options
     *
     * @return \Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterface[]
     */
    public function getOptions();

    /**
     * Set Product Options
     * @param \Magedelight\ConfigurableProduct\Api\Data\ConfigurableOptionDataInterface[] array $options
     * @return $this
     */
    public function setOptions(array $options);
}
