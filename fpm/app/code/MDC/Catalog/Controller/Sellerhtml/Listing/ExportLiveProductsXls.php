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
namespace MDC\Catalog\Controller\Sellerhtml\Listing;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportLiveProductsXls extends \Magedelight\Catalog\Controller\Sellerhtml\Listing\Exports
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceFormat;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @param \Magedelight\Backend\App\Action\Context          $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface    $attributeRepository
     * @param \Magedelight\Catalog\Model\ProductFactory        $vendorProductFactory
     * @param \Magento\Framework\View\Result\LayoutFactory     $resultLayoutFactory
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \Magento\Framework\Pricing\Helper\Data           $priceFormat
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceFormat,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->fileFactory = $fileFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->storeManager = $storeManager;
        $this->priceFormat = $priceFormat;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        parent::__construct($context, $fileFactory, $attributeRepository, $vendorProductFactory, $resultLayoutFactory);
    }

    /**
     * Export action for Mylisting live products.
     *
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {

        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->createBlock(\Magedelight\Catalog\Block\Sellerhtml\Listing\Live::class);
        $collection = $block->getLiveProductsCollection();
        $storeId = $this->storeManager->getStore()->getId();

        $filename = 'live_products.xlsx';
        header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header("Content-Encoding: UTF-8");
        header('Pragma: public');
        $header = [];
        if ($storeId == 2) {
            $header =
                [
                'بائع سكو' => 'string',
                'اسم المنتج' => 'string',
                'السعر' => '@',
                'سعر خاص' => '@',
                'خاص من التسجيل' => 'YYYY-MM-DD HH:MM:SS',
                'خاص إلى تاريخ' => 'YYYY-MM-DD HH:MM:SS',
                'كمية' => '0.00',
                'الرمز الشريطي' => 'string',
            ];
        } else {
            $header =
                [
                'Vendor Sku' => 'string',
                'Product Name' => 'string',
                'Price' => '@',
                'Special Price' => '@',
                'Special From Date' => 'YYYY-MM-DD HH:MM:SS',
                'Special To Date' => 'YYYY-MM-DD HH:MM:SS',
                'Quantity' => '0.00',
                'Barcode' => 'string',
            ];
        }
        $priceHelper = $this->priceFormat;
        while ($product = $collection->fetchItem()) {
            $data[] = [
                $product->getVendorSku(),
                $product->getProductName(),
                $priceHelper->currency($product->getData('price'), true, false),
                $priceHelper->currency($product->getData('special_price'), true, false),
                $product->getData('special_from_date'),
                $product->getData('special_to_date'),
                $product->getQty(),
                $product->getBarcode()
            ];
        }
         
        $writer = new \XLSXWriter();
        $writer->setAuthor('Tamata');
        $writer->writeSheetHeader('Sheet1', $header);
        foreach ($data as $row) {
            $writer->writeSheetRow('Sheet1', $row);
        }

        $writer->writeToStdOut();
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
