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

namespace Ktpl\SearchLanding\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Ktpl\SearchLanding\Api\Data\PageInterface;
use Ktpl\SearchLanding\Api\Data\PageInterfaceFactory;
use Ktpl\SearchLanding\Api\Repository\PageRepositoryInterface;
use Ktpl\SearchLanding\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class PageRepository
 *
 * @package Ktpl\SearchLanding\Repository
 */
class PageRepository implements PageRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageInterfaceFactory
     */
    private $pageFactory;

    /**
     * PageRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param PageInterfaceFactory $pageFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        PageInterfaceFactory $pageFactory
    )
    {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Get collection
     *
     * @return PageInterface[]|\Ktpl\SearchLanding\Model\ResourceModel\Page\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Get page by id
     *
     * @param int $id
     * @return bool|PageInterface|mixed
     */
    public function get($id)
    {
        $page = $this->create();
        $page = $this->entityManager->load($page, $id);

        if (!$page->getId()) {
            return false;
        }

        return $page;
    }

    /**
     * Crate page factory instance
     *
     * @return PageInterface
     */
    public function create()
    {
        return $this->pageFactory->create();
    }

    /**
     * Delete page
     *
     * @param PageInterface $page
     * @return PageRepositoryInterface|void
     * @throws \Exception
     */
    public function delete(PageInterface $page)
    {
        $this->entityManager->delete($page);
    }

    /**
     * Save page
     *
     * @param PageInterface $page
     * @return PageInterface|object
     * @throws \Exception
     */
    public function save(PageInterface $page)
    {
        return $this->entityManager->save($page);
    }
}