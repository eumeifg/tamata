<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Controller\Tracking;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Rma\Helper\Data as Helper;

class PackagePrint extends \Magento\Rma\Controller\Tracking\PackagePrint
{

    /**
     * Http response file factory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileResponseFactory,
        Helper $helper
    ) {
        $this->_fileFactory = $fileResponseFactory;
        parent::__construct($context, $coreRegistry, $fileResponseFactory, $helper);
    }

    /**
     * Create pdf document with information about packages
     *
     * @return void
     */
    public function execute()
    {
        /** @var $rmaHelper \Magento\Framework\Stdlib\DateTime\DateTime */
        $rmaId = $this->getRequest()->getParam('entity_id');
        if ($rmaId) {
            /** @var $rmaModel \Magento\Rma\Model\Rma */
            $rmaModel = $this->_objectManager->create(\Magento\Rma\Model\Rma::class)->load($rmaId);
            if ($rmaModel) {
                /** @var $dateModel \Magento\Framework\Stdlib\DateTime\DateTime */
                $dateModel = $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class);
                /** @var $pdfModel \Magento\Rma\Model\Pdf\Rma */
                $pdfModel = $this->_objectManager->create(\Magedelight\Rma\Model\Pdf\Rma::class);
                $pdf = $pdfModel->getPackagePdf([$rmaModel]);
                return $this->_fileFactory->create(
                    'package_slip_' . $dateModel->date('Y-m-d_H-i-s') . '.pdf',
                    $pdf->render(),
                    DirectoryList::MEDIA,
                    'application/pdf'
                );
            }
        }
    }
}
