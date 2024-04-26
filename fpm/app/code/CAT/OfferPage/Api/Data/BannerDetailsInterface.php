<?php

namespace CAT\OfferPage\Api\Data;

interface BannerDetailsInterface
{
    const PAGE_TYPE = 'page_type';
    const DATA_ID = 'data_id';
    const IMAGE_URL = 'image_url';
    const LAYOUT = 'layout';
    const IMAGES = 'images';

    /**
     * @return mixed
     */
    public function getPageType();

    /**
     * @param $pageType
     * @return mixed
     */
    public function setPageType($pageType);

    /**
     * @return mixed
     */
    public function getDataId();

    /**
     * @param $dataId
     * @return mixed
     */
    public function setDataId($dataId);

    /**
     * @return mixed
     */
    public function getImageUrl();

    /**
     * @param $imageUrl
     * @return mixed
     */
    public function setImageUrl($imageUrl);

    /**
     * @return mixed
     */
    public function getLayout();

    /**
     * @param $layout
     * @return mixed
     */
    public function setLayout($layout);

    /**
     * @return mixed
     */
    public function getImages();

    /**
     * @param $images
     * @return mixed
     */
    public function setImages($images);

}
