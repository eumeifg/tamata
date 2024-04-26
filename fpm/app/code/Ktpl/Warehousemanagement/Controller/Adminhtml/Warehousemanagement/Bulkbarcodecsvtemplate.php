<?php

namespace Ktpl\Warehousemanagement\Controller\Adminhtml\Warehousemanagement;

use Magento\Backend\App\Action;

class Bulkbarcodecsvtemplate extends Action
{

    /**
     * @var \Ktpl\Warehousemanagement\Helper\Data
     */
    protected $warehouseHelper;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * Bulkoffercsvtemplate constructor.
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Catalog\Helper\Bulkimport\Offers $offersHelper
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        Action\Context  $context,
        \Ktpl\Warehousemanagement\Helper\Data $warehouseHelper,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->warehouseHelper = $warehouseHelper;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
            $headers = $this->warehouseHelper->getCSVHeaders();
            $heading_names = implode(',', $headers);
            $handle = $handle2 = $this->file->fileOpen("php://output", "w");

            $headings = explode(',', $heading_names);
            fputcsv($handle, $headings);
            $row = $this->warehouseHelper->getSampleRow();

            fputcsv($handle, $row);
            $this->file->fileClose($handle);
            $this->downloadCsv();
    }

    /**
     *
     */
    public function downloadCsv()
    {
        $file = "bulkbarcodetemplate_" . date('Ymd_His') . ".csv";
        //set appropriate headers
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $file);
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header('Expires: 0');
    }

}
