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

namespace Ktpl\SearchLanding\Api\Repository;

use Ktpl\SearchLanding\Api\Data\PageInterface;

/**
 * Interface PageRepositoryInterface
 *
 * @package Ktpl\SearchLanding\Api\Repository
 */
interface PageRepositoryInterface
{
    /**
     * Get collection
     *
     * @return \Ktpl\SearchLanding\Model\ResourceModel\Page\Collection | PageInterface[]
     */
    public function getCollection();

    /**
     * Create page interface
     *
     * @return PageInterface
     */
    public function create();

    /**
     * Get page by id
     *
     * @param int $id
     * @return PageInterface
     */
    public function get($id);

    /**
     * Save page
     *
     * @param PageInterface $page
     * @return PageInterface
     */
    public function save(PageInterface $page);

    /**
     * Delete page
     *
     * @param PageInterface $page
     * @return $this
     */
    public function delete(PageInterface $page);
}