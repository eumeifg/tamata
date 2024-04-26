<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Sellerhtml\Bulkimport;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Attributeselector extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    protected $_productAttributeRepository;

    /**
     * @var SubCategoryInterface
     */
    protected $_subCategoryInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     *
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
     * @param \Magedelight\Catalog\Api\SubCategoryInterface $subCategoryInterface
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Catalog\Model\Category $category
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magedelight\Catalog\Api\SubCategoryInterface $subCategoryInterface,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        \Magento\Catalog\Model\Category $category,
        array $data = []
    ) {
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_subCategoryInterface = $subCategoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->vendorHelper = $vendorHelper;
        $this->category = $category;
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Allowed Categories List
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedTabCategories()
    {
        $vendorCats = [];
        foreach ($this->authSession->getUser()->getCategoryIds() as $category_id) {
            $category = $this->categoryRepositoryInterface->get($category_id);
            if ($category->getIsActive()) {
                $cats = explode('/', $category->getPath());
                $i = 1;
                foreach ($cats as $cat) {
                    if ($cat != 1 && $cat != 2) {
                        $cat = $this->categoryRepositoryInterface->get($cat);
                        if (count($cats) == $i) {
                            isset($vendorCats[$category->getId()]) ?
                                $vendorCats[$category->getId()] .= $cat->getName() :
                                $vendorCats[$category->getId()] = $cat->getName();
                        } else {
                            isset($vendorCats[$category->getId()]) ?
                                $vendorCats[$category->getId()] .= $cat->getName() . '&nbsp;&nbsp;&#xbb;&nbsp;&nbsp;' :
                                $vendorCats[$category->getId()] = $cat->getName() . '&nbsp;&nbsp;&#xbb;&nbsp;&nbsp;';
                        }
                    }
                    $i++;
                }
            }
        }
        asort($vendorCats);
        return $vendorCats;
    }

    /**
     * Retrieve Categories List
     * @return string
     */
    public function getAllowedCategories()
    {
        return $this->_subCategoryInterface->getAllowedCategories();
    }

    public function getAllowedAttributeOptions($att_arry)
    {
        foreach ($att_arry as $array_info => $value) {
            $object_manager = $this->category->load($array_info);
            if (is_array($value)) {
                foreach ($value as $att_id => $att_code) {
                    $manufacturerOptions = $this->_productAttributeRepository->get($att_code)->getOptions();
                    if ($manufacturerOptions) {
                        foreach ($manufacturerOptions as $manufacturerOption) {
                            $att_array[$object_manager->getName()][$att_code][$manufacturerOption->getValue()] =
                                $manufacturerOption->getLabel();
                        }
                    }
                }
            }
        }
        return $att_array;
    }
}
