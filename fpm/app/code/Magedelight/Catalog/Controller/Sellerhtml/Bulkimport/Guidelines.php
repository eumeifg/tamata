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

class Guidelines extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\DompdfFactory
     */
    protected $dompdfFactory;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\DompdfFactory $dompdfFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\DompdfFactory $dompdfFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->dompdfFactory = $dompdfFactory;
        parent::__construct($context);
    }

    /**
     * Vendor product landing page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Guidelines - Bulkimport'));
        $resultPage = $resultPage->getLayout()
            ->createBlock(\Magento\Framework\View\Element\Template::class)
            ->setTemplate('Magedelight_Catalog::bulkimport/guidelines.phtml')
            ->toHtml();
        $response = $this->dompdfFactory->create();
        $response->setData($resultPage);

        return $response;
    }
}
