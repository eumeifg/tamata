<?php


namespace CAT\VIP\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;

use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;

/**

* Main page controller

*/

class Index extends Action {

	/**

	* @var PageFactory

	*/

	protected $resultPageFactory;

	/**

	* @param Context     $context

	* @param PageFactory $resultPageFactory

	*/

	public function __construct(Context $context,PageFactory $resultPageFactory) {

		parent::__construct($context);

		$this->resultPageFactory = $resultPageFactory;

	}

	/**

	* @return \Magento\Framework\View\Result\PageFactory

	*/

	public function execute() {

		$resultPage = $this->resultPageFactory->create();

		$resultPage->setActiveMenu('CAT_VIP::grid');

		$resultPage->addBreadcrumb(__('VIP Orders'), __('VIP Orders'));

		$resultPage->getConfig()->getTitle()->prepend(__('VIP Orders'));

		return $resultPage;

	}
}