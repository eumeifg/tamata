<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Abandonedcart\Controller\Adminhtml\Rule;

use Magedelight\Abandonedcart\Controller\Adminhtml\Rule;

class Edit extends Rule
{
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //echo "<pre>"; print_r($this->getRequest()->getPostValue()); die;
        $id = $this->getRequest()->getParam('id');
         /** @var \Magedelight\Abandonedcart\Model\Rule $model */
        $model = $this->ruleFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getAbandonedCartRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }
        $resultPage = $this->resultPageFactory->create();

        $resultPage->addBreadcrumb(
            $id ? __('Abandonedcart') : __('Abandonedcart'),
            $id ? __('Manage Rule') : __('Manage Rule')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Abandonedcart'));

        /*$resultPage->setActiveMenu('Magedelight_Abandonedcart::rule')
            ->addBreadcrumb(__('Abandonedcart'), __('Abandonedcart'))
            ->addBreadcrumb(__('Manage Rule'), __('Manage Rule'));*/
            
        if ($id === null) {
            $resultPage->addBreadcrumb(__('New Rule'), __('New Rule'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Rule'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Rule'), __('Edit Rule'));
            //$model->setCustomerGroups(explode(",", $model->getCustomerGroups()));
            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $this->coreRegistry->register('magedelight_abandonedcart_rule', $model);
            $resultPage->getConfig()->getTitle()->prepend(
                $model->getRuleName()
            );
        }

        return $resultPage;
    }
}
