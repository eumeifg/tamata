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
namespace MDC\Catalog\Plugin\Model;

class RemoveSortingFilters
{

    /**
     * @param \Magedelight\Catalog\Model\CategoryProductRepository $subject
     * @param $result
     * @return mixed
     */
    public function afterLoadAvailableOrders(\Magedelight\Catalog\Model\CategoryProductRepository $subject, $result) {
        $orders = ['price', 'name', 'most_viewed','random'];
        foreach ($result as $key => $value)
        {
            if(!in_array($key, $orders))
            {
                unset($result[$key]);
            }
        }
        return $result;
    }
}
