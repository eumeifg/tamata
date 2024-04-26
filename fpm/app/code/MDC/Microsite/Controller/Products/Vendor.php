<?php
namespace MDC\Microsite\Controller\Products;

class Vendor extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		 $vId = $this->getRequest()->getParam('vid', false);		 
		 
		return $this->_pageFactory->create();
	}
}