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
namespace Magedelight\Catalog\Controller\Sellerhtml\Bulkimport;

class Bulkoffercsvtemplate extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Catalog\Helper\Bulkimport\Offers
     */
    protected $offersHelper;
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
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Catalog\Helper\Bulkimport\Offers $offersHelper,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->offersHelper = $offersHelper;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $vendor_id = $this->_auth->getUser()->getVendorId();

        if ($vendor_id) {
            $headers = $this->offersHelper->getCSVHeaders();
            $heading_names = implode(',', $headers);
            $handle = $handle2 = $this->file->fileOpen("php://output", "w");

            $headings = explode(',', $heading_names);
            fputcsv($handle, $headings);
            $row = $this->offersHelper->getSampleRow();

            fputcsv($handle, $row);
            $this->file->fileClose($handle);
            $this->downloadCsv();
        }
    }

    /**
     *
     */
    public function downloadCsv()
    {
        $file = "bulkoffertemplate_" . date('Ymd_His') . ".csv";
        //set appropriate headers
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $file);
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header('Expires: 0');
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
