<?php

namespace Ktpl\Dealszone\Plugin\Catalog\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;

class ImagePlugin
{
    /**
     * Fix for bad urls on 2.3.4
     * @param Image $subject
     * @param \Closure $proceed
     * @param $category
     */
    public function aroundBeforeSave(Image $subject, \Closure $proceed, $category)
    {      
        $attributeName = $subject->getAttribute()->getName();  
        $dealZoneImg = $category->getDealzoneImage();
        $proceed($category);

        if (isset($dealZoneImg[0]['name']) && $attributeName === "dealzone_image") {
            $category->setDealzoneImage($dealZoneImg[0]['name']);
        }
    }
}
