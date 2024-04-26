<?php
 
namespace MDC\Catalog\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class ThresholdQty extends Action
{

    public function __construct(
        Context $context,
        \MDC\Catalog\Helper\OnlyXLeft $helper        
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('productId', false)) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return "";
        }
        $html = null;
    
        $thresoldStatus = $this->_helper->getProductXleftById($this->getRequest()->getParam('productId', false));
        
        if($thresoldStatus["status"]){
            $html .= '<div class="availability only" title="Only '.$thresoldStatus["qty"].' left">';
            $html .= /* @noEscape */ __('Only %1 left!', "<strong>".$thresoldStatus["qty"]."</strong>");
            $html .= '</div>';
        }
    
        $responseData['response'] =  $html;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    
    
     
}
