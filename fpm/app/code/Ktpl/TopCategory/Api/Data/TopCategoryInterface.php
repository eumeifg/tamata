<?php
/**
 * php version 7.2.17
 */
namespace Ktpl\TopCategory\Api\Data;

/**
 * Top Category interface.
 *
 * @api
 * @since 100.0.2
 */
interface TopCategoryInterface
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
}
