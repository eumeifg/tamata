<?php

namespace MDC\Catalog\Controller\Sellerhtml\Listing;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportNonLiveProducts
 * @package MDC\Catalog\Controller\Sellerhtml\Listing
 */
class ExportNonLiveProducts extends \Magedelight\Catalog\Controller\Sellerhtml\Listing\ExportNonLiveProducts
{
    /**
     * ExportNonLiveProducts constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param DirectoryList $directoryList
     */
    public function __construct(\Magedelight\Backend\App\Action\Context $context, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository, \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Framework\File\Csv $csvProcessor, DirectoryList $directoryList)
    {
        parent::__construct($context, $fileFactory, $attributeRepository, $vendorProductFactory, $resultLayoutFactory, $csvProcessor, $directoryList);
    }

    public function execute()
    {
        /** start csv content and set template */
        $content[] =
            [
                'vendor_sku' => __('Vendor Sku'),
                'name' => __('Product Name'),
                'price' => __('Price'),
                'special_price' => __('Special Price'),
                'special_from_date' => __('Special From Date'),
                'special_to_date' => __('Special To Date'),
                'qty' => __('Quantity'),
                'barcode' => __('Barcode')
            ];
        $resultLayout = $this->resultLayoutFactory->create();
        if ($this->getRequest()->getParam('vpro') == 'pending') {
            $block = $resultLayout->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Pending::class
            );
            $collection = $block->getPendingProductRequests();
        } elseif ($this->getRequest()->getParam('vpro') == 'disapproved') {
            $block = $resultLayout->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Disapproved::class
            );
            $collection = $block->getDisapprovedProductRequests();
        } else {
            $block = $resultLayout->getLayout()->createBlock(
                \Magedelight\Catalog\Block\Sellerhtml\Listing\Nonlive\Approved::class
            );
            $collection = $block->getApprovedProducts();
        }
        $fileName = 'non_live_products.csv';
        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;

        while ($product = $collection->fetchItem()) {
            if ($this->getRequest()->getParam('vpro') == 'pending') {
                $attributes = !empty($product->getAttributes()) && !is_null(json_decode($product->getAttributes())) ? json_decode($product->getAttributes()) : null;
                $barcode = isset($attributes->bar_code) && !empty($attributes->bar_code) ? $attributes->bar_code : null;
            } elseif ($this->getRequest()->getParam('vpro') == 'disapproved') {
                $attributes = !empty($product->getAttributes()) && !is_null(json_decode($product->getAttributes())) ? json_decode($product->getAttributes()) : null;
                $barcode = isset($attributes->bar_code) && !empty($attributes->bar_code) ? $attributes->bar_code : null;
            } elseif ($this->getRequest()->getParam('vpro') == 'approve') {
                $barcode = $product->getBarcode();
            } else {
                $barcode = $product->getBarcode();
            }
            if ($this->getRequest()->getParam('vpro', 'approve') == 'approve') {
                $content[] = [
                    $product->getVendorSku(), $product->getProductName(),
                    $product->getData('price'), $product->getData('special_price'),
                    $product->getData('special_from_date'), $product->getData('special_to_date'), $product->getQty(), $barcode
                ];
            } else {
                $content[] = [
                    $product->getVendorSku(), $product->getName(),
                    $product->getPrice(), $product->getSpecialPrice(),
                    $product->getSpecialFromDate(), $product->getSpecialToDate(), $product->getQty(), $barcode
                ];
            }
        }
        $this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);

        return $this->fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true,
            ],
            DirectoryList::MEDIA,
            'text/csv',
            null
        );
    }
}
