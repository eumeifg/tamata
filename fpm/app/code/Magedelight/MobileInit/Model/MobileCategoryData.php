<?php
namespace Magedelight\MobileInit\Model;

use Magedelight\MobileInit\Api\Data\MobileCategoryDataInterface;

class MobileCategoryData extends \Magento\Framework\DataObject implements MobileCategoryDataInterface
{
    /**
     * @inheritdoc
     */
    public function setCategoryLabel($categoryLabel)
    {
           return $this->setData('label', $categoryLabel);
    }

   /**
    * @inheritdoc
    */
    public function getCategoryLabel()
    {
           return $this->getData('label');
    }

   /**
    * @inheritdoc
    */
    public function setCategoryId($categoryId)
    {
           return $this->setData('id', $categoryId);
    }

   /**
    * @inheritdoc
    */
    public function getCategoryId()
    {
           return $this->getData('id');
    }

   /**
    * @inheritdoc
    */
    public function setContentType($catContentType)
    {
           return $this->setData('contentType', $catContentType);
    }

   /**
    * @inheritdoc
    */
    public function getContentType()
    {
           return $this->getData('contentType');
    }

   /**
    * @inheritdoc
    */
    public function getChildrenData()
    {
        return $this->getData('children_data');
    }

    /**
     * @inheritdoc
     */
    public function setChildrenData(array $childrenData = null)
    {
        return $this->setData('children_data', $childrenData);
    }

    /**
     * @inheritdoc
     */
    public function setIsSelected($isSelected)
    {
        return $this->setData('is_selected', $isSelected);
    }

    /**
     * @inheritdoc
     */
    public function getIsSelected()
    {
        return $this->getData('is_selected');
    }

    /**
     * @inheritdoc
     */
    public function setCategoryIcon($categoryIcon)
    {
        return $this->setData('category_icon', $categoryIcon);
    }

   /**
    * @inheritdoc
    */
    public function getCategoryIcon()
    {
        return $this->getData('category_icon');
    }

    /**
     * @inheritdoc
     */
    public function setMobileCategoryBanner($mobileCategoryBanner)
    {
        return $this->setData('mobile_category_banner', $mobileCategoryBanner);
    }

   /**
    * @inheritdoc
    */
    public function getMobileCategoryBanner()
    {
        return $this->getData('mobile_category_banner');
    }

    /**
     * @inheritdoc
     */
    public function setMobileCategoryImage($mobileCategoryImage)
    {
        return $this->setData('mobile_category_image', $mobileCategoryImage);
    }

   /**
    * @inheritdoc
    */
    public function getMobileCategoryImage()
    {
        return $this->getData('mobile_category_image');
    }
}
