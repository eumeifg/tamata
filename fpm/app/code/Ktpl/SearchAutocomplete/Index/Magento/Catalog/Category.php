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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\SearchAutocomplete\Index\AbstractIndex;

/**
 * Class Category
 *
 * @package Ktpl\SearchAutocomplete\Index\Magento\Catalog
 */
class Category extends AbstractIndex
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Category constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($this->getCollection() as $category) {
            $items[] = $this->mapCategory($category);
        }

        return $items;
    }

    /**
     * Map category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function mapCategory($category)
    {
        return [
            'name' => $this->getFullPath($category),
            'url' => $category->getUrl(),
        ];
    }

    /**
     * List of parent categories
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getFullPath(CategoryInterface $category)
    {
        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

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

        return implode('<i>›</i>', $result);
    }

    /**
     * Map data
     *
     * @param $data
     * @return mixed
     */
    public function map($data)
    {
        foreach ($data as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create('Magento\Catalog\Model\Category')->load($entityId);

            $map = $this->mapCategory($entity);
            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }
}
