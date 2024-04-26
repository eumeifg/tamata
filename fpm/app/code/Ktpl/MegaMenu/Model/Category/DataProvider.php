<?php

namespace Ktpl\MegaMenu\Model\Category;

use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;

class DataProvider extends CategoryDataProvider
{
    protected function getFieldsMap()
    {
        $parentFieldMap = parent::getFieldsMap();
        array_push($parentFieldMap['general'], 'menu_content');
        return $parentFieldMap;
    }
}
