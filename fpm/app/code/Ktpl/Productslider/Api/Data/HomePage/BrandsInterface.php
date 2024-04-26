<?php

namespace Ktpl\Productslider\Api\Data\HomePage;

interface BrandsInterface
{
    const CATEGORY_ID = 'category_id';
    const IMAGE_PATH = 'image_path';
    const PAGE_TYPE = 'page_type';
    const TITLE = 'title';

    /**
     * Get Category ID
     * @return int
     */
    public function getCategoryId();

    /**
     * Get Category ID
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId);

    /**
     * Get Image path
     * @return string
     */
    public function getImagePath();

    /**
     * Set Image path
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath);

    /**
     * Get Page Type
     * @return string
     */
    public function getPageType();

    /**
     * Set Page Type
     * @param string $pageType
     * @return $this
     */
    public function setPageType($pageType);

    /**
     * Get Title
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

}
