<?php

namespace MDC\Sales\Model\Sales\Pdf;

class Shipping extends \Magento\Tax\Model\Sales\Pdf\Shipping
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Shipping constructor.
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->appState = $appState;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $taxConfig, $data);
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $amount = $this->getOrder()->formatPriceTxt(0);
        }else{
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        }
        $amountInclTax = $this->getSource()->getShippingInclTax();
        if (!$amountInclTax) {
            $amountInclTax = $this->getAmount() + $this->getSource()->getShippingTaxAmount();
        }

        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            $amountInclTax = $this->getOrder()->formatPriceTxt(0);
        }else{
            $amountInclTax = $this->getOrder()->formatPriceTxt($amountInclTax);
        }
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        if ($this->_taxConfig->displaySalesShippingBoth($store)) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __('Shipping (Excl. Tax)') . ':',
                    'font_size' => $fontSize,
                ],
                [
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __('Shipping (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                ],
            ];
        } elseif ($this->_taxConfig->displaySalesShippingInclTax($store)) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        } else {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        }

        return $totals;
    }

    /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        if($this->appState->getAreaCode() == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE){
            return false;
        }else{
            $amount = $this->getAmount();
            return $this->getDisplayZero() === 'true' || $amount != 0;
        }
    }
}
