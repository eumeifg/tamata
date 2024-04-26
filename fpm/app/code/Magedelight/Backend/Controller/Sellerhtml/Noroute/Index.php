<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Controller\Sellerhtml\Noroute;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Index extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Noroute action
     *
     * @return \Magedelight\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magedelight\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setStatusHeader(404, '1.1', 'Not Found');
        $resultPage->setHeader('Status', '404 File not found');
        $resultPage->addHandle('sellerhtml_noroute');
        return $resultPage;
    }

    /**
     * Error page should be public accessible. Do not check keys to avoid redirect loop
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        return true;
    }
}
