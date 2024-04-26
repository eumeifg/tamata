<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating;

class Save extends \Magedelight\Vendor\Controller\Adminhtml\Review\Vendorrating
{
    protected $_date;
    
    protected $vendorRating;
    
    protected $backendSession;
    
    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Vendorrating $vendorRating
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\VendorratingFactory $vendorratingFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Vendorrating $vendorRating
    ) {
        parent::__construct($context, $vendorratingFactory, $resultPageFactory);
        $this->_date = $date;
        $this->vendorRating = $vendorRating;
        $this->backendSession = $context->getSession();
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->vendorRating;
            $id = $this->getRequest()->getParam('vendor_rating_id');
            if ($id) {
                $model->load($id);
            }
            if ($data['vendor_rating_id']) {
                 $model->setData('is_shared', $data['is_shared']);
                 $model->setData('shared_at', $this->_date->gmtDate());
            } else {
                $model->setData($data);
            }
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Vendor Rating has been saved.'));
                $this->backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['vendor_rating_id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Vendor Rating.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['vendor_rating_id' => $this->getRequest()->getParam('vendor_rating_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::edit_vendorrating');
    }
}
