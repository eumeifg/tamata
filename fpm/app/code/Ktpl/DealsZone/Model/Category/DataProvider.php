<?php
/**
 * Ktpl
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_DealsZone
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com/)
 * @license     https://https://www.KrishTechnolabs.com/
 */

namespace Ktpl\DealsZone\Model\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Category\FileInfo;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{

    /**
     * @var Filesystem
     */
    private $fileInfo;

    public function getData()
    {
        $data = parent::getData();
        $category = $this->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();
            $categoryData = $this->addUseConfigSettings($categoryData);
            $categoryData = $this->filterFields($categoryData);
            //$categoryData = $this->convertValues($category, $categoryData);

            $this->loadedData[$category->getId()] = $categoryData;
        }

        if (isset($data['custom_image'])) {
            unset($data['custom_image']);

            //create getCategoryThumbUrl in helper file
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper     = $objectManager->get(\Ktpl\DealsZone\Helper\Data::class);

            $data[$category->getId()]['custom_image'][0]['name'] = $category->getData('custom_image');
            $data[$category->getId()]['custom_image'][0]['url']  = $helper->getImageUrl($category->getData($imageType));
            $data[$category->getId()]['custom_image'][0]['size'] = isset($stat) ? $stat['size'] : 0;
            $data[$category->getId()]['custom_image'][0]['type'] = $mime;
        }

        return $data;
    }

    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'custom_image'; // custom image field

        return $fields;
    }

    /**
     * Get FileInfo instance
     *
     * @return FileInfo
     *
     * @deprecated 101.1.0
     */
    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }
}
