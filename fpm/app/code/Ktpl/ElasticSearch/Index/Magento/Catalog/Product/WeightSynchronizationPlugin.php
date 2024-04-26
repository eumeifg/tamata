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

namespace Ktpl\ElasticSearch\Index\Magento\Catalog\Product;

use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Magento\Eav\Model\Entity;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;

/**
 * Class WeightSynchronizationPlugin
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Catalog\Product
 */
class WeightSynchronizationPlugin
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var EavAttribute
     */
    private $eavAttribute;

    /**
     * WeightSynchronizationPlugin constructor.
     *
     * @param Entity $entity
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param EavAttribute $eavAttribute
     */
    public function __construct(
        Entity $entity,
        AttributeCollectionFactory $attributeCollectionFactory,
        EavAttribute $eavAttribute
    )
    {
        $this->entity = $entity;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * Set search weight
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexInterface $index
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(IndexRepositoryInterface $indexRepository, IndexInterface $index)
    {
        if ($index->getIdentifier() != 'catalogsearch_fulltext') {
            return;
        }

        $attributes = $index->getAttributes();

        if (!is_array($attributes) || count($attributes) == 0) {
            return;
        }

        $entityTypeId = $this->entity->setType(Product::ENTITY)->getTypeId();

        $collection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('is_searchable', 1);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($collection as $attribute) {
            if (!array_key_exists($attribute->getAttributeCode(), $attributes) && $attribute->getIsSearchable()) {
                $attribute->setIsSearchable(0)
                    ->save();
            }
        }

        foreach ($attributes as $code => $weight) {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->eavAttribute->loadByCode($entityTypeId, $code);
            if (!$attribute->getId()) {
                continue;
            }
            if ($attribute->getSearchWeight() != $weight || !$attribute->getIsSearchable()) {
                $attribute->setSearchWeight($weight)
                    ->setIsSearchable(1)
                    ->save();
            }
        }
    }
}
