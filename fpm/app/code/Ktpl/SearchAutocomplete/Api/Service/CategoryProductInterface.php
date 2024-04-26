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

namespace Ktpl\SearchAutocomplete\Api\Service;

/**
 * Interface CategoryProductInterface
 *
 * @package Ktpl\SearchAutocomplete\Api\Service
 */
interface CategoryProductInterface
{
    /**
     * Get category product collection
     *
     * @param IndexInterface $index
     * @return array
     */
    public function getCollection($index);
}
