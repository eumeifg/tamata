<?php

namespace MDC\Catalog\Plugin\Catalog\Model\Category\Attribute\Backend;

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
        $smallImagelFiles = $category->getSmallImage();        
        $proceed($category);

        if (isset($smallImagelFiles[0]['name']) && $attributeName === "small_image") {
            $category->setSmallImage($smallImagelFiles[0]['name']);
        }    
    }
}
