<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Index\Magento\Catalog\Attribute;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Source
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Catalog\Attribute
 */
class Source implements ArrayInterface
{
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var bool
     */
    private $toBuild = true;

    /**
     * Source constructor.
     *
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory
    )
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Get attribute options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->toBuild) {
            $this->toBuild = false;

            $collection = $this->attributeCollectionFactory->create()
                ->addVisibleFilter()
                ->addDisplayInAdvancedSearchFilter()
                ->setOrder('attribute_id', 'asc');

            foreach ($collection as $attribute) {
                $attributeOptions = $attribute->getSource()->getAllOptions(true);
                if (count($attributeOptions) > 1) {
                    $options[] = [
                        'value' => $attribute->getAttributeCode(),
                        'label' => $attribute->getDefaultFrontendLabel() . ' [' . $attribute->getAttributeCode() . ']',
                    ];
                }
            }
        }

        return $options;
    }
}
