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
namespace Magedelight\Vendor\Model\Source;

use \Magento\Framework\DataObject;

/**
 * @author Rocket Bazaar Core Team
 */
abstract class AbstractSource extends DataObject
{
    abstract public function toOptionHash($selector = false);

    public function toOptionArray($selector = false)
    {
        $arr = [];
        foreach ($this->toOptionHash($selector) as $v => $l) {
            if (!is_array($l)) {
                $arr[] = ['label'=>$l, 'value'=>$v];
            } else {
                $options = [];
                foreach ($l as $v1 => $l1) {
                    $options[] = ['value'=>$v1, 'label'=>$l1];
                }
                $arr[] = ['label'=>$v, 'value'=>$options];
            }
        }
        return $arr;
    }

    public function getOptionLabel($value)
    {
        $options = $this->toOptionHash();
        if (is_array($value)) {
            $result = [];
            foreach ($value as $v) {
                $result[$v] = isset($options[$v]) ? $options[$v] : $v;
            }
        } else {
            $result = isset($options[$value]) ? $options[$value] : $value;
        }
        return $result;
    }
}
