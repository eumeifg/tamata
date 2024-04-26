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
namespace Magedelight\Catalog\Model\Source;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection
    ) {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_options = [];

        foreach ($this->getAttribute() as $attribute) {
            $_options [] = ['value' => $attribute->getAttributeId(), 'label' => $attribute->getStoreLabel()];
        }

        return $_options;
    }

    public function getAttribute()
    {
        $attributesCollection = $this->attributeCollection
                ->setEntityTypeFilter(4)
                ->setOrder('frontend_label', 'ASC')
                ->load()
                ->getItems();
        return $attributesCollection;
    }
}
