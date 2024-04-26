<?php

namespace CAT\VIP\Controller\Adminhtml\Offer;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magedelight\OffersImportExport\Controller\Adminhtml\Offers
{

    /**
     * @var \Magedelight\OffersImportExport\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\OffersImportExport\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\OffersImportExport\Helper\Data $helper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->helper = $helper;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $file = "sample_vipoffers_template_" . date('Ymd_His') . ".csv";
        $csvfeilds = [
            "product_id"=>"product_id",
            "vendor_id"=>"vendor_id",
            "ind_qty"=>"ind_qty",
            "global_qty"=>"global_qty",
            "type"=>"type",
            "discount"=>"discount",
            "customer_group"=>"customer_group",
        ];
        $samplefeilds = [
            "product_id"=>"413299",
            "vendor_id"=>"684",
            "ind_qty"=>"1",
            "global_qty"=>"10",
            "type"=>"Fixed",
            "discount"=>"5000",
            "customer_group"=>"1,23",
        ];
        $headers = new \Magento\Framework\DataObject($csvfeilds);
        $template = '"{{product_id}}","{{vendor_id}}","{{ind_qty}}","{{global_qty}}"' .
            ',"{{type}}","{{discount}}","{{customer_group}}"';
        $content = $headers->toString($template);
        $content .= "\n";
        $sampleRow = new \Magento\Framework\DataObject($samplefeilds);
        $content .= $sampleRow->toString($template);
        return $this->fileFactory->create($file, $content, DirectoryList::VAR_DIR);
    }
}
