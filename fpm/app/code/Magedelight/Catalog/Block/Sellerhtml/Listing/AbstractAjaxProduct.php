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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing;

class AbstractAjaxProduct extends \Magedelight\Backend\Block\Template
{

    /**
     * @var \Magedelight\Catalog\Model\ProductRequestFactory
     */
    protected $_vendorProductRequestFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magedelight\Catalog\Model\ProductFactory
     */
    protected $vendorProductFactory;

    /**
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    protected $_storeManager;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProduct
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $vendorProductRequest
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Template\Context $context,
        \Magedelight\Catalog\Model\ProductFactory $vendorProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Catalog\Model\ProductRequestFactory $vendorProductRequest,
        array $data = []
    ) {
        $this->_categoryRepository = $categoryRepository;
        $this->productFactory= $productFactory;
        $this->vendorProductFactory = $vendorProduct;
        $this->authSession = $authSession;
        $this->_storeManager = $context->getStoreManager();
        $this->_vendorProductRequestFactory = $vendorProductRequest;
        parent::__construct($context, $data);
    }

    public function getVendorId()
    {
        return $this->authSession->getUser()->getVendorId();
    }

    public function getDateFromate($val)
    {
        $value = date('m/j/Y', strtotime($val));
        return $value;
    }

    public function getProduct($prodId)
    {
        $product = $this->productFactory->create()->load($prodId);
        return $product;
    }

    public function isView()
    {
        return $this->getRequest()->getParam('view');
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function getCategoryPath($id, $storeId = 0)
    {
        $string = '';
        $cnt = 0;
        $pathIds = $this->_categoryRepository->get($id, $storeId)->getPath();
        $path = explode('/', $pathIds);

        foreach ($path as $pathId) {
            if ($this->_storeManager->getStore()->getRootCategoryId() == $pathId) {
                continue;
            }
            $cnt++;
            $sufix = ((count($path) - 1) == $cnt) ? '' : ' > ';
            $string.= $this->_categoryRepository->get($pathId)->getName() . $sufix;
        }
        return $string;
    }
}
