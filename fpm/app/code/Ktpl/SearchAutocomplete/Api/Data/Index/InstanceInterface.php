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

namespace Ktpl\SearchAutocomplete\Api\Data\Index;

use Ktpl\SearchAutocomplete\Api\Data\IndexInterface;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Interface InstanceInterface
 *
 * @package Ktpl\SearchAutocomplete\Api\Data\Index
 */
interface InstanceInterface
{
    /**
     * Get items
     *
     * @return array
     */
    public function getItems();

    /**
     * Get size
     *
     * @return int
     */
    public function getSize();

    /**
     * Set index
     *
     * @param IndexInterface $index
     * @return $this
     */
    public function setIndex($index);

    /**
     * Set index repository
     *
     * @param IndexRepositoryInterface $indexRepository
     * @return $this
     */
    public function setRepository(IndexRepositoryInterface $indexRepository);
}
