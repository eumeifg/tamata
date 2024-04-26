<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use \Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory as ProductLabelCollectionFactory;

class EavPlugin
{
    /**
     * Template for tooltip added to virtual attributes in product edit form.
     */
    const TOOLTIP_TEMPLATE = 'Ktpl_ProductLabel/form/element/helper/tooltip';

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var ProductLabelCollectionFactory
     */
    private $plabelCollectionFactory;

    public function __construct(
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        ProductLabelCollectionFactory $plabelCollectionFactory
    ) {
        $this->arrayManager          = $arrayManager;
        $this->plabelCollectionFactory = $plabelCollectionFactory;
    }

    public function aroundSetupAttributeMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject,
        callable $proceed,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        $groupCode,
        $sortOrder
    ) {
        $meta = $proceed($attribute, $groupCode, $sortOrder);

        if ($this->hasCalculatedValues($attribute)) {
            $configPath = ltrim($subject::META_CONFIG_PATH, \Magento\Framework\Stdlib\ArrayManager::DEFAULT_PATH_DELIMITER);

            $fieldConfig = [
                'tooltip' => [
                    'description' => __("This attribute is linked to a product label."),
                ],
                'tooltipTpl' => self::TOOLTIP_TEMPLATE,
            ];

            $meta = $this->arrayManager->merge($configPath, $meta, $fieldConfig);
        }

        return $meta;
    }

    private function hasCalculatedValues(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $result = false;

        if ($attribute->getAttributeId()) {
            $productLabelCollection = $this->plabelCollectionFactory->create();
            $productLabelCollection->addAttributeFilter($attribute);

            $result = $productLabelCollection->getSize() > 0;
        }

        return $result;
    }
}
