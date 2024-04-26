<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Magento\Catalog\Model\Config;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields data
 */
class SortFields extends \Magento\CatalogGraphQl\Model\Resolver\Category\SortFields
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var Sortby
     */
    private $sortbyAttributeSource;

    protected $storeManager;

    /**
     * @param Config $catalogConfig
     * @param Sortby $sortbyAttributeSource
     */
    public function __construct(
        Config $catalogConfig,
        Sortby $sortbyAttributeSource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->sortbyAttributeSource = $sortbyAttributeSource;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        $sortFieldsOptions = $this->sortbyAttributeSource->getAllOptions();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if ($value['layer_type'] === 'search') {
            $sortFieldsOptions[] = ['label' => __('Relevance'), 'value' => 'relevance'];
        }

        array_walk(
            $sortFieldsOptions,
            function (&$option) {
                $option['label'] = (string)$option['label'];
            }
        );

        foreach ($sortFieldsOptions as $key => $value) {
             
            if($storeId === 2){
                if($value['label'] === "Position"){
                    $sortFieldsOptions[$key]['label'] = "موقع";
                }
                if($value['label'] === "Product Name"){
                    $sortFieldsOptions[$key]['label'] = "اسم المنتج";
                }
                if($value['label'] === "Price"){
                    $sortFieldsOptions[$key]['label'] = "السعر";
                }
                if($value['label'] === "Visibility"){
                    $sortFieldsOptions[$key]['label'] = "الرؤية";
                }
                if($value['label'] === "Popularity"){
                    $sortFieldsOptions[$key]['label'] = "شعبية";
                }
            }
        }        

        $data = [
            'default' => $this->catalogConfig->getProductListDefaultSortBy($storeId),
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
