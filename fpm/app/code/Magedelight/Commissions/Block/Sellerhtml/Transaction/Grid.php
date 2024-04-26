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
namespace Magedelight\Commissions\Block\Sellerhtml\Transaction;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Grid extends \Magedelight\Commissions\Block\Sellerhtml\AbstractPayment
{
    const PAGE_LIMIT = 10;

    /**
     * @var \Magedelight\Commissions\Model\Commission\Payment
     */
    protected $payment;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory
     * @param \Magedelight\Commissions\Model\ResourceModel\Reports\Commission\Payment\CollectionFactory $paymentReportCollection
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Commissions\Model\Source\PaymentStatus $paymentStatus
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magedelight\Commissions\Model\Commission\Payment $payment
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
        \Magedelight\Commissions\Model\Commission\Payment $payment,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        $this->payment = $payment;
        $this->storeManager = $storeManager;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getTransactionSummary()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'vendor.transaction.summary.pager'
            );

            $limit = $this->getRequest()->getParam('limit', false);
            $sfrm = $this->getRequest()->getParam('sfrm');
            $pager->setData('pagersfrm', 'confirmed');
            $pager->setTemplate('Magedelight_Commissions::html/pager.phtml');

            if (!$limit) {
                $limit = self::PAGE_LIMIT;
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

    public function getTransactionSummary()
    {
        $data = $this->authSession->getGridSession();

        $financial_transaction_summary = $data['grid_session']['financial_transaction_summary'];
        $collection = $this->_collectionFactory->create();
        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            "main_table.vendor_order_id = rvo.vendor_order_id",
            ['increment_id', 'vendor_order_id', 'grand_total', 'rvo_shipping_amount' => 'rvo.shipping_amount',
                'total_refunded','order_currency_code']
        );
        $collection->addFieldToFilter(
            'main_table.vendor_id',
            ['eq' => $this->authSession->getUser()->getVendorId()]
        );
        //$collection->addFieldToFilter('rvo.is_confirmed',['eq' => "1"]);

        if ($this->getRequest()->getParam('session-clear-transaction') == "1") {
            $financial_transaction_summary = $data['grid_session']['financial_transaction_summary'];
            foreach ($financial_transaction_summary as $key => $val) {
                $financial_transaction_summary[$key] = '';
            }

            $gridsession = $this->authSession->getGridSession();
            $gridsession['grid_session']['financial_transaction_summary'] = $financial_transaction_summary;
            $this->authSession->setGridSession($gridsession);
            $data = $this->authSession->getGridSession();
        }

        if ($q = $this->getRequest()->getParam('q', false)) {
            $collection->addFieldToFilter(
                ['commission_invoice_id', 'purchase_order_id'],
                [
                ['like' => '%' . $q . '%'],
                ['like' => '%' . $q . '%']
                ]
            );
            $financial_transaction_summary['q'] = $q;
        } else {
            if (is_array($financial_transaction_summary) && array_key_exists('q', $financial_transaction_summary)) {
                $collection->addFieldToFilter(
                    ['commission_invoice_id', 'purchase_order_id'],
                    [
                    ['like' => '%' . $financial_transaction_summary['q'] . '%'],
                    ['like' => '%' . $financial_transaction_summary['q'] . '%']
                    ]
                );
            }
        }

        $data = $this->getRequest()->getParams();
        if (!empty($data)) {
            if (isset($data['commission']['from'])) {
                if (!empty($data['commission']['from'])) {
                    $collection->addFieldToFilter(
                        'total_commission',
                        ['gteq' => $data['commission']['from']]
                    );
                }
                $financial_transaction_summary['commision[from]'] = $data['commission']['from'];
            } elseif ((!$financial_transaction_summary['commision[from]'])) {
                $financial_transaction_summary['commision[from]'] = "";
            } else {
                $financial_transaction_summary['commision[from]'] = $financial_transaction_summary['commision[from]'];
                $collection->addFieldToFilter(
                    'total_commission',
                    ['gteq' => $financial_transaction_summary['commision[from]']]
                );
            }
            if (isset($data['commission']['to'])) {
                if (!empty($data['commission']['to'])) {
                    $collection->addFieldToFilter(
                        'total_commission',
                        ['lteq' => $data['commission']['to']]
                    );
                }

                $financial_transaction_summary['commision[to]'] = $data['commission']['to'];
            } elseif ((!$financial_transaction_summary['commision[to]'])) {
                $financial_transaction_summary['commision[to]'] = "";
            } else {
                $financial_transaction_summary['commision[to]'] = $financial_transaction_summary['commision[to]'];
                $collection->addFieldToFilter(
                    'total_commission',
                    ['lteq' => $financial_transaction_summary['commision[to]']]
                );
            }

            if (isset($data['marketplace_fee']['from'])) {
                if (!empty($data['marketplace_fee']['from'])) {
                    $collection->addFieldToFilter(
                        'marketplace_fee',
                        ['gteq' => $data['marketplace_fee']['from']]
                    );
                }

                $financial_transaction_summary['marketplace_fee[from]'] = $data['marketplace_fee']['from'];
            } elseif ((!$financial_transaction_summary['marketplace_fee[from]'])) {
                $financial_transaction_summary['marketplace_fee[from]'] = "";
            } else {
                $financial_transaction_summary['marketplace_fee[from]'] =
                    $financial_transaction_summary['marketplace_fee[from]'];
                $collection->addFieldToFilter(
                    'marketplace_fee',
                    ['gteq' => $financial_transaction_summary['marketplace_fee[from]']]
                );
            }
            if (isset($data['marketplace_fee']['to'])) {
                if (!empty($data['marketplace_fee']['to'])) {
                    $collection->addFieldToFilter(
                        'marketplace_fee',
                        ['lteq' => $data['marketplace_fee']['to']]
                    );
                }

                $financial_transaction_summary['marketplace_fee[to]'] = $data['marketplace_fee']['to'];
            } elseif ((!$financial_transaction_summary['marketplace_fee[to]'])) {
                $financial_transaction_summary['marketplace_fee[to]'] = "";
            } else {
                $financial_transaction_summary['marketplace_fee[to]'] =
                    $financial_transaction_summary['marketplace_fee[to]'];
                $collection->addFieldToFilter(
                    'marketplace_fee',
                    ['lteq' => $financial_transaction_summary['marketplace_fee[to]']]
                );
            }
            if (isset($data['cancellation_fee']['from'])) {
                if (!empty(isset($data['cancellation_fee']['from']))) {
                    $collection->addFieldToFilter(
                        'cancellation_fee',
                        ['gteq' => $data['cancellation_fee']['from']]
                    );
                }
                $financial_transaction_summary['cf[from]'] = $data['cancellation_fee']['from'];
            } elseif ((!$financial_transaction_summary['cf[from]'])) {
                $financial_transaction_summary['cf[from]'] = "";
            } else {
                $financial_transaction_summary['cf[from]'] = $financial_transaction_summary['cf[from]'];
                $collection->addFieldToFilter(
                    'cancellation_fee',
                    ['gteq' => $financial_transaction_summary['cf[from]']]
                );
            }
            if (isset($data['cancellation_fee']['to'])) {
                if (!empty($data['cancellation_fee']['to'])) {
                    $collection->addFieldToFilter(
                        'cancellation_fee',
                        ['lteq' => $data['cancellation_fee']['to']]
                    );
                }
                $financial_transaction_summary['cf[to]'] = $data['cancellation_fee']['to'];
            } elseif ((!$financial_transaction_summary['cf[to]'])) {
                $financial_transaction_summary['cf[to]'] = "";
            } else {
                $financial_transaction_summary['cf[to]'] = $financial_transaction_summary['cf[to]'];
                $collection->addFieldToFilter(
                    'cancellation_fee',
                    ['lteq' => $financial_transaction_summary['cf[to]']]
                );
            }
            if (isset($data['service_tax']['from'])) {
                if (!empty($data['service_tax']['from'])) {
                    $collection->addFieldToFilter(
                        'service_tax',
                        ['gteq' => $data['service_tax']['from']]
                    );
                }
                $financial_transaction_summary['service_tax[from]'] = $data['service_tax']['from'];
            } elseif ((!$financial_transaction_summary['service_tax[from]'])) {
                $financial_transaction_summary['service_tax[from]'] = "";
            } else {
                $financial_transaction_summary['service_tax[from]'] =
                    $financial_transaction_summary['service_tax[from]'];
                $collection->addFieldToFilter(
                    'service_tax',
                    ['gteq' => $financial_transaction_summary['service_tax[from]']]
                );
            }
            if (isset($data['service_tax']['to'])) {
                if (!empty($data['service_tax']['to'])) {
                    $collection->addFieldToFilter(
                        'service_tax',
                        ['lteq' => $data['service_tax']['to']]
                    );
                }

                $financial_transaction_summary['service_tax[to]'] = $data['service_tax']['to'];
            } elseif ((!$financial_transaction_summary['service_tax[to]'])) {
                $financial_transaction_summary['service_tax[to]'] = "";
            } else {
                $financial_transaction_summary['service_tax[to]'] =
                    $financial_transaction_summary['service_tax[to]'];
                $collection->addFieldToFilter(
                    'service_tax',
                    ['lteq' => $financial_transaction_summary['service_tax[to]']]
                );
            }
            if (isset($data['total_amount']['from'])) {
                if (!empty($data['total_amount']['from'])) {
                    $collection->addFieldToFilter(
                        'total_amount',
                        ['gteq' => $data['total_amount']['from']]
                    );
                }

                $financial_transaction_summary['net_payable[from]'] = $data['total_amount']['from'];
            } elseif ((!$financial_transaction_summary['net_payable[from]'])) {
                $financial_transaction_summary['net_payable[from]'] = "";
            } else {
                $financial_transaction_summary['net_payable[from]'] =
                    $financial_transaction_summary['net_payable[from]'];
                $collection->addFieldToFilter(
                    'total_amount',
                    ['gteq' => $financial_transaction_summary['net_payable[from]']]
                );
            }
            if (isset($data['total_amount']['to'])) {
                if (!empty($data['total_amount']['to'])) {
                    $collection->addFieldToFilter(
                        'total_amount',
                        ['lteq' => $data['total_amount']['to']]
                    );
                }

                $financial_transaction_summary['net_payable[to]'] = $data['total_amount']['to'];
            } elseif ((!$financial_transaction_summary['net_payable[to]'])) {
                $financial_transaction_summary['net_payable[to]'] = "";
            } else {
                $financial_transaction_summary['net_payable[to]'] =
                    $financial_transaction_summary['net_payable[to]'];
                $collection->addFieldToFilter(
                    'total_amount',
                    ['lteq' => $financial_transaction_summary['net_payable[to]']]
                );
            }

            if (isset($data['created_at']['from'])) {
                $dateFrom = $this->_date->gmtDate('Y-m-d', $data['created_at']['from']);
                if (!empty($dateFrom)) {
                    $collection->addFieldToFilter(
                        'main_table.created_at',
                        ['gteq' => $dateFrom]
                    );
                }

                $financial_transaction_summary['created_date[from]'] = $dateFrom;
            } elseif ((!$financial_transaction_summary['created_date[from]'])) {
                $financial_transaction_summary['created_date[from]'] = "";
            } else {
                $financial_transaction_summary['created_date[from]'] =
                    $financial_transaction_summary['created_date[from]'];
                $dateFrom = $this->_date->gmtDate(
                    'Y-m-d',
                    $financial_transaction_summary['created_date[from]']
                );
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['gteq' => $dateFrom]
                );
            }
            if (!empty($data['created_at']['to'])) {
                $dateTo = $this->_date->gmtDate('Y-m-d', $data['created_at']['to'] . ' +1 days');
                if (!empty($dateTo)) {
                    $collection->addFieldToFilter(
                        'main_table.created_at',
                        ['lteq' => $dateTo]
                    );
                }

                $financial_transaction_summary['created_date[to]'] = $dateTo;
            } elseif ((!$financial_transaction_summary['created_date[to]'])) {
                $financial_transaction_summary['created_date[to]'] = "";
            } else {
                $financial_transaction_summary['created_date[to]'] =
                    $financial_transaction_summary['created_date[to]'];
                $dateTo = $this->_date->gmtDate(
                    'Y-m-d',
                    $financial_transaction_summary['created_date[to]'] . ' +1 days'
                );
                $collection->addFieldToFilter(
                    'main_table.created_at',
                    ['lteq' => $dateTo]
                );
            }
            if (isset($data['order_number'])) {
                if (!empty($data['order_number'])) {
                    $orderId = $data['order_number'];
                    $collection->addFieldToFilter(
                        'purchase_order_id',
                        ['like' => '%' . trim($orderId) . '%']
                    );
                }

                $financial_transaction_summary['increment_id'] = $data['order_number'];
            } elseif ((!$financial_transaction_summary['increment_id'])) {
                $financial_transaction_summary['increment_id'] = "";
            } else {
                $financial_transaction_summary['increment_id'] =
                    $financial_transaction_summary['increment_id'];
                $collection->addFieldToFilter(
                    'purchase_order_id',
                    ['like' => '%' . trim($financial_transaction_summary['increment_id']) . '%']
                );
            }
            if (!($this->getRequest()->getParam('status') === null) &&
                $this->getRequest()->getParam('status') != '') {
                $status = $data['status'];
                $collection->addFieldToFilter(
                    'main_table.status',
                    ['eq' => $status]
                );
                $financial_transaction_summary['status'] = $status;
            } elseif ((!$financial_transaction_summary['status'])) {
                $financial_transaction_summary['status'] = "";
            } else {
                $financial_transaction_summary['status'] = $financial_transaction_summary['status'];
            }

            $gridsession = $this->authSession->getGridSession();
            $gridsession['grid_session']['financial_transaction_summary'] = $financial_transaction_summary;
            $this->authSession->setGridSession($gridsession);
        }

        $sortOrder = $this->getRequest()->getParam('sort_order', 'created_at');
        $direction = $this->getRequest()->getParam('dir', 'DESC');
        $this->_addSortOrderToCollection($collection, $sortOrder, $direction);
        $collection->addFieldToFilter(
            'main_table.website_id',
            ['eq' => $this->_storeManager->getStore()->getWebsiteId()]
        );
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
        $vendorOrder = $this->_vendorOrderFactory->load($_item->getVendorOrderId());
        return $vendorOrder;
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('rbsales/transaction/summary');
    }

    /**
     *
     * @param integer $optionId
     * @return string
     */
    public function getStatus($optionId)
    {
        return $this->_paymentStatus->getOptionText($optionId);
    }

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_paymentStatus->toOptionArray();
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
     * @return boolean
     */
    public function isLiable()
    {
        return $this->payment->isVendorLiableForShipping($this->_storeManager->getStore()->getWebsiteId());
    }
}
