<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Plugins\Bundle;

class Option
{
    public function aroundGetSelectionQtyTitlePrice($subject, \Closure $proceed, $selection, $includeContainerAddType = true)
    {
        $priceTitle = $proceed($selection, $includeContainerAddType);
       // $priceTitle = $this->_addStatusToTitle($priceTitle, $selection);

        return $priceTitle;
    }

    public function aroundGetSelectionTitlePrice($subject, \Closure $proceed, $selection, $includeContainerAddType = true)
    {
        $priceTitle = $proceed($selection, $includeContainerAddType);
        //$priceTitle = $this->_addStatusToTitle($priceTitle, $selection);

        return $priceTitle;
    }

    protected function _addStatusToTitle($priceTitle, $selection)
    {
        if ($selection->getData('md_is_salable')) {
            $span = '</span>';
            $position = strpos($priceTitle, $span);
            $text = ' &nbsp; <span class="rb-bundle-status">(' . __('Out of Stock') . ')</span>';
            if ($position !== false) {
                $priceTitle = substr_replace($priceTitle, $span . $text, $position, 7);
            } else {
                $priceTitle .= $text;
            }
        }

        return $priceTitle;
    }
}
