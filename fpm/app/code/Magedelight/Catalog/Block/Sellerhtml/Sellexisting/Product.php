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
namespace Magedelight\Catalog\Block\Sellerhtml\Sellexisting;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Product extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionForm;

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magedelight\Catalog\Model\Source\SellingTypes
     */
    protected $sellingTypes;

    /**
     * @var \Magedelight\Catalog\Model\Source\Manufacturer
     */
    protected $manufacturer;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesno;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     *
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Catalog\Model\Source\SellingTypes $sellingTypes
     * @param \Magedelight\Catalog\Model\Source\Manufacturer $manufacturer
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categoryCollectionFactory
     * @param \Magento\Backend\Model\Session $sessionForm
     * @param \Magedelight\Vendor\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Catalog\Model\Source\SellingTypes $sellingTypes,
        \Magedelight\Catalog\Model\Source\Manufacturer $manufacturer,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categoryCollectionFactory,
        \Magento\Backend\Model\Session $sessionForm,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        $this->yesno = $yesno;
        $this->manufacturer = $manufacturer;
        $this->sellingTypes = $sellingTypes;
        $this->authSession = $authSession;
        $this->_categoryCollectionFactory = $_categoryCollectionFactory;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->vendorHelper = $vendorHelper;
        $this->sessionForm = $sessionForm;
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
        foreach ($this->getCurrentVendorCategories() as $category_id) {
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

    public function getCurrentVendorCategories()
    {
        return $this->authSession->getUser()->getCategoryIds();
    }

    /**
     *
     * @return string[]
     */
    public function getSellingTypes()
    {
        return $this->sellingTypes->getAllOptions();
    }

    /**
     *
     * @return type
     */
    public function getManufacturer()
    {
        return $this->manufacturer->getAllOptions();
    }

    /**
     *
     * @return type
     */
    public function getYesNo()
    {
        return $this->yesno->toOptionArray();
    }

    /**
     *
     * @return type
     */
    public function getVendorId()
    {
        return $this->authSession->getUser()->getVendorId();
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();
        foreach ($collection as $category) {
            return $this->_getTreeCategories($category, false);
        }
    }

    /**
     * Retrieve tree Categories
     * @param $parent
     * @param $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getTreeCategories($parent, $isChild)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();

        $vendorCats = $this->authSession->getUser()->getCategory();

        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();
        $currentlevel = $parent->getLevel() + 1;
        $html = ($currentlevel == 3) ? '<ul class="category-ul level-' . $currentlevel . '" style="display:none">' :
            '<ul class="category-ul level-' . $currentlevel . '">';
        foreach ($collection as $category) {
            $class = 'level-' . $category->getLevel();
            if ($category->getLevel() > $currentlevel) {
                $html .= '<ul class="' . $class . '">';
            } elseif ($category->getLevel() < $currentlevel) {
                $html .= '</ul><ul class="' . $class . '">';
            }

            $childClass = '';
            if ($category->hasChildren()) {
                $childClass = ($category->getLevel() == 2) ? ' base has-children' : ' has-children';
            }

            $html .= '<li class="item level-' . $category->getLevel() . $childClass . '">';
            if (!$category->hasChildren()) {
                $checked = in_array($category->getId(), $vendorCats, true) ? ' checked = "checked"' : '';
                if ($checked) {
                    $html .= '<a id="' . $category->getId() . '" href="';
                    $html .= $this->getUrl(
                        'rbcatalog/bulkimport/downloadSample',
                        ['name' => $category->getName(), 'cid' => $category->getId()]
                    );
                    $html .= '" class="checkbox">' . $category->getName() . '</a>';
                } else {
                    $html .= $category->getName();
                }
            } else {
                $html .= '<label for="category-' . $category->getId() . '">';
                $html .= '<span>' . $category->getName() . '</span></label>';
            }
            $currentlevel = $category->getLevel();
            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    public function getFormData()
    {
        return $this->sessionForm->getData();
    }
}
