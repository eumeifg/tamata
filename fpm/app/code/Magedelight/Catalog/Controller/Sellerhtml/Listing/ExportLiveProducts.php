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
namespace Magedelight\Catalog\Controller\Sellerhtml\Listing;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportLiveProducts extends \Magedelight\Catalog\Controller\Sellerhtml\Listing\Exports
{
    protected $csvProcessor;

    protected $directoryList;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->fileFactory = $fileFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
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
                ];
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->createBlock(\Magedelight\Catalog\Block\Sellerhtml\Listing\Live::class);
        $collection = $block->getLiveProductsCollection();
        $fileName = 'live_products.csv';
        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;

        while ($product = $collection->fetchItem()) {
            $content[] = [
                $product->getVendorSku(), $product->getProductName(),
                $product->getData('price'), $product->getData('special_price'),
                $product->getData('special_from_date'), $product->getData('special_to_date'), $product->getQty()
                ];
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
