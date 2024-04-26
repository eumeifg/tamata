<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\Plugin;

class SidebarFilter
{

    /**
     * @param \Magento\Catalog\Model\Layer $subject
     * @param type $collection
     * @return type
     */
    public function beforePrepareProductCollection(\Magento\Catalog\Model\Layer $subject, $collection)
    {
        $collection->getSelect()->join(
            ['vp' =>  'md_vendor_product'],
            "e.entity_id = vp.marketplace_product_id and vp.status = 1",
            ['vp.*']
        );
        $collection->getSelect()->group('marketplace_product_id');
        return [$collection];
    }
}
