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
namespace Magedelight\Commissions\Controller\Adminhtml\Categorycommission;

use Magedelight\Commissions\Api\CategoryCommissionRepositoryInterface;
use Magedelight\Commissions\Controller\Adminhtml\Commissions;
use Magedelight\Commissions\Model\CommissionFactory;
use Magedelight\Commissions\Model\Source\CalculationType;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Commissions
{
    const DEFAULT_PRECISION = 2;
    const DEFAULT_PERCENTAGE_SYMBOL = '%';
    /**
     * @var CategoryCommissionRepositoryInterface
     */
    protected $categoryCommissionRepository;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    protected $commissionFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     *
     * @param JsHelper $jsHelper
     * @param Registry $registry
     * @param Context $context
     * @param StoreManagerInterface $_storeManager
     * @param CurrencyInterface $localeCurrency
     * @param CategoryCommissionRepositoryInterface $categoryCommissionRepository
     * @param PublisherInterface $publisher
     */
    public function __construct(
        JsHelper $jsHelper,
        Registry $registry,
        Context $context,
        StoreManagerInterface $_storeManager,
        CurrencyInterface $localeCurrency,
        CategoryCommissionRepositoryInterface $categoryCommissionRepository,
        CommissionFactory $commissionFactory,
        PublisherInterface $publisher = null
    ) {
        $this->jsHelper = $jsHelper;
        parent::__construct($registry, $categoryCommissionRepository, $context);

        $this->_storeManager = $_storeManager;
        $this->localeCurrency = $localeCurrency;

        $this->messagePublisher = $publisher ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->categoryCommissionRepository = $categoryCommissionRepository;
        $this->commissionFactory = $commissionFactory;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('commission');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = (array_key_exists('commission_id', $data)) ? $data['commission_id'] : false;
            $commission = $this->initCommission($id);

            $eventParams = [];
            $eventParams['is_new'] = 1;
            $eventParams['website'] = $data['website_id'];

            $currencyCode = $this->_storeManager->getDefaultStoreView()->getBaseCurrencyCode(); /* used for evenParams*/
            $currencySymbol = $this->localeCurrency->getCurrency($currencyCode)->getSymbol(); /* used for evenParams*/

            if ($commission) {
                $eventParams['is_new'] = 0;

                if ($commission['calculation_type'] == CalculationType::FLAT_VALUE) {
                    $eventParams['old']['commission'] = $currencySymbol .
                        round($commission['commission_value'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['old']['commission'] = round(
                        $commission['commission_value'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }

                if ($commission['marketplace_fee_type'] == 1) {
                    $eventParams['old']['marketplace_fee'] = $currencySymbol .
                        round($commission['marketplace_fee'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['old']['marketplace_fee'] = round(
                        $commission['marketplace_fee'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }

                if ($commission['cancellation_fee_calculation_type'] == 1) {
                    $eventParams['old']['cancellation_fee'] = $currencySymbol .
                        round($commission['cancellation_fee_commission_value'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['old']['cancellation_fee'] = round(
                        $commission['cancellation_fee_commission_value'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }
                $eventParams['old_category'] = $commission['product_category'];
            } else {
                $commission = $this->commissionFactory->create();
                $eventParams['is_new'] = 1;
            }

            try {
                $commission->setData($data);
                $commission->save();

                $eventParams['website'] = $commission->getWebsiteId();
                $eventParams['category'] = $data['product_category'];
                if ($data['calculation_type'] == CalculationType::FLAT_VALUE) {
                    $eventParams['new']['commission'] = $currencySymbol .
                        round($data['commission_value'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['new']['commission'] = round(
                        $data['commission_value'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }

                if ($data['marketplace_fee_type'] == 1) {
                    $eventParams['new']['marketplace_fee'] = $currencySymbol .
                        round($data['marketplace_fee'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['new']['marketplace_fee'] = round(
                        $data['marketplace_fee'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }

                if ($data['cancellation_fee_calculation_type'] == 1) {
                    $eventParams['new']['cancellation_fee'] = $currencySymbol .
                        round($data['cancellation_fee_commission_value'], self::DEFAULT_PRECISION);
                } else {
                    $eventParams['new']['cancellation_fee'] = round(
                        $data['cancellation_fee_commission_value'],
                        self::DEFAULT_PRECISION
                    ) . self::DEFAULT_PERCENTAGE_SYMBOL;
                }

                /*$this->messagePublisher->publish('category_commission.sendEmail', $commission);
                 $this->messageManager->addSuccessMessage(
                     __('The Category commission has been saved and Vendor email notification
                 is added to queue, wait to send an email soon')
                 );*/
                $this->_eventManager->dispatch('vendor_category_commission_save', $eventParams);
                $this->messageManager->addSuccess(__('The Category commission has been saved.'));
                $this->_getSession()->setRBCommissionsCommissionData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        '*/*/edit',
                        [
                        'commission_id' => $commission->getId(),
                        '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Magento\Framework\Exception\MailException $e) {
                $this->messageManager->addException($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the commission.'));
            }

            $this->_getSession()->setRBCommissionsCommissionData($data);
            $resultRedirect->setPath(
                '*/*/edit',
                [
                'commission_id' => $commission->getId(),
                '_current' => true
                ]
            );
            return $resultRedirect;
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::edit');
    }
}
