<?php

namespace Ktpl\CategoryView\Helper;

use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class ImageHoverData extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $galleryReadHandler;
    protected $imageHelper;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        GalleryReadHandler $galleryReadHandler,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function addGallery($product)
    {
        $this->galleryReadHandler->execute($product);
    }

    public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /** @var $image \Magento\Catalog\Model\Product\Image */
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'rollover_image_url',
                    $this->imageHelper->init($product, 'product_page_image_rollover')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
    }

    public function getHoverImage(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $rolloverImage = $product->getResource()->getAttribute('rollover')->getFrontend()->getValue($product);
        if(isset($rolloverImage))
            return $mediaUrl . "catalog/product" . $rolloverImage;
        return '';
    }

}