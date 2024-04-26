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
namespace Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Gallery;

use Magedelight\Catalog\Block\Sellerhtml\Media\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\Element\AbstractBlock;

class Content extends \Magedelight\Catalog\Block\Sellerhtml\ProductRequest\Gallery\Widget
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', \Magedelight\Catalog\Block\Sellerhtml\Media\Uploader::class);

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->addSessionParam()->getUrl('rbcatalog/product_gallery/upload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve uploader block
     *
     * @return Uploader
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }
    /**
     *
     * @return string
     */
    public function getImagesJson()
    {
        try {
            $jsonImages = $this->getAttributeValue('images');
            if ($jsonImages != null && $jsonImages != '') {
                $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $images = json_decode($jsonImages, true);

                foreach ($images as &$image) {
                    $image['url'] = $this->mediaConfig->getTmpMediaUrl(str_replace('.tmp', '', $image['file']));

                    try {
                        $fileHandler = $directory->stat($this->mediaConfig->getTmpMediaPath(
                            str_replace('.tmp', '', $image['file'])
                        ));
                        $image['size'] = $fileHandler['size'];
                    } catch (FileSystemException $e) {
                        $image['url'] = $this->getImageHelper()->getDefaultPlaceholderUrl('small_image');
                        $image['size'] = 0;
                        $this->_logger->warning($e);
                    }
                }
                return $this->jsonEncoder->encode($images);
            }
        } catch (\Exception $e) {
            return '[]';
        }
        return '[]';
    }

    /**
     * @return string
     */
    public function getImagesValuesJson()
    {
        $values = [];

        return $this->jsonEncoder->encode($values);
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];

        foreach ($this->getMediaAttributes() as $attribute) {
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $this->getAttributeValue($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name' => $this->getElement()->getAttributeFieldName($attribute),
            ];
        }

        return $imageTypes;
    }

    /**
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        // return $this->getElement()->getDataObject()->getMediaAttributes();
        return $this->productFactory->create()->getMediaAttributes();
    }

    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->jsonEncoder->encode($this->getImageTypes());
    }

    public function getName()
    {
        return $this->getHtmlId();
    }

    /**
     * Get the Html Id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getHtmlIdPrefix() . 'product' . $this->getFieldNameSuffix();
    }

    public function getReadonly()
    {
        return false;
    }

    public function getBaseImage()
    {
        if ($this->isRequestResubmitted()) {
            if (!($this->getCurrentRequest()->getBaseImage() === null)) {
                return $this->_jsonDecoder->decode($this->getCurrentRequest()->getBaseImage());
            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated
     */
    private function getImageHelper()
    {
        if ($this->imageHelper === null) {
            $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Image::class);
        }
        return $this->imageHelper;
    }
}
