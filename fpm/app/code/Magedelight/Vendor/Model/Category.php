<?php
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\CategoryDataInterface;

class Category extends \Magento\Framework\DataObject implements CategoryDataInterface
{
    /**
     * @inheritdoc
     */
    public function setCategoryLabel($categoryLabel)
    {
           return $this->setData('label', $categoryLabel);
    }

   /**
    * @return string.
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
    * @return int.
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
    * @return string.
    */
    public function getContentType()
    {
           return $this->getData('contentType');
    }

   /**
    * @return $childrenData
    */
    public function getChildrenData()
    {
        return $this->getData('children_data');
    }

    /**
     * @param  $childrenData
     * @return $this
     */
    public function setChildrenData(array $childrenData = null)
    {
        return $this->setData('children_data', $childrenData);
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
}
