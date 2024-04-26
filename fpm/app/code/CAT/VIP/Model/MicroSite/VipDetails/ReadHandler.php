<?php

namespace CAT\VIP\Model\MicroSite\VipDetails;

use Exception;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use CAT\VIP\Model\Resolver\Product\VipPrice;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ReadHandler implements ExtensionInterface
{

    /**
     * @var VipPrice
     */
    protected $_vipPrice;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param VipPrice $vipPrice
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        VipPrice                   $vipPrice,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->_vipPrice = $vipPrice;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute($entity, $arguments = [])
    {
        $extension = $entity->getExtensionAttributes();

        if ($extension->getVipPrice() !== null) {
            return $entity;
        }

        $vipPrice = false;
        if ($entity->getTypeId() == 'configurable') {
            $product = $this->productRepository->getById($entity->getId());
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProducts as $childProduct) {
                $vipPrice = $this->_vipPrice->isCheckVip($childProduct->getId());
                if ($vipPrice !== false) {
                    break;
                }
            }
        } else {
            $vipPrice = $this->_vipPrice->isCheckVip($entity->getId());
        }
        $extension->setVipPrice($vipPrice);
        $entity->setExtensionAttributes($extension);
        return $entity;
    }
}
