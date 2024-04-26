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
namespace Magedelight\Commissions\Observer;

use Magedelight\Commissions\Model\Commission\Payment as PaymentCommissionModel;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class VendorPaymentStatusChange implements ObserverInterface
{
    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_comm/template';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magedelight\Commissions\Model\Commission\Payment $payment,
        \Magedelight\Theme\Model\Users $usersModel
    ) {
        $this->_paymentcom = $payment;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->priceHelper = $priceHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('emailconfiguration/vendor_comm/enabled')) {
            $data = $observer->getEvent()->getVendorPaymentIds();

            // ------- Adjust the debit transaction total amount with that vendors credit transaction total amount START
            $credit_data = $observer->getEvent()->getVndrTransctionIdsAmounts();

            if ($credit_data) {
                foreach ($credit_data as $key => $value) {
                    $trans_credit_total = (float)$value['amount'];

                    $debit_trans_collection = $this->_paymentcom->getCollection()
                    ->addFieldToFilter('transaction_type', PaymentCommissionModel::DEBIT_TRANSACTION_TYPE)
                    //->addFieldToFilter('status',  array('neq' => 1))
                    ->addFieldToFilter('vendor_id', $value['vendor_id'])
                    ->addFieldToFilter('is_settled', ['eq' => 0])
                    ->setOrder('updated_at', 'asc');
                    $debit_trans_collection->getSelect()->limit(1);

                    foreach ($debit_trans_collection as $key => $update_debit_trans_collection) {

                        $update_debit_trans_collection->setIsSettled(1);
                        $update_debit_trans_collection->setSettledTransactionId($value['purchase_order_id']);

                        $credit_trans_collection = $this->_paymentcom->getCollection()->addFieldToFilter(
                            'vendor_payment_id',
                            $value['p_payment_id']
                        );

                        foreach ($credit_trans_collection as $key => $update_credit_trans_collection) {
                            $credit_total =  $trans_credit_total + $update_debit_trans_collection->getTotalAmount();

                            $update_credit_trans_collection->setTotalAmount($credit_total);

                            $update_credit_trans_collection->setIsSettled(1);
                            $update_credit_trans_collection->setSettledTransactionId(
                                $update_debit_trans_collection->getPurchaseOrderId()
                            );
                            $update_credit_trans_collection->save();
                        }
                    }
                    $debit_trans_collection->save();
                }
            }
            // Adjust the debit transaction total amount with that vendors credit transaction total amount END -------

            $collection = $this->_paymentcom->getCollection()->addFieldToFilter('vendor_payment_id', $data);
            $collection->getSelect()->joinLeft(
                ['rbv' => 'md_vendor'],
                "main_table.vendor_id = rbv.vendor_id",
                ['rbv.email']
            );
            $collection->getSelect()->joinLeft(
                ['rbvw' => 'md_vendor_website_data'],
                "rbv.vendor_id = rbvw.vendor_id",
                ['rbvw.name', 'vendor_store_id' => 'rbvw.store_id']
            );
            foreach ($collection as $coll) {
                $email = $coll->getEmail();
                $name = $coll->getName();
                $totalAmount = $coll->getTotalAmount();
                $formattedTotalAmount = $this->priceHelper->currency($totalAmount, true, false);
                $porderid = $coll->getPurchaseOrderId();
                $vendorStoreId = $coll->getVendorStoreId();
                $userEmails = $this->usersModel->getUserEmails($coll->getVendorId(), 'Magedelight_Vendor::financial');
                $this->_sendNotification($email, $porderid, $name, $formattedTotalAmount, $vendorStoreId, $userEmails);
            }
        }
    }

    protected function _sendNotification($email, $porderid, $name, $totalAmount, $vendorStoreId, $userEmails = [])
    {
        $templateVars = [
            'purchase_order_id' => $porderid,
            'total_amount' => $totalAmount,
            'name' => $name
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $vendorStoreId,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope('general')
            ->addTo($email);

        if (!empty($userEmails)) {
            foreach ($userEmails as $userEmail) {
                $this->_transportBuilder->addTo($userEmail);
            }
        }

        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
