<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Sellerhtml\Order\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Sales\Model\OrderFactory
     */
    protected $vendorOrderFactory;
    /**
     * @var \Magento\MediaStorage\Model\File\Uploader
     */
    private $uploader;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\MediaStorage\Model\File\Uploader $uploader
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->storeManager = $storeManager;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            if ($vendorOrderId = $this->getRequest()->getParam('id', false)) {
                $uploader = $this->_objectManager->create(
                    \Magento\MediaStorage\Model\File\Uploader::class,
                    ['fileId' => 'custom_invoice']
                );
                $uploader->setAllowedExtensions(['pdf']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('invoice'));

                $this->_eventManager->dispatch(
                    'invoice_upload_file_after',
                    ['result' => $result, 'action' => $this]
                );

                unset($result['tmp_name']);
                unset($result['path']);

                $result['url'] = $this->getTmpMediaUrl($result['file']);

                if (!empty($result['file'])) {
                    if ($vendorOrderId) {
                        $vendorOrder =  $this->vendorOrderFactory->create()->load($vendorOrderId);
                        if (!$vendorOrder->getVendorOrderId()) {
                            throw new Exception('No Such Order Found.');
                        }
                        $vendorOrder->setData('custom_invoice', $result['file']);
                    } else {
                        throw new Exception('No data found.');
                    }

                    try {
                        $vendorOrder->save();
                    } catch (\Magento\Framework\Model\Exception $e) {
                        $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                    } catch (\RuntimeException $e) {
                        $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                    }
                }
            } else {
                $result = ['error' => __('Failed to upload invoice.')];
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . '/invoice';
    }

    /**
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
