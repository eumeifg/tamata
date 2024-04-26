<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Api\Data;

use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * Banner interface.
 *
 * @api
 * @since 100.0.2
 */
interface BannerInterface
{
    /**
     * #@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BANNER_ID = 'banner_id';
    const NAME = 'name';
    const STATUS = 'status';
    const TYPE = 'type';
    const CONTENT = 'content';
    const IMAGE = 'image';
    const URL_BANNER = 'url_banner';
    const TITLE = 'title';
    const BANNER_TEXT = 'banner_text';
    const NEWTAB = 'newtab';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PAGE_TYPE = 'page_type';
    const DATA_ID = 'data_id';

    /**
     * @return int
     */
    public function getBannerId();
    /**
     * @param  int $id
     * @return $this
     */
    public function setBannerId($id);

    /**
     * @return string
     */
    public function getStatus();
    /**
     * @param  string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param  string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param  string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param  string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string[]
     */
    public function getImage();

    /**
     * @param  ImageContentInterface $image
     * @return $this
     */
    public function setImage($image = null);

    /**
     * @return string
     */
    public function getUrlBanner();

    /**
     * @param  string $url
     * @return $this
     */
    public function setUrlBanner($url);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param  string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getBannerText();

    /**
     * @param  string $title
     * @return $this
     */
    public function setBannerText($banner_text);

    /**
     * @return string
     */
    public function getNewtab();

    /**
     * @param  string $newTab
     * @return $this
     */
    public function setNewtab($newTab);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param  string $newTab
     * @return $this
     */
    public function setCreatedAt($createdAt);

     /**
      * @return string
      */
    public function getUpdatedAt();

    /**
     * @param  string $newTab
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

      /**
     * @return string
     */
    public function getPageType();

    /**
     * @param  string $pageType
     * @return $this
     */
    public function setPageType($pageType);

      /**
     * @return int
     */
    public function getDataId();

    /**
     * @param  int $dataId
     * @return $this
     */
    public function setDataId($dataId);
}
