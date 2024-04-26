<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Model\Source;

class SuggestedSets extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $options;

    public function __construct(\Magento\Catalog\Model\Product\AttributeSet\Options $options)
    {
        $this->options = $options;
    }

    /**
     * Retrieve All Attribute Set
     *
     * @param bool $withEmpty add empty (please select) values to result
     * @return Label[]
     */
    public function getAllOptionsss()
    {
        $result = [];
        foreach ($this->getSuggestedSets() as $option) {
            $result[] = ['value' => $option['id'], 'label' => $option['label']];
        }
        array_unshift($result, ['value' => '', 'label' => __('-- Please Select --')]);

        $this->_options = $result;

        return $this->_options;
    }
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->options->toOptionArray();
            array_unshift($this->_options, ['value' => '0', 'label' => __('Use Parent Category Settings')]);
        }
        return $this->_options;
    }
}
