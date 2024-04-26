<?php

namespace MDC\Custom\Model\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Category\FileInfo;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['general'][] = 'mobile_category_banner'; // custom image field

        return $fields;
    }
}
