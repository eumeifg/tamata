<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;

class ResizedProductImages
{
    /**
     * @var \Magedelight\Catalog\Model\Product\View\Gallery
     */
    protected $galleryProcessor;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * ResizedProductImages constructor.
     * @param \Magedelight\Catalog\Model\Product\View\Gallery $galleryProcessor
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magedelight\Catalog\Model\Product\View\Gallery $galleryProcessor,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->galleryProcessor = $galleryProcessor;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get media gallery entries
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]|null
     */
    public function afterGetMediaGalleryEntries(
        ProductInterface $entity,
        $result
    ) {
        $images = $this->galleryProcessor->getGalleryImages($entity)->getItems();
        foreach ($result as $mediaGalleryEntry){
            $extensionAttributes = $mediaGalleryEntry->getExtensionAttributes();
            foreach ($images as $image){
                if($image->getId() ===  $mediaGalleryEntry->getId()){
                    $imageResize = $this->imageHelper->init($entity, 'image', ['type' => 'image'])
                        ->constrainOnly(true)
                        ->keepAspectRatio(true)
                        ->keepTransparency(true)
                        ->resize(400, 570);
                    $extensionAttributes->setMobileImage($imageResize->getUrl());
                    $extensionAttributes->setResizedMediumImage($image->getData('medium_image_url'));
                    $mediaGalleryEntry->setExtensionAttributes($extensionAttributes);
                }
            }
        }
        return $result;
    }
}
