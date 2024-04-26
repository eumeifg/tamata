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
namespace Magedelight\Catalog\Block\Sellerhtml;

use Magedelight\Catalog\Api\SubCategoryInterface;

class Category extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var SubCategoryInterface
     */
    protected $subCategoryInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param SubCategoryInterface $subCategoryInterface
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        SubCategoryInterface $subCategoryInterface,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->subCategoryInterface = $subCategoryInterface;
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     *
     * @return \Magento\User\Model\User|null
     */
    public function getVendor()
    {
        return $this->authSession->getUser();
    }

    /**
     * Retrieve Categories List
     * @return string
     */
    public function getCategories()
    {
        return $this->subCategoryInterface->getCategories();
    }

    public function getSubcategoriesUrl()
    {
        return $this->getUrl('rbcatalog/product/subcategories');
    }
}
