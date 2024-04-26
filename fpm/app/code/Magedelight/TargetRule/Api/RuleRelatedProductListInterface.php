<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_TargetRule
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\TargetRule\Api;

use Magedelight\TargetRule\Api\Data\ProductInterface;

interface RuleRelatedProductListInterface
{
    /**
     * Retrieve rule related products
     * @param integer $productId
     * @param string $type
     * @return ProductInterface[]
     */
    public function getList($productId, $type);
}
