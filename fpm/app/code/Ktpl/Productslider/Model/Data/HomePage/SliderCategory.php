<?php
namespace Ktpl\Productslider\Model\Data\HomePage;

use Ktpl\Productslider\Api\Data\HomePage\SliderCategoryInterface;

class SliderCategory extends \Magento\Framework\DataObject implements SliderCategoryInterface
{

    /**
     * {@inheritDoc}
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }


    /**
     * {@inheritDoc}
     */
    public function getImagePath()
    {
        return $this->getData(self::IMAGE_PATH);
    }

    /**
     * {@inheritDoc}
     */
    public function setImagePath($imagePath)
    {
        return $this->setData(self::IMAGE_PATH, $imagePath);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    /**
     * {@inheritDoc}
     */
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }
    /**
     * {@inheritDoc}
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * {@inheritDoc}
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * {@inheritDoc}
     */
    public function getPageType()
    {
        return $this->getData(self::PAGE_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPageType($width)
    {
        return $this->setData(self::PAGE_TYPE, $width);
    }

    /**
     * {@inheritDoc}
     */
    public function getLayout()
    {
        return $this->getData(self::LAYOUT);
    }

    /**
     * {@inheritDoc}
     */
    public function setLayout($layout)
    {
        return $this->setData(self::LAYOUT, $layout);
    }
}
