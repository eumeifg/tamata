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

namespace Ktpl\ElasticSearch\Api\Service;

/**
 * Interface QueryServiceInterface
 * @package Ktpl\ElasticSearch\Api\Service
 */
interface QueryServiceInterface
{
    /**
     * Build query
     *
     * @param string $query
     * @return array
     */
    public function build($query);
}
