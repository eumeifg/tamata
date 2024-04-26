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
namespace Magedelight\Catalog\Plugin\Indexer;

class Config
{
    const RB_VENDOR_INDEXER = "md_vendor_product_listing";
    /**
     * Get indexers list
     *
     * @return array[]
     */
    public function afterGetIndexers(\Magento\Indexer\Model\Config $subject, $result)
    {
        $indexers = $result;
        foreach ($indexers as $key => $indexer) {
            if ($key == self::RB_VENDOR_INDEXER) {
                $temp = [$key => $indexers[$key]];
                unset($indexers[$key]);
                $indexers = $temp + $indexers;
            }
        }
        return $indexers;
    }
}
