<?php

namespace CAT\Custom\Model\WebApi;

use CAT\Custom\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_productRepository = $productRepository;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     */
    public function getProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $getList = $this->_productRepository->getList($searchCriteria);
        foreach ($getList->getItems() as $items) {
            $product = $this->_productRepository->getById($items->getEntityId());
            $boxNumber = !empty($product->getBoxNumber()) ? $product->getBoxNumber() : 'null';
            $items->setBoxNumber($boxNumber);
        }
        return $getList;
    }
}