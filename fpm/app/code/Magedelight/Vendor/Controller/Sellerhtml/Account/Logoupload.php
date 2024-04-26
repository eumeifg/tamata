<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

class Logoupload extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magedelight\Vendor\Model\Vendor\Image
     */
    protected $imageModel;

    /**
     * @var \Magedelight\Vendor\Model\Upload
     */
    protected $uploadModel;

    /**
     * @var \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface
     */
    protected $vendorWebsiteRepository;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository
     * @param \Magedelight\Vendor\Model\Vendor\Image $imageModel
     * @param \Magedelight\Vendor\Model\Upload $uploadModel
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Vendor\Api\VendorWebsiteRepositoryInterface $vendorWebsiteRepository,
        \Magedelight\Vendor\Model\Vendor\Image $imageModel,
        \Magedelight\Vendor\Model\Upload $uploadModel
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->storeManager = $storeManager;
        $this->imageModel = $imageModel;
        $this->uploadModel = $uploadModel;
        $this->vendorWebsiteRepository = $vendorWebsiteRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $result = [];
        $_filesParam = $this->getRequest()->getFiles()->toArray();
        if ($_filesParam) {
            try {
                if ($logo = $this->uploadModel->uploadFileAndGetName(
                    'businesslogo',
                    $this->imageModel->getBaseDir('vendor/logo'),
                    []
                )) {
                    $vendorWebsiteModel = $this->vendorWebsiteRepository->getVendorWebsiteData(
                        $this->_auth->getUser()->getVendorId()
                    );
                    $vendorWebsiteModel->setLogo($logo)->save();
                }
                $result['url'] = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'vendor/logo/' . $logo;
                $result['file'] = $logo;
                $result['file_upload_error'] = false;
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode(),'file_upload_error'=> true];
            }

            /** @var \Magento\Framework\Controller\Result\Raw $response */
            $response->setContents(json_encode($result));
            return $response;
        }
        $response->setContents(json_encode('failed'));
        return $response;
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
