<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod;

use Magento\Framework\Model\Exception as FrameworkException;

class MassDelete extends \Magedelight\Shippingmatrix\Controller\Adminhtml\Shippingmethod
{

    /**
     * @var \Magedelight\Shippingmatrix\Model\ShippingMethodFactory
     */
    protected $shippingMethodFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Shippingmatrix\Model\ShippingMethodFactory $shippingMethodFactory
    ) {
        $this->shippingMethodFactory = $shippingMethodFactory;
        parent::__construct($context);
    }

    /**
     * Check Grid List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Shippingmatrix::delete');
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $requestIds = $this->getRequest()->getParam('selected');
        if (!is_array($requestIds)) {
            $this->messageManager->addError(__('Please select shipping method(s).'));
        } else {
            try {
                foreach ($requestIds as $requestId) {
                    $szchart = $this->shippingMethodFactory->create()->load($requestId);
                    $szchart->delete();
                }
                $this->messageManager->addSuccess(
                    __('%1 record(s) have been deleted.', count($requestIds))
                );
            } catch (FrameworkException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting record(s).'));
            }
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('rbshippingmatrixrate/shippingmethod/index');
        return $redirectResult;
    }
}
