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
namespace Magedelight\Commissions\Block\Sellerhtml\Commission;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Grid extends \Magedelight\Commissions\Block\Sellerhtml\AbstractPayment
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus,
        PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $collectionFactory,
            $paymentReportCollection,
            $authSession,
            $vendorOrderFactory,
            $paymentStatus,
            $priceFormatter,
            $date,
            $priceHelper,
            $data
        );
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCommisionSummary()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.commision.summary.pager'
            );

            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'confirmed');
            $pager->setTemplate('Magedelight_Commissions::html/pager.phtml');

            if (!$limit) {
                $limit = 10;
                $pager->setPage(1);
            }
            $pager->setLimit($limit)
                ->setCollection(
                    $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    public function getCommisionSummary()
    {
        $data = $this->authSession->getGridSession();
        if ($this->getRequest()->getParam('session-clear-commision') == "1") {
            $financial_transaction_summary = $data['grid_session']['financial_commision_invoice'];
            foreach ($financial_transaction_summary as $key => $val) {
                $financial_transaction_summary[$key] = '';
            }

            $gridsession = $this->authSession->getGridSession();
            $gridsession['grid_session']['financial_commision_invoice'] = $financial_transaction_summary;
            $this->authSession->setGridSession($gridsession);
            $data = $this->authSession->getGridSession();
        }

        $financial_commision_invoice = $data['grid_session']['financial_commision_invoice'];
        $collection = $this->_collectionFactory->create();
        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            "main_table.vendor_order_id = rvo.vendor_order_id",
            ['order_currency_code']
        );
        $collection->getSelect()->columns([
            'total_fees' => new \Zend_Db_Expr(
                '(total_commission + marketplace_fee + cancellation_fee + service_tax)'
            )
        ]);

        $collection->addFieldToFilter(
            'main_table.vendor_id',
            ['eq' => $this->authSession->getUser()->getVendorId()]
        )
            ->addFieldToFilter('main_table.status', ['eq' => 1]);

        if ($q = $this->getRequest()->getParam('q', false)) {
            $collection->addFieldToFilter(
                ['commission_invoice_id', 'purchase_order_id'],
                [
                ['like' => '%' . $q . '%'],
                ['like' => '%' . $q . '%']
                ]
            );
            $financial_commision_invoice['q'] = $q;
        } else {
            if ($financial_commision_invoice['q']) {
                $collection->addFieldToFilter(
                    ['commission_invoice_id', 'purchase_order_id'],
                    [
                    ['like' => '%' . $financial_commision_invoice['q'] . '%'],
                    ['like' => '%' . $financial_commision_invoice['q'] . '%']
                    ]
                );
            }
        }

        $data = $this->getRequest()->getParams();
        if (!empty($data)) {
            if (isset($this->getRequest()->getParam('paid_at')['from'])) {
                $dateFrom = $this->_date->gmtDate(
                    null,
                    $this->getRequest()->getParam('paid_at')['from']
                );
                if (!empty($dateFrom)) {
                    $collection->addFieldToFilter(
                        'paid_at',
                        ['gteq' => $dateFrom]
                    );
                }
                $financial_commision_invoice['invoice_genereated_date[from]'] = $dateFrom;
            } elseif (!$financial_commision_invoice['invoice_genereated_date[from]']) {
                $financial_commision_invoice['invoice_genereated_date[from]'] = "";
            } else {
                $dateFrom = $this->_date->gmtDate(
                    null,
                    $financial_commision_invoice['invoice_genereated_date[from]']
                );
                $collection->addFieldToFilter(
                    'paid_at',
                    ['gteq' => $dateFrom]
                );
                $financial_commision_invoice['invoice_genereated_date[from]'] = $dateFrom;
            }

            if (!empty($this->getRequest()->getParam('paid_at')['to'])) {
                $dateTo = $this->_date->gmtDate(
                    null,
                    $this->getRequest()->getParam('paid_at')['to'] . ' +1 days'
                );
                if (!empty($dateTo)) {
                    $collection->addFieldToFilter(
                        'paid_at',
                        ['lteq' => $dateTo]
                    );
                }
                $financial_commision_invoice['invoice_genereated_date[to]'] = $dateTo;
            } elseif (!array_key_exists(
                $financial_commision_invoice['invoice_genereated_date[to]'],
                $financial_commision_invoice
            ) && !$financial_commision_invoice['invoice_genereated_date[to]']) {
                $financial_commision_invoice['invoice_genereated_date[to]'] = "";
            } else {
                $financial_commision_invoice['invoice_genereated_date[to]'] =
                    $financial_commision_invoice['invoice_genereated_date[to]'];
                $dateTo = $this->_date->gmtDate(
                    null,
                    $financial_commision_invoice['invoice_genereated_date[to]'] . ' +1 days'
                );
                $collection->addFieldToFilter(
                    'paid_at',
                    ['lteq' => $dateTo]
                );
            }
            $invoiceId = $this->getRequest()->getParam('invoice_number');
            if (isset($invoiceId)) {
                if (!empty($invoiceId)) {
                    $collection->addFieldToFilter(
                        'commission_invoice_id',
                        ['like' => '%' . trim($invoiceId)]
                    );
                }
                $financial_commision_invoice['consolidation_invoice_number'] = trim($invoiceId);
            } elseif (!$financial_commision_invoice['consolidation_invoice_number']) {
                $financial_commision_invoice['consolidation_invoice_number'] = "";
            } else {
                $financial_commision_invoice['consolidation_invoice_number'] =
                    $financial_commision_invoice['consolidation_invoice_number'];
                $collection->addFieldToFilter(
                    'commission_invoice_id',
                    ['like' => '%' . trim($financial_commision_invoice['consolidation_invoice_number'])]
                );
            }
            $totalFees = new \Zend_Db_Expr(
                '(total_commission + marketplace_fee + cancellation_fee + service_tax)'
            );

            if (isset($this->getRequest()->getParam('total_commission')['from'])) {
                if (!empty($this->getRequest()->getParam('total_commission')['from'])) {
                    $collection->addFieldToFilter(
                        $totalFees,
                        ['gteq' => $this->getRequest()->getParam('total_commission')['from']]
                    );
                }

                $financial_commision_invoice['total_fees_amount[from]'] = $this->getRequest()->getParam(
                    'total_commission'
                )['from'];
            } elseif (!$financial_commision_invoice['total_fees_amount[from]']) {
                $financial_commision_invoice['total_fees_amount[from]'] = "";
            } else {
                $financial_commision_invoice['total_fees_amount[from]'] =
                    $financial_commision_invoice['total_fees_amount[from]'];
                $collection->addFieldToFilter(
                    $totalFees,
                    $financial_commision_invoice['total_fees_amount[from]']
                );
            }

            if (isset($this->getRequest()->getParam('total_commission')['to'])) {
                if (!empty($this->getRequest()->getParam('total_commission')['to'])) {
                    $collection->addFieldToFilter(
                        $totalFees,
                        ['lteq' => $this->getRequest()->getParam('total_commission')['to']]
                    );
                }

                $financial_commision_invoice['total_fees_amount[to]'] = $this->getRequest()->getParam(
                    'total_commission'
                )['to'];
            } elseif (!$this->getRequest()->getParam('total_commission')['to']) {
                $financial_commision_invoice['total_fees_amount[to]'] = "";
            } else {
                $this->getRequest()->getParam('total_commission')['to'] = $this->getRequest()->getParam(
                    'total_commission'
                )['to'];
                $collection->addFieldToFilter(
                    $totalFees,
                    $this->getRequest()->getParam('total_commission')['to']
                );
            }
            $gridsession = $this->authSession->getGridSession();
            $gridsession['grid_session']['financial_commision_invoice'] = $financial_commision_invoice;

            $this->authSession->setGridSession($gridsession);
        }
        $sortOrder = $this->getRequest()->getParam('sort_order', 'paid_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);

        $this->setCollection($collection);

        return $collection;
    }

    /**
     *
     * @param type $collection
     * @param string $sortOrder
     * @param string $direction
     */
    protected function _addSortOrderToCollection(
        $collection,
        $sortOrder,
        $direction
    ) {
        $collection->getSelect()->order($sortOrder . ' ' . $direction);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     *
     * @param type $_item
     * @return type
     */
    public function getVendorOrderData($_item)
    {
        if (isNull($this->_vendorOrder)) {
            $this->_vendorOrder = $this->_vendorOrderFactory->load($_item->getVendorOrderId());
        }
        return $this->_vendorOrder;
    }

    /**
     *
     * @param Date $date
     * @return Date
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/commission/index');
    }

    /**
     *
     * @param type $_order
     * @return string
     */
    public function getInvoiceDownloadUrl($_order)
    {
        return $this->getUrl(
            'rbsales/commission/view',
            ['vendor_order_id' => $_order->getVendorOrderId()]
        );
    }

    /**
     *
     * @param type $id
     * @return string
     */
    public function getInvoiceUrl($id)
    {
        return $this->getUrl(
            'rbsales/commission/print',
            ['id' => $id, 'tab' => '3,1']
        );
    }
}
