<?php

namespace CAT\AIRecommend\Model\Data;

use CAT\AIRecommend\Api\Data\RecommenderBuilderResultsInterface;
use Magento\Framework\DataObject;

class RecommenderBuilderResults extends DataObject implements RecommenderBuilderResultsInterface
{

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * @inheritDoc
     */
    public function setProducts(array $products)
    {
        return $this->setData(self::PRODUCTS, $products);
    }
}
