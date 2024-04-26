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

namespace Ktpl\ElasticSearch\Block\Index\Magento\Catalog;

use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\ElasticSearch\Api\Service\IndexServiceInterface;
use Ktpl\ElasticSearch\Block\Index\Base;
use Magento\Catalog\Helper\Output;

/**
 * Class Category
 *
 * @package Ktpl\ElasticSearch\Block\Index\Magento\Catalog
 */
class Category extends Base
{
    /**
     * @var Output
     */
    private $outputHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CatalogCategoryFactory
     */
    private $categoryFactory;

    /**
     * Category constructor.
     *
     * @param CatalogCategoryFactory $categoryFactory
     * @param Output $outputHelper
     * @param IndexServiceInterface $indexService
     * @param ObjectManagerInterface $objectManager
     * @param Context $context
     */
    public function __construct(
        CatalogCategoryFactory $categoryFactory,
        Output $outputHelper,
        IndexServiceInterface $indexService,
        ObjectManagerInterface $objectManager,
        Context $context
    )
    {
        $this->storeManager = $context->getStoreManager();
        $this->categoryFactory = $categoryFactory;
        $this->outputHelper = $outputHelper;

        parent::__construct($indexService, $objectManager, $context);
    }

    /**
     * List of parent categories
     *
     * @param int $categoryId
     * @return array
     */
    public function getFullPath($categoryId)
    {
        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $result = [];
        $id = $categoryId;

        do {
            $parent = $this->categoryFactory->create()
                ->load($id)
                ->getParentCategory();

            $id = $parent->getId();

            if (!$parent->getId()) {
                break;
            }

            if (!$parent->getIsActive() && $parent->getId() != $rootId) {
                break;
            }

            if ($parent->getId() != $rootId) {
                $result[] = $parent;
            }
        } while ($parent->getId() != $rootId);

        $result = array_reverse($result);

        return $result;
    }

    /**
     * Get category image.
     *
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryImage($item)
    {
        $category = $item->load($item->getId());
        $imgHtml = '';

        if ($imgUrl = $category->getImageUrl()) {
            $imgHtml = '<img src="' . $imgUrl . '" 
                alt="' . $this->escapeHtml($category->getName()) . '" 
                title="' . $this->escapeHtml($category->getName()) . '" 
                class="image" />';
            $imgHtml = $this->outputHelper->categoryAttribute($category, $imgHtml, 'image');
        }

        return $imgHtml;
    }
}
