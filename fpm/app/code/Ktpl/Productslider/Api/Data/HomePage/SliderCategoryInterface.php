<?php
namespace Ktpl\Productslider\Api\Data\HomePage;

/**
 * @api
 */
interface SliderCategoryInterface
{

    const IMAGE_PATH = 'image_path';
    const CATEGORY_ID = 'category_id';
    const HEIGHT ='height';
    const WIDTH ='width';
    const PAGE_TYPE = 'page_type';
    const LAYOUT = 'layout';

    /**
     * Get category id
     * @return int
     */
    public function getCategoryId();

    /**
     * Set category id
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId);

    /**
     * Get Category Image Path
     * @return string
     */
    public function getImagePath();

    /**
     * Set Category Image Path
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath);

    /**
     * Get Height
     * @return array
     */
    public function getHeight();

    /**
     * Set Height
     * @param array $height
     * @return $this
     */
    public function setHeight(array $height);

    /**
     * Get Width
     * @return string
     */
    public function getWidth();

    /**
     * Set Width
     * @param string $width
     * @return $this
     */
    public function setWidth($height);

    /**
     * Get PageType
     * @return string
     */
    public function getPageType();

    /**
     * Set PageType
     * @param string $pageType
     * @return $this
     */
    public function setPageType($pageType);

    /**
     * Get PageType
     * @return string
     */
    public function getLayout();

    /**
     * Set PageType
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout);
}
