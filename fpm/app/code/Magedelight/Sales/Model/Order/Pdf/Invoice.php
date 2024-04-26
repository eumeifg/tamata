<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\Order\Pdf;

/**
 * Sales Order Invoice PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $vendorHelper;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magedelight\Vendor\Helper\Data $vendorHelper,
        array $data = []
    ) {
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
        $this->vendorHelper = $vendorHelper;
    }

    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            /*$this->insertVendorAddress($page, $invoice->getStore(), $invoice->getVendorId());*/
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

            $vendor = $this->getVendorDetail($invoice->getVendorId());
            if (!empty($vendor)) {
                $vendorName = '';
                if (!empty($vendor->getBusinessName())) {
                    $vendorName = $vendor->getBusinessName();
                }
                $this->insertVendorName($page, __('Sold By : ') . $vendorName);
                $this->insertVendorAddress(
                    $page,
                    __('Address : ') . $this->getFullAddress($invoice->getVendorId())
                );
                $this->insertVendorVat($page, __('VAT : ') . $vendor->getVat());

                if (!empty($this->getVendorGstin($invoice->getVendorId()))) {
                    $this->insertVendorGstin(
                        $page,
                        __('GSTIN : ') . $this->getVendorGstin($invoice->getVendorId())
                    );
                }
            }

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    public function getVendorGstin($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if ($this->vendorHelper->isModuleEnabled('RB_VendorInd') &&
            $this->vendorHelper->getConfigValue('rbvendorind/general_settings/enabled')) {
            if (!empty($vendor)) {
                return $vendor->getGstin();
            }
        }
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorDetail($vendorId)
    {
        return $this->vendorHelper->getVendorDetails($vendorId);
    }

    public function getFullAddress($vendorId)
    {
        $vendor = $this->getVendorDetail($vendorId);
        if (!empty($vendor)) {
            return $vendor->getAddress1() . ' ' . $vendor->getAddress2() . ' ' . $vendor->getCity() . ' '
                . $vendor->getRegion() . ' ' . $vendor->getPincode();
        }
    }

    /**
     * Insert title and number for concrete document type
     *
     * @param \Zend_Pdf_Page $page
     * @param string $text
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function insertVendorName(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 220, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param $text
     * @throws \Zend_Pdf_Exception
     */
    public function insertVendorAddress(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 220, $docHeader[1] - 30, 'UTF-8');
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param $text
     * @throws \Zend_Pdf_Exception
     */
    public function insertVendorVat(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 220, $docHeader[1] - 45, 'UTF-8');
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param $text
     * @throws \Zend_Pdf_Exception
     */
    public function insertVendorGstin(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 220, $docHeader[1] - 60, 'UTF-8');
    }
}
