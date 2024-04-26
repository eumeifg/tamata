<?php

namespace MDC\Custom\Plugin\Catalog\Model\Category\Attribute\Backend;

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
        $mobCatBanner = $category->getMobileCategoryBanner();
        $mobCatImage = $category->getMobileCategoryImage();
        $proceed($category);

        if (isset($mobCatBanner[0]['name']) && $attributeName === "mobile_category_banner") {
            $category->setMobileCategoryBanner($mobCatBanner[0]['name']);
        }
        if (isset($mobCatImage[0]['name']) && $attributeName === "mobile_category_image") {
            $category->setMobileCategoryImage($mobCatImage[0]['name']);
        }
    }
}
