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

class Bundle
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;
    /**
     * @var \Magedelight\ProductAlert\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magedelight\ProductAlert\Helper\Data $_helper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->_helper = $_helper;
    }

    public function afterToHtml(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject, $html)
    {
        $product = $subject->getProduct();

        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        $json = [];
        foreach ($selectionCollection as $item) {
            /*generate information only for out of stock items*/
            if ($item->getData('md_native_is_salable') == 0) {
                $json[$item->getId()] = [
                    'is_salable' => $item->getData('md_native_is_salable'),
                    'alert'      => $this->_helper->getStockAlert($item)
                ];
            }
        }

        $json = $this->jsonEncoder->encode($json);
        $html = '<script>
                    window.md_json_config = ' . $json . '
                </script>'
            . $html;

        return $html;
    }
}
