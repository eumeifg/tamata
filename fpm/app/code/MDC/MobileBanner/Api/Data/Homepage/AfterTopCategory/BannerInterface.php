<?php

namespace MDC\MobileBanner\Api\Data\Homepage\AfterTopCategory;

interface BannerInterface
{
    const IMAGE_PATH = 'image_path';
    const PAGE_TYPE = 'page_type';
    const DATA_ID = 'data_id';

    /**
     * get image_path
     *
     * @return string
     */
    public function getImagePath();

    /**
     * set image_path
     *
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath);

    /**
     * get image_path
     *
     * @return string
     */
    public function getPageType();

    /**
     * set image_path
     *
     * @param string $pageType
     * @return $this
     */
    public function setPageType($pageType);

    /**
     * get image_path
     *
     * @return string
     */
    public function getDataId();

    /**
     * set image_path
     *
     * @param int $dataId
     * @return $this
     */
    public function setDataId($dataId);


}
