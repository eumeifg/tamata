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

class ExportLiveProductsXml extends \Magedelight\Catalog\Controller\Sellerhtml\Listing\Exports
{
    protected $csvProcessor;

    protected $directoryList;

    protected $convertArray;

    protected $file;

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
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Convert\ConvertArray $convertArray,
        \Magento\Framework\Filesystem\Io\File $file

    ) {
        $this->fileFactory = $fileFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->vendorProductFactory = $vendorProductFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->convertArray = $convertArray;
        $this->file = $file;
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
        $fileName = 'live_products.xml';
        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
       
        $productdata = [];
              
        foreach ($collection->getData() as $key => $value) {
        	$productdata[] = array(
            		"id" => $value['entity_id'],
            		"vendor_sku" => $value['vendor_sku'],
            		"product_name" => $value['product_name'],
            		"price" => $value['price'],
            		"special_price" => $value['special_price'],
            		"special_from_date" => $value['special_from_date'],
            		"special_to_date" => $value['special_to_date'],
            		"qty" => $value['qty'],

            );
        }
       
         $this->createXMLfile($productdata);

         return $this->fileFactory->create(
            $fileName,
            [
                'type'  => "filename",
                'value' => $fileName,
                'rm'    => true,
            ],
            DirectoryList::MEDIA,
            'text/xml',
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

    public function createXMLfile($productsArray){

   
     $fileName = 'live_products.xml';
        $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
             
               $dom     = new \DOMDocument('1.0', 'utf-8'); 
               $root      = $dom->createElement('porducts'); 
               for($i=0; $i<count($productsArray); $i++){
                 
                 $productId  =  $productsArray[$i]['id'];  
                 $name = htmlspecialchars($productsArray[$i]['product_name']);
                 $vendorSku = htmlspecialchars($productsArray[$i]['vendor_sku']);
                 $price = htmlspecialchars($productsArray[$i]['price']);
                 $specialPrice = htmlspecialchars($productsArray[$i]['special_price']);
                 $spPriceFrom = htmlspecialchars($productsArray[$i]['special_from_date']);
                 $spPriceTo = htmlspecialchars($productsArray[$i]['special_to_date']);
                 $qty = htmlspecialchars($productsArray[$i]['qty']);
                    
                 $vendorProduct = $dom->createElement('product');
                 $vendorProduct->setAttribute('id', $productId);
                 
                 $vendor_sku = $dom->createElement('vendor_sku', $vendorSku); 
                 $vendorProduct->appendChild($vendor_sku);

                 $name = $dom->createElement('product_name', $name); 
                 $vendorProduct->appendChild($name);

                 $price = $dom->createElement('price', $price); 
                 $vendorProduct->appendChild($price);

                $specialPrice = $dom->createElement('special_price', $specialPrice); 
                 $vendorProduct->appendChild($specialPrice);

                 $spPriceFrom = $dom->createElement('special_price_from', $spPriceFrom); 
                 $vendorProduct->appendChild($spPriceFrom);

                  $spPriceTo = $dom->createElement('special_price_to', $spPriceTo); 
                 $vendorProduct->appendChild($spPriceTo);

                $qty = $dom->createElement('qty', $qty); 
                 $vendorProduct->appendChild($qty);
             
                 $root->appendChild($vendorProduct);
               }
               $dom->appendChild($root); 
               $dom->save($filePath);               
        } 
}
