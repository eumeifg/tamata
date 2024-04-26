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
use Magedelight\Catalog\Model\ProductRequest as VendorProductRequest;
use Magedelight\Catalog\Model\ProductRequestFactory;
use Magedelight\Catalog\Model\ProductRequestManagement;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlFactory;

class EditPost extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $directoryList;

    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

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

    /**
     * @var ProductRequestManagement
     */
    protected $productRequestManagement;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param UrlFactory $urlFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRequestManagement $productRequestManagement
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        ProductRequestFactory $productRequestFactory,
        \Magedelight\Catalog\Model\ProductEmailManagement $productEmailManagement,
        \Magento\Framework\Registry $coreRegistry,
        UrlFactory $urlFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRequestManagement $productRequestManagement
    ) {
        parent::__construct($context);
        $this->urlModel = $urlFactory->create();
        $this->productRequest = $productRequestFactory->create();
        $this->jsonEncoder = $jsonEncoder;
        $this->productEmailManagement = $productEmailManagement;
        $this->coreRegistry = $coreRegistry;
        $this->_productRepository = $productRepositoryInterface;
        $this->_mathRandom = $mathRandom;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->directoryList = $directoryList;
        $this->_storeManager = $storeManager;
        $this->productRequestManagement = $productRequestManagement;
    }

    public function execute()
    {
        $this->resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost()) {
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            $this->resultRedirect->setUrl($this->_redirect->error($url));
            return $this->resultRedirect;
        }
        $this->postData = $this->getRequest()->getPostValue();
        $p = $this->getRequest()->getParam('p', 1);
        $limit = $this->getRequest()->getParam('limit', 10);
        $errors = [];
        $store = $this->getRequest()->getParam('store', false);
        $this->postData['store'] = $store;
        $pid = $this->postData['offer']['marketplace_product_id'];
        $hasVariants = $this->getRequest()->getParam('has_variants', false);
        $this->postData['vendor_product_id'] = $this->postData['offer']['vendor_product_id'];
        $vendorId = $this->_auth->getUser()->getVendorId();
        $productRequsetId = $this->postData['offer']['product_request_id'];
        $isNew = false;
        if (!$productRequsetId) {
            $isNew = true;
        }
        try {
            $this->productRequestManagement->createProductRequest($vendorId, $this->postData, $isNew);
            $this->messageManager->addSuccess(__('Your product changes are added successfully for approval.'));
            $url = $this->urlModel->getUrl(
                'rbcatalog/listing/index',
                ['tab' => '1,0', 'sfrm' => 'nl', 'vpro' => 'pending', 'p' => $p, 'limit' => $limit, '_secure' => true]
            );
            $this->resultRedirect->setUrl($this->_redirect->success($url));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Zend_Db_Statement_Exception $e) {
            if ($e->getCode() == self::ERROR_CODE_DUPLICATE_ENTRY) {
                $requestId = $this->getDuplicateRequestId($vendorId, $this->postData['offer']['vendor_sku']);
                $p = $this->getRequest()->getParam('p', 1);
                $editLink = $this->urlModel->getUrl(
                    'rbcatalog/product/edit',
                    ['id' => $requestId, 'tab' => '1,0', 'p' => $p]
                );
                $this->messageManager->addError(
                    __('An edit request for this product is already exist. <a href="%1">
                    Click here to edit existing request.</a>', $editLink)
                );
            } else {
                throw $e;
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while submitting approved product change request.')
            );
        }

        //return $this->resultRedirect;
        return $this->_redirect('rbcatalog/listing', ['_current' => true]);
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products_approved_view_edit');
    }
    
}
