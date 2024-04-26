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
namespace Magedelight\Catalog\Controller\Sellerhtml\Product;

use Magedelight\Backend\App\Action\Context;
use Magedelight\Catalog\Block\Sellerhtml\Sellexisting\Result;
use Magedelight\Catalog\Model\ProductFactory;
use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magedelight\Catalog\Model\ProductRequestFactory;
use Magedelight\Catalog\Model\ProductRequestManagement as ProductRequestManagement;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\UrlFactory;

class CreatePost extends \Magedelight\Backend\App\Action
{
    const PRODUCT_RESOURCE = 'Magedelight_Catalog::manage_products';

    /**
     * @var \Magento\Framework\Json\Decoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magedelight\Catalog\Model\ProductEmailManagement
     */
    protected $productEmailManagement;

    /**
     * @var \Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    /**
     * @var VendorProductRequest
     */
    protected $productRequest;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var array variable
     */
    protected $postData;

    /**
     * @var \Magento\Framework\Controller\ResultRedirectFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    protected $sellProducts;
    protected $vendorProductFactory;

    /**
     * @var ProductRequestManagement
     */
    protected $productRequestManagement;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Framework\Json\Decoder $jsonDecoder
     * @param ProductRequestFactory $productRequestFactory
     * @param ProductRequestManagement $productRequestManagement
     * @param \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param UrlFactory $urlFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param JsonHelper $jsonHelper
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ProductFactory $vendorProductFactory
     * @param Result $sellProducts
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        ProductRequestFactory $productRequestFactory,
        ProductRequestManagement $productRequestManagement,
        \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement,
        \Magento\Framework\Registry $coreRegistry,
        UrlFactory $urlFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\Math\Random $mathRandom,
        JsonHelper $jsonHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        ProductFactory $vendorProductFactory,
        Result $sellProducts,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->urlModel = $urlFactory->create();
        $this->productRequest = $productRequestFactory->create();
        $this->productRequestManagement = $productRequestManagement;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->productEmailManagement = $productEmailManagement;
        $this->coreRegistry = $coreRegistry;
        $this->_productRepository = $productRepositoryInterface;
        $this->_mathRandom = $mathRandom;
        $this->jsonHelper = $jsonHelper;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->sellProducts = $sellProducts;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\Controller\ResultRedirectFactory
     */
    public function execute()
    {
        $this->resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost()) {
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            $this->resultRedirect->setUrl($this->_redirect->error($url));
            return $this->resultRedirect;
        }
        $this->postData = $this->getRequest()->getPostValue();
        $store = $this->getRequest()->getParam('store', false);
        $this->postData['store'] = $store;
        $productRequsetId = $this->postData['offer']['product_request_id'];
        $isNew = true;
        if ($productRequsetId) {
            $isNew = false;
        }
        $url = $this->urlModel->getUrl(
            'rbcatalog/listing/index',
            ['tab' => '1,0', 'sfrm' => 'nl', 'vpro' => 'pending', '_secure' => true]
        );
        $this->resultRedirect->setUrl($this->_redirect->error($url));
        $vendorId = $this->_auth->getUser()->getVendorId();
        $is_offered = (array_key_exists('is_offered', $this->postData)) ? $this->postData['is_offered'] : 0;
        try {
            $this->productRequestManagement->createProductRequest($vendorId, $this->postData, $isNew, $is_offered);
            $this->messageManager->addSuccessMessage('Sucessfully added');
            $this->resultRedirect->setUrl($this->_redirect->success($url));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultRedirect;
    }

    /**
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::PRODUCT_RESOURCE);
    }
}
