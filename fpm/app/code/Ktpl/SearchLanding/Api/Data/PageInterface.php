<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchLanding
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchLanding\Api\Data;

/**
 * Interface PageInterface
 *
 * @package Ktpl\SearchLanding\Api\Data
 */
interface PageInterface
{
    const TABLE_NAME = 'ktpl_search_landing_page';

    const ID = 'page_id';
    const QUERY_TEXT = 'query_text';
    const URL_KEY = 'url_key';
    const TITLE = 'title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const LAYOUT_UPDATE = 'layout_update';
    const STORE_IDS = 'store_ids';
    const IS_ACTIVE = 'is_active';

    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get query text
     *
     * @return string
     */
    public function getQueryText();

    /**
     * Set query text
     *
     * @param string $value
     * @return $this
     */
    public function setQueryText($value);

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Set url key
     *
     * @param string $value
     * @return $this
     */
    public function setUrlKey($value);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $value
     * @return $this
     */
    public function setTitle($value);

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords();

    /**
     * Set meta keywords
     *
     * @param string $value
     * @return $this
     */
    public function setMetaKeywords($value);

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set meta description
     *
     * @param string $value
     * @return $this
     */
    public function setMetaDescription($value);

    /**
     * Get layout update
     *
     * @return string
     */
    public function getLayoutUpdate();

    /**
     * Set layout update
     *
     * @param string $value
     * @return $this
     */
    public function setLayoutUpdate($value);

    /**
     * Get store ids
     *
     * @return string
     */
    public function getStoreIds();

    /**
     * Set store ids
     *
     * @param string $value
     * @return $this
     */
    public function setStoreIds($value);

    /**
     * Get is active
     *
     * @return bool
     */
    public function isActive();

    /**
     * Set is active
     *
     * @param bool $value
     * @return $this
     */
    public function setIsActive($value);
}
