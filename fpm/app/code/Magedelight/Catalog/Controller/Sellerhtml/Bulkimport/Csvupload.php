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
namespace Magedelight\Catalog\Controller\Sellerhtml\Bulkimport;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Csvupload extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Catalog\Model\Csv\Upload
     */
    protected $_csvUpload;

    /**
     * @var \Magedelight\Catalog\Model\Gallery\Upload
     */
    protected $_galleryUpload;

    /**
     * @var \Magedelight\Catalog\Model\Offers\Upload
     */
    protected $_offersUpload;

    /**
     * @param Context $context
     * @param \Magedelight\Catalog\Model\Csv\Upload $csvUpload
     * @param \Magedelight\Catalog\Model\Gallery\Upload $galleryUpload
     * @param \Magedelight\Catalog\Model\Offers\Upload $offersUpload
     */
    public function __construct(
        Context $context,
        \Magedelight\Catalog\Model\Csv\Upload $csvUpload,
        \Magedelight\Catalog\Model\Gallery\Upload $galleryUpload,
        \Magedelight\Catalog\Model\Offers\Upload $offersUpload
    ) {
        $this->_galleryUpload = $galleryUpload;
        $this->_csvUpload = $csvUpload;
        $this->_offersUpload = $offersUpload;
        parent::__construct($context);
    }

    /**
     * Render results
     *
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $responseData = '';
        if ($this->getRequest()->getParam('type') == 'gallery') {
            $responseData = $this->_galleryUpload->upload($this->_auth->getUser()->getVendorId());
        } elseif ($this->getRequest()->getParam('type') == 'catalog_upload') {
            $responseData = $this->_csvUpload->upload(
                $this->_auth->getUser()->getVendorId(),
                $this->getRequest()->getParam('categoryId')
            );
        } elseif ($this->getRequest()->getParam('type') == 'update_offer') {
            $responseData = $this->_offersUpload->upload($this->_auth->getUser()->getVendorId());
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
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
