<?php

namespace CAT\AIRecommend\Api\Data;

interface RecommenderBuilderResultsInterface
{
    const PRODUCTS = 'products';
    const TITLE = 'title';
    /**
     * Get Slider Products
     * @api
     * @return \Ktpl\Productslider\Api\Data\ProductInterface[]|null
     */
    public function getProducts();

    /**
     * Set Slider Products
     * @api
     * @param \Ktpl\Productslider\Api\Data\ProductInterface[] $products
     * @return $this
     */
    public function setProducts(array $products);

     /**
     * @return string
     */
    public function getTitle();
    /**
     * @param string $categoryTitle
     * @return $this
     */
    public function setTitle(string $title);
}
