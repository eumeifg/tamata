<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Api\Data;

/**
 * Interface IndexInterface
 *
 * @package Ktpl\ElasticSearch\Api\Data
 */
interface IndexInterface
{
    const TABLE_NAME = 'ktpl_search_index';

    const ID = 'index_id';
    const IDENTIFIER = 'identifier';
    const TITLE = 'title';
    const POSITION = 'position';
    const ATTRIBUTES_SERIALIZED = 'attributes_serialized';
    const PROPERTIES_SERIALIZED = 'properties_serialized';
    const STATUS = 'status';
    const IS_ACTIVE = 'is_active';

    const STATUS_READY = 1;
    const STATUS_INVALID = 0;

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Set identifier
     *
     * @param string $input
     * @return $this
     */
    public function setIdentifier($input);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $input
     * @return $this
     */
    public function setTitle($input);

    /**
     * Get position
     *
     * @return number
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param number $input
     * @return $this
     */
    public function setPosition($input);

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Set attributes
     *
     * @param array $value
     * @return $this
     */
    public function setAttributes($value);

    /**
     * Get properties
     *
     * @return array
     */
    public function getProperties();

    /**
     * Set properties
     *
     * @param array $value
     * @return $this
     */
    public function setProperties($value);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $input
     * @return $this
     */
    public function setStatus($input);

    /**
     * Get is active
     *
     * @return number
     */
    public function getIsActive();

    /**
     * Set is active
     *
     * @param number $input
     * @return $this
     */
    public function setIsActive($input);

    /**
     * Get property
     *
     * @param string $key
     * @return string
     */
    public function getProperty($key);

    /**
     * Get data
     *
     * @param string $key
     * @return mixed|array
     */
    public function getData($key = null);

    /**
     * Set Data
     * @param string|array $key
     * @param string|int|array $value
     * @return $this
     */
    public function setData($key, $value = null);

    /**
     * Add Data
     *
     * @param array $data
     * @return $this
     */
    public function addData(array $data);
}
