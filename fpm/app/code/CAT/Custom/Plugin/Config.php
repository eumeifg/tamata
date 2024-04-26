<?php
namespace CAT\Custom\Plugin;

class Config
{
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $optionsnew = ['random' => __('Random Products')];
        $options = array_merge($options, $optionsnew);
        return $options;
    }
}