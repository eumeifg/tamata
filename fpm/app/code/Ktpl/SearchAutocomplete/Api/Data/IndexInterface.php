<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Api\Data;

/**
 * Interface IndexInterface
 *
 * @package Ktpl\SearchAutocomplete\Api\Data
 */
interface IndexInterface
{
    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get is active
     *
     * @return number
     */
    public function getIsActive();

    /**
     * Get order
     *
     * @return number
     */
    public function getOrder();

    /**
     * Get limit
     *
     * @return number
     */
    public function getLimit();

    /**
     * Add data
     *
     * @param array $data
     * @return $this
     */
    public function addData($data);
}
