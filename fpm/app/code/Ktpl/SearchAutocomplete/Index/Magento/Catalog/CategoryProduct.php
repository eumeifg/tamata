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

namespace Ktpl\SearchAutocomplete\Index\Magento\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Framework\UrlFactory;
use Ktpl\SearchAutocomplete\Api\Service\CategoryProductInterface;

/**
 * Class CategoryProduct
 *
 * @package Ktpl\SearchAutocomplete\Index\Magento\Catalog
 */
class CategoryProduct extends AbstractIndex
{
    /**
     * @var UrlFactory
     */
    protected $urlFactory;
    /**
     * @var $categoryProductInterface ;
     */
    protected $categoryProduct;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var CategoryRepositoryInterface
     */
    private $searchHelper;

    /**
     * CategoryProduct constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchHelper $searchHelper
     * @param UrlFactory $urlFactory
     * @param CategoryProductInterface $categoryProduct
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        SearchHelper $searchHelper,
        UrlFactory $urlFactory,
        CategoryProductInterface $categoryProduct
    )
    {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->searchHelper = $searchHelper;
        $this->urlFactory = $urlFactory;
        $this->categoryProduct = $categoryProduct;
    }

    /**
     * Get items
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        $items = [];
        $collection = $this->categoryProduct->getCollection($this->index);

        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        foreach ($collection as $category) {
            $url = $this->urlFactory->create()
                ->setQueryParam('q', $this->searchHelper->getEscapedQueryText())
                ->setQueryParam('cat', $category->getId())
                ->getUrl('catalogsearch/result');

            $items[] = [
                'name' => $this->getFullPath($category, $rootId),
                'url' => $url,
            ];

            if (count($items) >= $this->index->getLimit()) {
                break;
            }
        }
        return $items;
    }

    /**
     * List of parent categories
     *
     * @param CategoryInterface $category
     * @param Variable $rootId current store root category Id
     * @return string
     */
    public function getFullPath(CategoryInterface $category, $rootId)
    {
        $result = [
            $category->getName(),
        ];

        do {
            if (!$category->getParentId()) {
                break;
            }
            $category = $this->categoryRepository->get($category->getParentId());

            if (!$category->getIsActive() && $category->getId() != $rootId) {
                break;
            }

            if ($category->getId() != $rootId) {
                $result[] = $category->getName();
            }
        } while ($category->getId() != $rootId);

        $result = array_reverse($result);

        return $this->searchHelper->getEscapedQueryText() . __(' in ') . implode(' > ', $result);
    }
}