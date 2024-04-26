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
namespace Magedelight\Catalog\Block\Category;

class View extends \Magento\Catalog\Block\Category\View
{

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magedelight\Catalog\Helper\Data $helper,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_helper = $helper;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }

    /**
     * Alter layouts based on category.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_helper->isEnabled()) {
            $category = $this->getCurrentCategory();
            if ($category) {
                if ((int)$category->getLevel() === 2) {
                    $this->pageConfig->setPageLayout('1column');
                    $this->getCurrentCategory()->setDisplayMode(\Magento\Catalog\Model\Category::DM_PAGE);
                }
            }
        }
        return $this;
    }

    /**
     *
     * @return type
     */
    public function getCategoryList()
    {
        $_category  = $this->getCurrentCategory();
        $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect('*')
              ->addAttributeToFilter('is_active', 1)
              ->setOrder('position', 'ASC')
              ->addIdFilter($_category->getChildren());
        return $collection;
    }

    /**
     *
     * @param string $imageName
     * @return string|NULL
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryThumbImage($imageName)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        return  $mediaDirectory . 'catalog/category/' . $imageName;
    }

    /**
     * @return string
     */
    public function getPlaceholderImage()
    {
        return $this->getViewFileUrl('Magedelight_Catalog::images/category/placeholder/thumbnail.png');
    }
}
