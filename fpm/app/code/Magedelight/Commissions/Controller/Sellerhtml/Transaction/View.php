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
namespace Magedelight\Commissions\Controller\Sellerhtml\Transaction;

use Magedelight\Commissions\Model\Source\CalculationType;
use Magento\Framework\App\ObjectManager;

use Magento\Framework\Serialize\Serializer\Json;

class View extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    private $design;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    protected $_storeManager;

    /**
     * @var \Magedelight\Catalog\Helper\Pricing\Data
     */
    protected $priceHelper;

    protected $currencyCode = null;
    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        Json $serializer = null,
        \Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory $collectionFactory,
        \Magedelight\Commissions\Model\Source\CalculationType $calculationType,
        \Magedelight\Commissions\Model\Source\CommissionType $commissionType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Catalog\Helper\Pricing\Data $priceHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->design = $design;
        $this->calculationType = $calculationType;
        $this->commissionType = $commissionType;
        $this->_storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        parent::__construct($context);
    }

    /**
     * Default vendor account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $vendorPaymentId = $this->getRequest()->getParam('id');

        $collection = $this->_collectionFactory->create();

        $collection->getSelect()->join(
            ['rvo' => 'md_vendor_order'],
            "main_table.vendor_order_id = rvo.vendor_order_id",
            ['subtotal']
        );

        $result = $collection->addFieldToFilter(
            'vendor_payment_id',
            ['eq' =>$vendorPaymentId]
        )->getFirstItem();
        try {
            if ($result->getTransactionSummary() == null) {
                $transactionSummary = "";
            } else {
                $transactionSummary = $this->serializer->unserialize($result->getTransactionSummary());
            }

            $html = '';
            $html .= '<table class="data table table-transaction-items rb-table-transaction-items history">';

            $html .= '<tbody>';
            if (!empty($transactionSummary)) {
                $calculationTypes = $this->calculationType->toArray();
                $commissionTypes = $this->commissionType->toOptionArray(true);

                foreach ($transactionSummary as $key => $item) {
                    // $html .= '<tr>';

                    $html .= '<tr> 
                              <td class="col"> ' . __("Item Name") . '</td>
                              <td class="col"> ' . $item['itemName'] . ' </td>
                              </tr>';

                    if (isset($item['total_commission']) && !empty($item['total_commission'])):
                        $html .= '<tr>';

                        $html .= '<td class="col"> ' . __("Commission") . ' ';

                        foreach ($commissionTypes as $commissionType) {
                            if ($commissionType['value'] == $item['total_commission']['precedence']) {
                                $html .= '(' . __($commissionType['label']->getText() . ' Level') . ')';
                            }
                        }
                        $html .= '</td>';

                        $html .= '<td class="col">';

                        if ($calculationTypes[$item['total_commission']['calculationType']] ==
                            CalculationType::PERCENTAGE_LABEL) {
                            $html .= number_format($item['total_commission']['commissionRate'], 2) . '%';
                        } else {
                            $html .= $this->formatAmount($item['total_commission']['commissionRate']) .
                            ' (' . CalculationType::FLAT_LABEL . ')';
                        }

                        $html .= '</td>';

                        $html .= '</tr>';
                    endif;

                    if (isset($item['marketplace_fee']) && !empty($item['marketplace_fee'])):
                        $html .= '<tr>';

                        $html .= '<td class="col"> ' . __("Marketplace Fee") . ' ';

                        foreach ($commissionTypes as $commissionType) {
                            if ($commissionType['value'] == $item['marketplace_fee']['precedence']) {
                                $html .= '(' . __($commissionType['label']->getText() . ' Level') . ')';
                            }
                        }

                        $html .= '</td>';

                        $html .= '<td class="col">';

                        if ($calculationTypes[$item['marketplace_fee']['calculationType']] ==
                        CalculationType::PERCENTAGE_LABEL) {
                            $html .= number_format($item['marketplace_fee']['commissionRate'], 2) . '%';
                        } else {
                            $html .= $this->formatAmount($item['marketplace_fee']['commissionRate']) .
                            ' (' . CalculationType::FLAT_LABEL . ')';
                        }

                        $html .= '</td>';
                        $html .= '</tr>';
                    endif;

                    if (isset($item['cancellation_fee']) && !empty($item['cancellation_fee'])):
                        $html .= '<tr>';

                        $html .= '<td class="col"> ' . __("Cancellation Fee") . '</td>';

                        $html .= '<td class="col">';

                        if ($calculationTypes[$item['cancellation_fee']['calculationType']] ==
                        CalculationType::PERCENTAGE_LABEL) {
                            $html .= $item['cancellation_fee']['commissionRate'] . '%';
                        } else {
                            $html .= $this->formatAmount($item['cancellation_fee']['commissionRate']) .
                            ' (' . CalculationType::FLAT_LABEL . ')';
                        }

                        $html .= '</td>';
                        $html .= '</tr>';
                    endif;

                    if (isset($item['service_tax']) && !empty($item['service_tax'])):

                        $html .= '<tr>';
                        $html .= '<td class="col"> ' . __("Service Tax") . '</td>';
                        $html .= '<td class="col"> ' . number_format($item['service_tax'], 2) . '' .
                        __("% (Levied on commission and marketplace fee.)") . ' </td>';
                        $html .= '</tr>';

                    endif;

                }
            } else {
                $html .= '<tr>';
                $html .= '<td class="col" colspan="5">' . __('Data Not Available') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '</table>';
        } catch (\Exception $e) {
            $html = '<tr>';
            //$html .= '<td class="col" colspan="5">'.__('Data Not Available').'</td>';
            $html .= '<td class="col" colspan="5">' . __($e->getMessage()) . '</td>';
            $html .= '</tr>';
        }
        $response->setContents(json_encode($html));
        return $response;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::financial');
    }

    public function formatAmount($amount)
    {
        return $this->priceHelper->format($amount, false, null, null, $this->currencyCode);
    }
}
