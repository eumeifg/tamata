<?php
namespace MDC\Sales\Model\Sales\Order\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;

class Invoice extends \Magedelight\Sales\Model\Sales\Order\Pdf\Invoice
{

    protected $timezone;

    protected $_rootDirectory;

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
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\Information $information,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magedelight\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Ktpl\BarcodeGenerator\Helper\convertToArabic $arabicTextHelper,
        array $data = []
    ) {
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver, $authSession, $countryFactory, $registry, $information, $taxHelper, $userContext, $vendorRepository, $data);
        $this->timezone = $timezone;
        $this->arabicTextHelper = $arabicTextHelper;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Insert order to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true, $invoice = null)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        /*$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);*/
        //$this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
        $this->_setFontRegular($page, 10);
        if ($putOrderId) {
            $page->drawText(__('Order ID # ') . $order->getRealOrderId(), 420, $top -= 10, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Date: ') .
            $this->timezone->date(new \DateTime($order->getCreatedAt()))->format('d-m-Y'),
            420,
            $top,
            'UTF-8'
        );

        $page->drawText(__('Invoice No: ') . $invoice->getIncrementId(), 420, $top -= 15, 'UTF-8');

        $top -= 10;
        /*$page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);*/

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        /*$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Billing address:'), 35, $top + 40, 'UTF-8');

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y + 65, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }*/

        if (!$order->getIsVirtual()) {
            //$page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        /*$page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));*/
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        /*....Start check for arabic text if arabic it change text direction from right to left....*/
        foreach($billingAddress as $key => $address){
            if ($this->arabicTextHelper->is_arabic($billingAddress[$key])) {
                $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[$key]);
                $billingAddress[$key] = $orgText;
            } else {
                $billingAddress[$key] = $billingAddress[$key];
            }
        }
        /*if ($this->arabicTextHelper->is_arabic($billingAddress[0])) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[0]);
            $billingAddress[0] = $orgText;
        } else {
            $billingAddress[0] = $billingAddress[0];
        }
        if ($this->arabicTextHelper->is_arabic($billingAddress[1])) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[1]);
            $billingAddress[1] = $orgText;
        } else {
            $billingAddress[1] = $billingAddress[1];
        }
        if ($this->arabicTextHelper->is_arabic($billingAddress[2])) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[2]);
            $billingAddress[2] = $orgText;
        } else {
            $billingAddress[2] = $billingAddress[2];
        }
        if ($this->arabicTextHelper->is_arabic($billingAddress[3])) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[3]);
            $billingAddress[3] = $orgText;
        } else {
            $billingAddress[3] = $billingAddress[3];
        }
        if ($this->arabicTextHelper->is_arabic($billingAddress[4])) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($billingAddress[4]);
            $billingAddress[4] = $orgText;
        } else {
            $billingAddress[4] = $billingAddress[4];
        }*/
        /*....end check for arabic text if arabic it change text direction from right to left....*/

        

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            $yShipments = $this->y;
            $tracks = [];
            $currentY = min($yPayments, $yShipments);
            $this->y -= 15;
        }
    }

    /**
     * Insert store address to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @return void
     */
    protected function insertStoreAddress(&$page)
    {
        $storeData = $this->information->getStoreInformationObject($this->_storeManager->getStore());
        $storeAddressArray = [];
        $storeAddressArray[] = $storeData->getName();
        $storeAddressArray[] = $storeData->getStreetLine1();
        if(!empty($storeData->getStreetLine2())){
            $storeAddressArray[] = $storeData->getStreetLine2();
        }
        $storeAddressArray[] = $storeData->getCity().', '.$storeData->getRegion().', '.$storeData->getPostcode();
        $storeAddressArray[] = $storeData->getCountry();
        $storeAddressArray[] = $storeData->getPhone();
        if(!empty($storeData->getVatNumber())){
            $storeAddressArray[] = __('VAT Number').': '.$storeData->getVatNumber();
        }

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0);
        $page->drawRectangle(25, 760, 275, 735);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));

        $this->_setFontRegular($page, 10);
        $this->_setFontBold($page, 12);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Marketplace'), 35, 743, 'UTF-8');

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 720;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->drawRectangle(25, 735, 275, 655);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        foreach ($storeAddressArray as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(ltrim($_value)), 35, $top, 'UTF-8');
                    $top -= 15;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 820;
                //top border of the page
                $widthLimit = 140;
                //half of the page width
                $heightLimit = 140;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 425;
                $x2 = $x1 + $width;

                
                $page->setFillColor(new \Zend_Pdf_Color_Html('#eb0028'));
                if($this->_storeManager->getStore()->getCode() == "en") {
                    $this->_setFontBold($page, 65);
                    $customTop = $top - 45;
                } else {
                    $this->_setFontBold($page, 50);
                    $customTop = $top - 32;
                }
                $page->drawText(__('Hello'), 25, $customTop, 'UTF-8');
                $this->_setFontBold($page, 18);
                $page->drawText(__('This is your invoice'), 25, $top - 65, 'UTF-8');

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->_setFontBold($page, 10);

                /*Set tamata warehouse address*/
                $storeData = $this->information->getStoreInformationObject($this->_storeManager->getStore('admin'));
                $storeAddressArray = [];
                $storeAddressArray[] = $storeData->getName();
                $storeAddressArray[] = $storeData->getData('city').", ".$storeData->getData('street_line1');
                /*if(!empty($storeData->getData('street_line2'))){
                    $storeAddressArray[] = $storeData->getData('street_line2');
                }*/
                if ($storeData->getData("country_id") == "IQ") {
                    $countryName = "Iraq";
                } else {
                    $countryName = $storeData->getData("country");
                }
                $storeAddressArray[] = $countryName;
                $storeAddressArray[] = $storeData->getData('phone');
                /*if(!empty($storeData->getData())){
                    $storeAddressArray[] = __('VAT Number').': '.$storeData->getVatNumber();
                }*/
                foreach ($storeAddressArray as $value) {
                    if ($value !== '') {
                        $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                        foreach ($this->string->split($value, 45, true, true) as $_value) {
                            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
                            $this->_setFontRegular($page, 11);
                            $page->drawText(strip_tags(ltrim($_value)), 425, $y1 - 20, 'UTF-8');
                            $y1 -= 16;
                        }
                    }
                }

                $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0)); //black
                $page->setLineWidth(0.5);
                $page->drawLine(25, $y1 - 17, $x2, $y2-116);
                $this->y = $y1 - 30;
            }
        }
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
                //$this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());

            /* Add address */
            //$this->insertVendorAddress($page, $invoice->getStore(), $invoice->getVendorId());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                ),
                $invoice
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

            $vendor = $this->getVendorDetail($invoice->getVendorId());
            if(!empty($vendor)){
                $vendorName = '';
                if(!empty($vendor->getBusinessName())) {
                    $vendorName = $vendor->getBusinessName();
                }
                $this->insertVendorName($page, __('Sold By : ') .$vendorName);
                $this->insertVendorAddress($page, __('Address : ') .$this->getFullAddress($invoice->getVendorId()));
                $this->insertVendorVat($page, __('VAT : ') .$vendor->getVat());

                if(!empty($this->getVendorGstin($invoice->getVendorId()))) {
                    $this->insertVendorGstin($page, __('GSTIN : ') . $this->getVendorGstin($invoice->getVendorId()));
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
        $this->insertFooterLogo($page, $invoice->getStore());
        return $pdf;
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function insertFooterLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 80;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $bottom = 30;
                //top border of the page
                $widthLimit = 80;
                //half of the page width
                $heightLimit = 80;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $bottom - $height;
                $y2 = $bottom;
                $x1 = 485;
                $x2 = $x1 + $width;

                $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0)); //black
                $page->setLineWidth(0.5);
                $page->drawLine(25, $bottom + 15, $x2+5, $bottom + 15);

                $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $baseUrl = parse_url($baseUrl);
                $baseUrl = $baseUrl['host'];

                $this->_setFontBold($page, 12);
                $page->setFillColor(new \Zend_Pdf_Color_Html('#fa0028'));
                $page->drawText(__($baseUrl), 25, $bottom - 5 , 'UTF-8');

                $this->_setFontRegular($page, 10);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__("Thank you for shopping with us!"), 350, $bottom - 5 , 'UTF-8');

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1 + 5, $y1 + 8, $x2 + 5, $y2 + 8);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Item'), 'feed' => 35];

        //$lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];
        $lines[0][] = ['text' => __('Description'), 'feed' => 190, 'align' => 'left'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 375, 'align' => 'right'];

        $lines[0][] = ['text' => __('Unit Cost'), 'feed' => 450, 'align' => 'right'];

        //$lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Total'), 'feed' => 540, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $source)
    {
        $order = $source->getOrder();
        $totals = $this->_getTotalsList();
        $lineBlock = ['lines' => [], 'height' => 15];
        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);

            if ($total->canDisplay()) {
                $total->setFontSize(10);
                foreach ($total->getTotalsForDisplay() as $totalData) {

                    if (strpos(strtolower($totalData['label']), 'vat') !== false) {
                        $taxClassAmount = $this->taxHelper->getCalculatedTaxes($source);
                        foreach ($taxClassAmount as &$tax) {
                            $percent = $tax['percent'] ? ' (' . $tax['percent'] . '%)' : '';
                            $totalData['amount'] = $order->formatPriceTxt($tax['tax_amount']);
                            $totalData['label'] = __($tax['title']) . $percent . ':';
                        }
                    }

                    $lineBlock['lines'][] = [
                        [
                            'text' => $totalData['label'],
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => $this->_setFontBold($page, 12)
                        ],
                        [
                            'text' => $totalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => $this->_setFontBold($page, 12)
                        ],
                    ];

                    /*...Start to add discount of order in total section..*/
                    if($totalData['label'] == "Shipping & Handling:") {
                        $totalData['label'] = "Shipping Fee:";
                        /*$currencyCode = $source->getorderCurrencyCode();
                        $discountAmount = $source->getdiscountAmount();
                        $discountAmount = round($discountAmount, 2);
                        $lineBlock['lines'][] = [
                            [
                                'text' => "Discount",
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => $currencyCode." ".$discountAmount,
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];*/
                    }
                    /*...End to add discount of order in total section...*/
                }
            }
        }
        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        return $page;
    }

    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/design/frontend/Ktpl/tamata/web/fonts/cairo/light/cairo-300.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/design/frontend/Ktpl/tamata/web/fonts/cairo/bold/cairo-700.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
}
