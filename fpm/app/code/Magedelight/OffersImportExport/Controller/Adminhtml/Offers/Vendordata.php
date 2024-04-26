<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Controller\Adminhtml\Offers;

use Magento\Framework\App\Filesystem\DirectoryList;

class Vendordata extends \Magedelight\OffersImportExport\Controller\Adminhtml\Offers
{

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\OffersImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\OffersImportExport\Helper\Data
     */
    protected $_vendorProductFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

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
        $this->helper = $helper;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
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
            'rbvpw.price'
        ];
        $vendorCollection->getSelect()->join(
            ['rbvpw' => 'md_vendor_product_website'],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            $fields
        );
        $vendorCollection->addFieldToSelect(['vendor_id','vendor_sku','qty','marketplace_product_id']);
        /** start csv content and set template */
        $headers = new \Magento\Framework\DataObject(
            $this->helper->getCSVFields()
        );
        $template = $this->helper->getTemplate();
        $content = $headers->toString($template);
        $content .= "\n";
        foreach ($vendorCollection as $vendorCollectionEle) {
            $innerArray = [];
            $vendorCollectionEle->setData('marketplace_sku', $vendorCollectionEle->getData('sku'));
            $content .= $vendorCollectionEle->toString($template) . "\n";
        }
        return $this->fileFactory->create($file, $content, DirectoryList::VAR_DIR);
    }
}
