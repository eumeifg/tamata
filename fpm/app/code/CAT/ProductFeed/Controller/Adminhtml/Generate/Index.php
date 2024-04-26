<?php

namespace CAT\ProductFeed\Controller\Adminhtml\Generate;

use CAT\ProductFeed\Model\FbFeedGenerate;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var FbFeedGenerate
     */
    public $fbFeedGenerate;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param FbFeedGenerate $fbFeedGenerate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        FbFeedGenerate $fbFeedGenerate
    ) {
        $this->fbFeedGenerate = $fbFeedGenerate;
        
        parent::__construct($context);
    }
    
    /**
     * Optimize action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        set_time_limit(18000);
        
        try {
            $logger = $this->fbFeedGenerate->getLogger();
            $startTime = microtime(true);
            $this->fbFeedGenerate->generate(0); //0: For default Store Feed Generation 
            $endTime = microtime(true);
            $timeTaken = ($endTime - $startTime);
            $logger->info('Feed Total Time from Admin: '. round($timeTaken/60, 2)."mins");
            
            $this->messageManager->addSuccess(
                __('Product feed generation completed successfully. Total time:'.round($timeTaken/60, 2)."mins")
            );
        } catch (\Exception $e) {
            $message = __('Product feed generation failed.');
            $this->messageManager->addError($message);
            $this->messageManager->addError($e->getMessage());
            $logger->info('Admin Error: '.$e->getMessage());
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            ['section' => 'cat_product_feed']
        );
    }
}
