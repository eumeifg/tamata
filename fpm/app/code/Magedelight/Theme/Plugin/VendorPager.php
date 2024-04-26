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
namespace Magedelight\Theme\Plugin;

use Magento\Theme\Block\Html\Pager;

class VendorPager
{
    public function aroundGetLastNum(\Magento\Theme\Block\Html\Pager $subject, \Closure $proceed)
    {
        $collection = $subject->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + count($subject->getCollection()->getData());
    }
}
