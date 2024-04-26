<?php

namespace Ktpl\Productslider\Api\Data\HomePage;

interface NewItemsBannerInterface
{
    const IMAGE_PATH = 'image_path';
    const PAGE_TYPE = 'page_type';
    const DATA_ID = 'data_id';


    /**
     * Get category id
     * @return string
     */
    public function getImagePath();

    /**
     * Set category id
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath);

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
     * Get data id
     * @return int
     */
    public function getDataId();

    /**
     * Set data id
     * @param int $dataId
     * @return $this
     */
    public function setDataId($dataId);
}
