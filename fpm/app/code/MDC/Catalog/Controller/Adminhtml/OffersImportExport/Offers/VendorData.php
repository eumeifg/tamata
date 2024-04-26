<?php

namespace MDC\Catalog\Controller\Adminhtml\OffersImportExport\Offers;

use Magento\Framework\App\Filesystem\DirectoryList;

class VendorData extends \Magedelight\OffersImportExport\Controller\Adminhtml\Offers\Vendordata
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\OffersImportExport\Helper\Data $helper
     * @param \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\OffersImportExport\Helper\Data $helper,
        \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context, $vendorProductFactory, $helper, $collectionFactory, $fileFactory);
    }

    public function execute()
    {
        $file = "export_vendor_offer" . date('Ymd_His') . ".csv";
        $vendorIds = $this->getRequest()->getParam('vendorId');
        $vendorIdsArray = explode(',', $vendorIds);
        $vendorCollection = $this->collectionFactory->create();

        /* If in array received all opttions then not need to applay below conditions.*/
        if (!in_array("all", $vendorIdsArray)) {
            $vendorCollection->addFieldToFilter('main_table.vendor_id', ['in' => $vendorIdsArray]);
        }
        $vendorCollection->getSelect()->join(
            ['cpe' => 'catalog_product_entity'],
            "cpe.entity_id = main_table.marketplace_product_id",
            ['cpe.sku']
        );
        $fields = [
            'rbvpw.status',
            'rbvpw.reorder_level',
            'rbvpw.special_to_date',
            'rbvpw.special_from_date',
            'rbvpw.special_price',
            'rbvpw.price',
            'rbvpw.cost_price_iqd',
            'rbvpw.cost_price_usd'
        ];
        $vendorCollection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            $fields
        );
        $vendorCollection->addFieldToSelect(['vendor_id','vendor_sku','qty','marketplace_product_id']);
        $vendorCollection->getSelect()->joinLeft(
            ['cpev' => 'catalog_product_entity_varchar'],
            "cpe.row_id = cpev.row_id and cpev.attribute_id = 73 and cpev.store_id=0",
            ['product_name' =>'cpev.value']
        );
        /** start csv content and set template */
        $headers = new \Magento\Framework\DataObject(
            $this->helper->getCSVFields()
        );
        $template = $this->helper->getTemplate();
        $content = $headers->toString($template);
        $content .= "\n";
        foreach ($vendorCollection as $vendorCollectionEle) {
            /*$innerArray = [];*/
            $vendorCollectionEle->setData('marketplace_sku', $vendorCollectionEle->getData('sku'));
            $content .= $vendorCollectionEle->toString($template) . "\n";
        }
        return $this->fileFactory->create($file, $content, DirectoryList::VAR_DIR);
    }
}
