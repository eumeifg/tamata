<?php

namespace Ktpl\CategoryView\Controller\Adminhtml\Index;

class Block extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ktpl_CategoryView::ajax_category_block';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory $blockFactory
     */
    protected $_blockFactory;
    

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_blockFactory = $blockFactory;
        
        parent::__construct($context);
    }

    /**
     * block action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultData = [['value' => '0', 'label' => __('[ SELECT BLOCK ]')]];
        if($this->getRequest()->isAjax())
        {
            $type = $this->getRequest()->getParam('type');
            if($type != \Ktpl\CategoryView\Block\Adminhtml\Options::TYPE_NONE)
            {
                switch($type)
                {
                    case \Ktpl\CategoryView\Block\Adminhtml\Options::TYPE_BLOCK: 
                        $blockObject = $this->_blockFactory->create();
                        foreach($blockObject->getCollection() as $block)
                            $resultData[] = ['value' => $block->getId(), 'label' => $block->getTitle()];
                        break;
                }
            }
        }
        return $result->setData($resultData);
    }
}
