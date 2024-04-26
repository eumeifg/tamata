<?php

namespace MDC\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;
use Magento\SalesRule\Model\Rule;

/**
 * 
 */
class NewActionHtml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\NewActionHtml
{
	
	 /**
     * New action html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()
       		 ->getParam('id');
	    $formName = $this->getRequest()
	        ->getParam('form_namespace');

	    $jsFormObject = $this->getRequest()
	        ->getParam('form');

	    $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
	    $type = $typeArr[0];

	    $model = $this->_objectManager->create(
	        $type
	    )->setId(
	        $id
	    )->setType(
	        $type
	    )->setRule(
	        $this->_objectManager->create(Rule::class)
	    )->setPrefix(
	        'actions'
	    );
	    if (!empty($typeArr[1])) {
	        $model->setAttribute($typeArr[1]);
	    }

	    if ($model instanceof AbstractCondition) {
	        $model->setJsFormObject($jsFormObject);
	        $model->setFormName($formName);
	        $html = $model->asHtmlRecursive();
	    } else {
	        $html = '';
	    }
	    $this->getResponse()
	        ->setBody($html);
    }
}