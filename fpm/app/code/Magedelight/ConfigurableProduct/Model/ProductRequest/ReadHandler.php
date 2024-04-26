<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ConfigurableProduct
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ConfigurableProduct\Model\ProductRequest;

use Magedelight\Catalog\Api\Data\ProductRequestInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\ProductRequest
     */
    protected $productRequestResource;

    /**
     * @var VariationsBuilder
     */
    protected $variationsBuilder;

    /**
     * ReadHandler constructor.
     * @param \Magedelight\Catalog\Model\ResourceModel\ProductRequest $productRequestResource
     */
    public function __construct(
        \Magedelight\Catalog\Model\ResourceModel\ProductRequest $productRequestResource,
        \Magedelight\ConfigurableProduct\Model\ProductRequest\VariationsBuilder $variationsBuilder
    ) {
        $this->productRequestResource = $productRequestResource;
        $this->variationsBuilder = $variationsBuilder;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getTypeId() !== Configurable::TYPE_CODE) {
            return $entity;
        }

        $extensionAttributes = $entity->getExtensionAttributes();

        $extensionAttributes->setConfigurableProductLinks($this->getLinkedProducts($entity));

        $variantData = $this->variationsBuilder->create($entity);
        $extensionAttributes->setConfigurableProductOptions($variantData);

        $extensionAttributes->setConfigurableChildItems(
            $this->productRequestResource->getChildItems($entity->getId())
        );

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * Get linked to configurable simple products
     *
     * @param ProductRequestInterface $productRequest
     * @return int[]
     */
    private function getLinkedProducts($entity)
    {
        $childrenIds = $this->productRequestResource->getChildrenIds($entity->getId());

        if (isset($childrenIds[0])) {
            return $childrenIds[0];
        } else {
            return [];
        }
    }
}
