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

namespace Magedelight\TargetRule\Model;

use Magedelight\TargetRule\Api\RuleRelatedProductListInterface;
use Magedelight\TargetRule\Model\Catalog\Product\ProductList\Related;
use Magedelight\TargetRule\Model\Catalog\Product\ProductList\Upsell;

class RuleRelatedProductList implements RuleRelatedProductListInterface
{
    /**
     * @var Related
     */
    protected $relatedProducts;
    /**
     * @var Upsell
     */
    private $upsellProducts;

    /**
     * RuleRelatedProductList constructor.
     * @param Related $relatedProducts
     * @param Upsell $upsellProducts
     */
    public function __construct(
        Related $relatedProducts,
        Upsell $upsellProducts
    ) {
        $this->relatedProducts = $relatedProducts;
        $this->upsellProducts = $upsellProducts;
    }

   /**
    * @inheritdoc
    */
    public function getList($productId, $type)
    {
        switch ($type) {
            case 'related-rule':
                return $this->relatedProducts->setProductId($productId)->getAllItems();
                break;
            case 'upsell-rule':
                return $this->upsellProducts->setProductId($productId)->getAllItems();
                break;
            default:
                return $this->relatedProducts->setProductId($productId)->getAllItems();
                break;
        }
    }
}
