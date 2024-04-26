<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

/**
 * Theme_Label class used for system configuration
 */
namespace Magedelight\Backend\Model\View\Design\Theme;

class Label implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Labels collection array
     *
     * @var array
     */
    protected $_labelsCollection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\Label\ListInterface $labelList
     */
    public function __construct(\Magedelight\Backend\Model\ResourceModel\Theme\Collection $labelList)
    {
        $this->_labelsCollection = $labelList;
    }

    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        $options = $this->_labelsCollection->getLabels();
        if ($label) {
            array_unshift($options, ['value' => '', 'label' => $label]);
        }
        return $options;
    }

    /**
     * Return labels collection for backend system configuration with empty value "No Theme"
     *
     * @return array
     */
    public function getLabelsCollectionForSystemConfiguration()
    {
        return $this->toOptionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getLabelsCollection((string)new \Magento\Framework\Phrase('-- No Theme --'));
    }
}
