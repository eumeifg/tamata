<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Model\Rule\Source;

class Cancelcondition implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magedelight\Abandonedcart\Model\Rule
     */
    protected $rule;

    /**
     * Constructor
     *
     * @param \Magedelight\Abandonedcart\Model\Rule $rule
     */
    public function __construct(\Magedelight\Abandonedcart\Model\Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->rule->getCancelconditions();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
