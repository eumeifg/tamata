<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Controller\Adminhtml\BlackList;
 
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;

class SampleCsv extends Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository $assetRepo
     */
    protected $_assetRepo;

    /**
     * @var Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        Action\Context $context
    ) {
        $this->_storeManager=$storeManager;
        $this->_assetRepo = $assetRepo;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }
    /**
     * Download action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $file = $this->_assetRepo->getUrl("Magedelight_Abandonedcart::csv/sample.csv");
        $resultRaw = $this->resultRawFactory->create();
        try {
            //@codingStandardsIgnoreStart
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($file);
            //@codingStandardsIgnoreEnd
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
    }
}
