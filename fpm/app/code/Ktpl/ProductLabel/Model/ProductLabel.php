<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel as ProductLabelResource;

class ProductLabel extends AbstractModel implements IdentityInterface, ProductLabelInterface
{
    const CACHE_TAG = 'ktpl_productlabel';

    protected $storeManager;

    private $imageUploader;

    protected $fileInfo;

    protected $mediaDirectory;

    protected $cacheTag = self::CACHE_TAG;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager   = $storeManager;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG ];
    }

    public function isActive()
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    public function getProductLabelId()
    {
        return $this->getId();
    }

    public function getStores()
    {
        $stores = $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');

        if (is_numeric($stores)) {
            $stores = [$stores];
        }

        return $stores ?? [];
    }

    public function getName()
    {
        return (int) $this->getData(self::PRODUCTLABEL_NAME);
    }

    public function getAttributeId(): int
    {
        return (int) $this->getData(self::ATTRIBUTE_ID);
    }

    public function getOptionId(): int
    {
        return (int) $this->getData(self::OPTION_ID);
    }

    public function getProductLabelImage()
    {
        return (string) $this->getData(self::PRODUCTLABEL_IMAGE);
    }

    public function getPositionCategoryList()
    {
        return (string) $this->getData(self::PRODUCTLABEL_POSITION_CATEGORY_LIST);
    }

    public function getPositionProductView()
    {
        return (string) $this->getData(self::PRODUCTLABEL_POSITION_PRODUCT_VIEW);
    }

    public function getDisplayOn()
    {
        $values = $this->getData(self::PRODUCTLABEL_DISPLAY_ON);
        if (is_numeric($values)) {
            $values = [$values];
        }

        return $values ? $values : [];
    }

    public function getLabeltype()
    {
        return (string) $this->getData(self::PRODUCTLABEL_LABELTYPE);
    }

    public function getTextcolorpicker()
    {
        return (string) $this->getData(self::PRODUCTLABEL_TEXTCOLORPICKER);
    }

    public function getAlt()
    {
        return (int) $this->getData(self::PRODUCTLABEL_ALT);
    }

    public function setIsActive(bool $status)
    {
        return $this->setData(self::IS_ACTIVE, (bool) $status);
    }

    public function setProductLabelId($value)
    {
        return $this->setId((int) $value);
    }

    public function setName($value)
    {
        return $this->setData(self::PRODUCTLABEL_NAME, (string) $value);
    }

    public function setAttributeId(int $value)
    {
        return $this->setData(self::ATTRIBUTE_ID, $value);
    }

    public function setOptionId(int $value)
    {
        return $this->setData(self::OPTION_ID, $value);
    }

    public function setImage($value)
    {
        return $this->setData(self::PRODUCTLABEL_IMAGE, $value);
    }

    public function setPositionCategoryList($value)
    {
        return $this->setData(self::PRODUCTLABEL_POSITION_CATEGORY_LIST, $value);
    }

    public function setPositionProductView($value)
    {
        return $this->setData(self::PRODUCTLABEL_IMAGE, $value);
    }

    public function setDisplayOn($value)
    {
        return $this->setData(self::PRODUCTLABEL_DISPLAY_ON, $value);
    }

    public function setLabeltype($value)
    {
        return $this->setData(self::PRODUCTLABEL_LABELTYPE, (string) $value);
    }

    public function setTextcolorpicker($value)
    {
        return $this->setData(self::PRODUCTLABEL_TEXTCOLORPICKER, (string) $value);
    }

    public function setAlt($value)
    {
        return $this->setData(self::PRODUCTLABEL_ALT, (string) $value);
    }

    public function populateFromArray(array $values)
    {
        $this->setData(self::IS_ACTIVE, (bool) $values['is_active']);
        $this->setData(self::PRODUCTLABEL_NAME, (string) $values['name']);
        $this->setData(self::ATTRIBUTE_ID, (int) $values['attribute_id']);
        $this->setData(self::OPTION_ID, (int) $values['option_id']);
        if (isset($values['image'][0]['name'])) {
            $this->setData(self::PRODUCTLABEL_IMAGE, $values['image'][0]['name']);
        } else {
            $this->setData(self::PRODUCTLABEL_IMAGE, null);
        }
        $this->setData(self::PRODUCTLABEL_POSITION_CATEGORY_LIST, (string) $values['position_category_list']);
        $this->setData(self::PRODUCTLABEL_POSITION_PRODUCT_VIEW, (string) $values['position_product_view']);
        $this->setData(self::PRODUCTLABEL_DISPLAY_ON, implode(',', $values['display_on']));
        $this->setData(self::PRODUCTLABEL_LABELTYPE, (string) $values['labeltype']);
        $this->setData(self::PRODUCTLABEL_TEXTCOLORPICKER, (string) $values['textcolorpicker']);
        $this->setData(self::PRODUCTLABEL_ALT, (string) $values['alt']);
        $this->setData(self::STORE_ID, implode(',', $values['stores'] ?? $values['store_id']));
    }

    public function getImageUrl()
    {
        $url   = false;
        $image = $this->getData('image');
        if ($image) {
            if (is_string($image)) {
                $store = $this->storeManager->getStore();

                $isRelativeUrl = substr($image, 0, 1) === '/';

                $mediaBaseUrl = $store->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );

                $url = $mediaBaseUrl
                    . ltrim(\Ktpl\ProductLabel\Model\ImageLabel\FileInfo::ENTITY_MEDIA_PATH, '/')
                    . '/'
                    . $image;

                if ($isRelativeUrl) {
                    $url = $image;
                }
            }
        }

        return $url;
    }

    public function afterSave()
    {
        $imageName = $this->getData('image');
        $path      = $this->getImageUploader()->getFilePath($this->imageUploader->getBaseTmpPath(), $imageName);

        /* if ($this->mediaDirectory->isExist($path)) {
            $this->getImageUploader()->moveFileFromTmp($imageName);
        }*/

        return parent::afterSave();
    }

    protected function _construct()
    {
        $this->_init(ProductLabelResource::class);
    }

    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Ktpl\ProductLabel\ProductLabelImageUpload::class);
        }

        return $this->imageUploader;
    }
}
