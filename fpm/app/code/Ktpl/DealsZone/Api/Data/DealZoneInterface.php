<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\DealsZone\Api\Data;

/**
 * Deal Zone interface.
 *
 * @api
 */
interface DealZoneInterface
{
    /**
     * #@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const NAME = 'name';
    const IMAGE = 'image';
    const URL_KEY = 'url_key';
    const URL_PATH = 'url_path';
    const THUMBNAIL = 'thumbnail';
    const DISCOUNT_LABEL = 'discount_label';
    const DEALZONE_IMG = 'dealzone_image';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param  int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

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
    public function getImage();
    /**
     * @param  string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param  string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * @return string
     */
    public function getUrlPath();

    /**
     * @param  string $path
     * @return $this
     */
    public function setUrlPath($path);

    /**
     * @return string
     */
    public function getThumbnail();

    /**
     * @param  string $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail);

    /**
     * @return string|null
     */
    public function getDiscountLabel();

    /**
     * @param  string|null $label
     * @return $this
     */
    public function setDiscountLabel($label);

    /**
     * @return string|null
     */
    public function getDealzoneImage();

    /**
     * @param  string|null $path
     * @return $this
     */
    public function setDealzoneImage($path);
}
