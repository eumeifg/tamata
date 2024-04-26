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
namespace Magedelight\ProductAlert\Block\Product\View\Type;

class Grouped
{
    public function afterGetTemplate($subject, $result)
    {
        if (strpos($result, 'type/grouped.phtml') !== false) {
            $result = "Magedelight_ProductAlert::product/view/type/grouped.phtml";
        }
        return $result;
    }
}
