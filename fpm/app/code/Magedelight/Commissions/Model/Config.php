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
namespace Magedelight\Commissions\Model;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class Config
{

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/global_commission_change/template';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @param \Magedelight\Theme\Model\Users $usersModel
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory,
        \Magedelight\Theme\Model\Users $usersModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->usersModel = $usersModel;
        $this->collectionFactory = $collectionFactory;
    }

    public function beforesave($subject)
    {
        if ($subject->getSection() == 'commission') {
            $this->registry->register(
                'commission_value',
                $this->scopeConfig->getValue('commission/general/commission_value')
            );
            $this->registry->register(
                'commission_calc_type',
                $this->scopeConfig->getValue('commission/general/commission_calc_type')
            );
            $this->registry->register(
                'marketplace_fee',
                $this->scopeConfig->getValue('commission/general/marketplace_fee')
            );
            $this->registry->register(
                'marketplace_fee_calc_type',
                $this->scopeConfig->getValue('commission/general/marketplace_fee_calc_type')
            );
            $this->registry->register(
                'cancellation_fee',
                $this->scopeConfig->getValue('commission/general/cancellation_fee')
            );
            $this->registry->register(
                'cancellation_fee_calc_type',
                $this->scopeConfig->getValue('commission/general/cancellation_fee_calc_type')
            );
            $this->registry->register(
                'service_tax',
                $this->scopeConfig->getValue('commission/general/service_tax')
            );
        }
    }

    public function aftersave($subject)
    {
        if ($subject->getSection() == 'commission') {
            if ($this->scopeConfig->getValue('emailconfiguration/global_commission_change/enabled')) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('is_system', '0');
                $collection->getSelect()->joinLeft(
                    ['rvwd'=>'md_vendor_website_data'],
                    'rvwd.vendor_id = main_table.vendor_id',
                    ['name']
                );
                $collection->addFieldToFilter(
                    'rvwd.status',
                    ['in' => [VendorStatus::VENDOR_STATUS_ACTIVE, VendorStatus::VENDOR_STATUS_VACATION_MODE]]
                );
                $collection->addFieldToSelect(['email']);

                $isCommissionChanged = false;
                $templateVars = [];
                if ($this->scopeConfig->getValue(
                    'commission/general/commission_value'
                ) != $this->registry->registry('commission_value')) {
                    $isCommissionChanged = true;
                    $templateVars['cur_comm'] = $this->scopeConfig->getValue(
                        'commission/general/commission_value'
                    ) . ' (' . $this->getCalculationType($this->scopeConfig->getValue(
                        'commission/general/commission_calc_type'
                    )) . ')';
                    $templateVars['old_comm'] = $this->registry->registry(
                        'commission_value'
                    ) . ' (' . $this->getCalculationType(
                        $this->registry->registry('commission_calc_type')
                    ) . ')';
                }

                if ($this->scopeConfig->getValue(
                    'commission/general/marketplace_fee'
                ) != $this->registry->registry('marketplace_fee')) {
                    $isCommissionChanged = true;
                    $templateVars['cur_mp_fee'] = $this->scopeConfig->getValue(
                        'commission/general/marketplace_fee'
                    ) . ' (' . $this->getCalculationType(
                        $this->scopeConfig->getValue('commission/general/marketplace_fee_calc_type')
                    ) . ')';
                    $templateVars['old_mp_fee'] = $this->registry->registry(
                        'marketplace_fee'
                    ) . ' (' . $this->getCalculationType($this->registry->registry(
                        'marketplace_fee_calc_type'
                    )) . ')';
                }

                if ($this->scopeConfig->getValue(
                    'commission/general/cancellation_fee'
                ) != $this->registry->registry('cancellation_fee')) {
                    $isCommissionChanged = true;
                    $templateVars['cur_can_fee'] = $this->scopeConfig->getValue(
                        'commission/general/cancellation_fee'
                    ) . ' (' . $this->getCalculationType(
                        $this->scopeConfig->getValue('commission/general/cancellation_fee_calc_type')
                    ) . ')';
                    $templateVars['old_can_fee'] = $this->registry->registry(
                        'cancellation_fee'
                    ) . ' (' . $this->getCalculationType(
                        $this->registry->registry('cancellation_fee_calc_type')
                    ) . ')';
                }

                if ($this->scopeConfig->getValue(
                    'commission/general/service_tax'
                ) != $this->registry->registry('service_tax')) {
                    $isCommissionChanged = true;
                    $templateVars['cur_service_tax'] = $this->scopeConfig->getValue(
                        'commission/general/service_tax'
                    );
                    $templateVars['old_service_tax'] = $this->registry->registry('service_tax');
                }

                if ($isCommissionChanged) {
                    foreach ($collection as $collData) {
                        $userEmails = $this->usersModel->getUserEmails(
                            $collData->getVendorId(),
                            'Magedelight_Vendor::financial'
                        );
                        $templateVars['vendor_name'] = $collData->getName();
                        $this->_sendNotification($collData->getEmail(), $templateVars, $userEmails);
                    }
                }
            }
        }
    }

    public function _sendNotification($email, $templateVars, $userEmails = [])
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
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

    protected function getCalculationType($type = '')
    {
        if ($type == 1) {
            return "Flat";
        } else {
            return "%";
        }
    }
}
