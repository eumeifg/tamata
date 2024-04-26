<?php

namespace MDC\Custom\Model\Resolver\Category;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Magento\Catalog\Model\Config;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SortFields extends \Magedelight\CatalogGraphQl\Model\Resolver\Category\SortFields
{
    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var Sortby
     */
    private $sortbyAttributeSource;

    /**
     * SortFields constructor.
     * @param Config $catalogConfig
     * @param Sortby $sortbyAttributeSource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $catalogConfig,
        Sortby $sortbyAttributeSource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->sortbyAttributeSource = $sortbyAttributeSource;
        parent::__construct($catalogConfig, $sortbyAttributeSource, $storeManager);
    }

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
            // if ($value['value'] === "name"){unset($sortFieldsOptions[$key]);}
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
                if($value['label'] === "Random"){
                    $sortFieldsOptions[$key]['label'] = "عشوائي";
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