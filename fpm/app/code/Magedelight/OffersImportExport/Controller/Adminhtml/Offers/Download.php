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
        $file = "sample_offers_template_" . date('Ymd_His') . ".csv";
        $headers = new \Magento\Framework\DataObject(
            $this->helper->getCSVFields()
        );
        $template = $this->helper->getTemplate();
        $content = $headers->toString($template);
        $content .= "\n";
        $sampleRow = new \Magento\Framework\DataObject(
            $this->helper->getSampleData()
        );
        $content .= $sampleRow->toString($template);
        return $this->fileFactory->create($file, $content, DirectoryList::VAR_DIR);
    }
}
