<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Controller\Adminhtml\Vendorcommission;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magedelight\Commissions\Controller\Adminhtml\Vendorcommission
{

    /**
     * @var CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magedelight\Commissions\Model\Vendorcommission
     */
    protected $vendorCommission;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Commissions\Model\Vendorcommission $vendorCommission
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magedelight\Commissions\Model\VendorcommissionFactory $vendorcommissionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Commissions\Model\Vendorcommission $vendorCommission,
        \Magento\Backend\Model\Session $session,
        StoreManagerInterface $_storeManager,
        CurrencyInterface $localeCurrency
    ) {
        parent::__construct($context, $vendorcommissionFactory, $resultPageFactory);
        $this->vendorCommission = $vendorCommission;
        $this->session = $session;
        $this->_storeManager = $_storeManager;
        $this->localeCurrency = $localeCurrency;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::edit');
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('vendor_commission_id');
            $eventParams = [];
            $eventParams['is_new'] = 1;
            $eventParams['website'] = $data['website_id'];
            $currencyCode = $this->_storeManager->getDefaultStoreView()->getBaseCurrencyCode();
            $currencySymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();

            if ($id) {
                $this->vendorCommission->load($id);
                $commission = $this->vendorCommission->getData();
                $eventParams['is_new'] = 0;
                if ($commission['vendor_calculation_type'] == 1) {
                    $eventParams['old']['commission'] = $currencySymbol .
                        round($commission['vendor_commission_value'], 2);
                } else {
                    $eventParams['old']['commission'] = round($commission['vendor_commission_value'], 2) . '%';
                }

                if ($commission['vendor_marketplace_fee_type'] == 1) {
                    $eventParams['old']['marketplace_fee'] = $currencySymbol .
                        round($commission['vendor_marketplace_fee'], 2);
                } else {
                    $eventParams['old']['marketplace_fee']=round(
                        $commission['vendor_marketplace_fee'],
                        2
                    ) . '%';
                }

                if ($commission['vendor_cancellation_fee_type'] == 1) {
                    $eventParams['old']['cancellation_fee'] = $currencySymbol .
                        round($commission['vendor_cancellation_fee'], 2);
                } else {
                    $eventParams['old']['cancellation_fee'] = round(
                        $commission['vendor_cancellation_fee'],
                        2
                    ) . '%';
                }
                $eventParams['old_vendor_id'] = $commission['vendor_id'];
            }
            $this->vendorCommission->setData($data);
            try {
                $this->vendorCommission->save();
                $eventParams['vendor_id'] = $data['vendor_id'];
                if ($data['vendor_calculation_type'] == 1) {
                    $eventParams['new']['commission'] = $currencySymbol .
                        round($data['vendor_commission_value'], 2);
                } else {
                    $eventParams['new']['commission'] = round($data['vendor_commission_value'], 2) . '%';
                }

                if ($data['vendor_marketplace_fee_type'] == 1) {
                    $eventParams['new']['marketplace_fee'] = $currencySymbol .
                        round($data['vendor_marketplace_fee'], 2);
                } else {
                    $eventParams['new']['marketplace_fee'] = round($data['vendor_marketplace_fee'], 2) . '%';
                }

                if ($data['vendor_cancellation_fee_type'] == 1) {
                    $eventParams['new']['cancellation_fee'] = $currencySymbol .
                        round($data['vendor_cancellation_fee'], 2);
                } else {
                    $eventParams['new']['cancellation_fee'] = round($data['vendor_cancellation_fee'], 2) . '%';
                }
                $this->_eventManager->dispatch('vendor_commission_save', $eventParams);
                $this->messageManager->addSuccess(__('The Vendor commission has been saved.'));
                $this->session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['vendor_commission_id' => $this->vendorCommission->getId(),
                        '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addError('Vendor commission already exists with this
                 vendor on selected site.');
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Vendor type.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect(
                '*/*/edit',
                ['vendor_commission_id' => $this->getRequest()->getParam('vendor_commission_id')]
            );
            return;
        }
        $this->_redirect('*/*/');
    }
}
