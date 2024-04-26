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
namespace Magedelight\Catalog\Plugin\Helper\Product;

class Compare
{

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postHelper;

    /**
     * @param \Magento\Framework\Data\Helper\PostHelper $postHelper
     */
    public function __construct(
        \Magento\Framework\Data\Helper\PostHelper $postHelper
    ) {
        $this->postHelper = $postHelper;
    }

    /**
     * Get parameters used for build add product to compare list urls
     *
     * @param \Magento\Catalog\Helper\Product\Compare $subject
     * @param \Closure $proceed
     * @param $product Product
     * @return string
     */
    public function aroundGetPostDataParams(
        \Magento\Catalog\Helper\Product\Compare $subject,
        \Closure $proceed,
        $product
    ) {
        $params = ['product' => $product->getId()];
        if ($product->getVendorId()) {
            $params['vendor_id'] = $product->getVendorId();
        }
        return $this->postHelper->getPostData($subject->getAddUrl(), $params);
    }
}
