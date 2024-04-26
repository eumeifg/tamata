<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CouponOptions implements ArrayInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    protected $ruleFactory;
    
    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
        $this->_ruleFactory = $ruleFactory;
    }
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $ruleCollection = $this->_ruleFactory->create()->getCollection()
            ->addFilter('use_auto_generation', 1);
        
        $options = [];
        
        foreach ($ruleCollection as $rule) {
            if (!isset($options[0])) {
                $options[] = [
                    'label'  => __('--Select Rule--'),
                    'value'  => '',
                ];
            }

            $options[] = [
                'label'  => $rule->getName(),
                'value'  => $rule->getRuleId(),
            ];
        }
        //echo "<pre>"; print_r($options); die;
        return $options;
    }
}
