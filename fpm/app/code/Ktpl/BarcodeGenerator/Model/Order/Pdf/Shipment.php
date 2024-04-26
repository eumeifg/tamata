<?php

namespace Ktpl\BarcodeGenerator\Model\Order\Pdf;

use Magedelight\Backend\Model\Auth\Session;
use Magedelight\Vendor\Model\VendorFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Color_Rgb;
use Zend_Pdf_Page;
use Ktpl\BarcodeGenerator\Helper\Data as barcodeGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend_Pdf_Image;
use Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid\Collection;
use Magedelight\Backend\App\Area\FrontNameResolver;

class Shipment extends \MDC\Sales\Model\Sales\Order\Pdf\Shipment
{

    //const SIZE_A4                       = '595:842:';
    const SIZE_CUSTOM_PACKAGING_SLIP    = '283:340:';
    //const SIZE_CUSTOM_PACKAGING_SLIP  = '595:842:';

    /**
     * @var Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Ktpl\Tookan\Helper\Data
     */
    private $tookanHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Shipment constructor.
     * @param Data $paymentData
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Config $pdfConfig
     * @param Factory $pdfTotalFactory
     * @param ItemsFactory $pdfItemsFactory
     * @param TimezoneInterface $localeDate
     * @param StateInterface $inlineTranslation
     * @param Renderer $addressRenderer
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Session $authSession
     * @param CountryFactory $countryFactory
     * @param VendorFactory $vendorFactory
     * @param Registry $registry
     * @param State $state
     * @param \Ktpl\Tookan\Helper\Data $tookanHelper
     * @param barcodeGenerator $barcodeGenerator
     * @param \Ktpl\BarcodeGenerator\Helper\convertToArabic $arabicTextHelper
     * @param array $data
     */
    public function __construct(
        Data $paymentData,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Config $pdfConfig,
        Factory $pdfTotalFactory,
        ItemsFactory $pdfItemsFactory,
        TimezoneInterface $localeDate,
        StateInterface $inlineTranslation,
        Renderer $addressRenderer,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        \Magedelight\Sales\Api\OrderRepositoryInterface $orderRepository,
        Session $authSession,
        CountryFactory $countryFactory,
        VendorFactory $vendorFactory,
        Registry $registry,
        State $state,
        \Ktpl\Tookan\Helper\Data $tookanHelper,
        barcodeGenerator $barcodeGenerator,
        \Ktpl\BarcodeGenerator\Helper\convertToArabic $arabicTextHelper,
        array $data = []
    )
    {
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver, $orderRepository, $authSession, $countryFactory, $vendorFactory, $registry, $state, $tookanHelper, $data);
        $this->state = $state;
        $this->tookanHelper = $tookanHelper;
        $this->filesystem = $filesystem;
        $this->barcodeGenerator = $barcodeGenerator;
        $this->arabicTextHelper = $arabicTextHelper;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Insert address to pdf page
     *
     * @param Zend_Pdf_Page &$page
     * @param null $store
     * @return void
     */
    protected function insertAddress(&$page, $store = null)
    {
        $vendorAddressArray = array();
        //$y2 = 745;
        $y2 = 340;
        if ($this->state->getAreaCode() != Area::AREA_ADMINHTML) {
            if ($this->authSession->getUser()) {
                $vendorDataArray = $this->authSession->getUser()->getData();
            }

            if (empty($vendorDataArray)) {
                $vendor = $this->_vendorFactory->create()->load($this->_vendorId);
                $vendorDataArray = $vendor->getData();
            }

            $vendorAddressArray[] = $vendorDataArray['business_name'];
            $countryName = $this->getCountryname($vendorDataArray['country_id']);
            $vendorAddressArray[] = $vendorDataArray['address1'] . ", " .$vendorDataArray['address2'];
            $vendorAddressArray[] = $vendorDataArray['city'] . ',  ' . $vendorDataArray['region'];
            if (array_key_exists('pincode', $vendorAddressArray)) {
                $vendorAddressArray[] = $vendorDataArray['pincode'] . ' - ' . $countryName;
            } else {
                $vendorAddressArray[] = $countryName;
            }
        } else {
            $y2 = $y2 - 10;
            $vendorAddressArray = $this->getStoreAddress();
        }

    }

    public function getCountryname($countryCode)
    {
        if (!$countryCode || $countryCode == '') return '';
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $this->_vendorId = $shipment->getVendorId();
            $page = $this->newPage();

            $order = $shipment->getOrder();
            $vendorOrder = $this->orderRepository->getById($shipment->getVendorOrderId());

            $this->_setCustomHeader($page, $shipment, $shipment->getStore());


            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);

            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }

            /* Add address */
            /* Add head */
            $shipment->setVendorOrder($vendorOrder);
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );

            // $this->insertAddress($page, $shipment->getStore());
            //$this->customAddress($page, $shipment->getStore());

               /* Add image */
            $this->insertLogo($page, $shipment->getStore());
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            $this->_localeResolver->revert();
        }
        return $pdf;
    }

    /*public function customAddress(&$page, $store = null)
    {}*/

    public function _setCustomHeader(&$page, $shipment, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $this->_setFontRegular($page, 25);
        $this->_setFontBold($page, 24);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Hello'), 15, $top - 30, 'UTF-8');

        $this->_setFontRegular($page, 15);
        $this->_setFontBold($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("I'm your receipt"), 15, $top - 40, 'UTF-8');

        $this->_setFontRegular($page, 8);
        //$this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("Thank you for shopping with Tamata"), 15, $top - 55, 'UTF-8');


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($shipment->getOrder()->getIncrementId());

        //$customerName = $order->getCustomerName();
        $customerName = $order->getShippingAddress()->getName();
        if ($this->arabicTextHelper->is_arabic($customerName)) {
            $orgText = $this->arabicTextHelper->convertArabicRightToLeft($customerName);
            $orgCustomerName = $orgText;
        } else {
            $orgCustomerName = $order->getCustomerName();
        }
        //echo $orgCustomerName;die;
        $this->_setFontRegular($page, 15);
        $this->_setFontBold($page, 9);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText($orgCustomerName, 15, $top -= 65, 'UTF-8');

        $this->_setFontRegular($page, 8);
        //$this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("Shipment ID #"), 15, $top -= 15, 'UTF-8');


        $this->_setFontRegular($page, 8);
        //$this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("Order Date"), 120, $top , 'UTF-8');

        $this->_setFontRegular($page, 8);
        //$this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText($shipment->getIncrementId(), 15, $top -= 10, 'UTF-8');

        $this->_setFontRegular($page, 8);
        //$this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $date = strtotime($order->getCreatedAt());
        $finalOrderDate = date("d-m-Y",$date);
        $page->drawText($finalOrderDate, 120, $top, 'UTF-8');

        $this->_setFontRegular($page, 15);
        $this->_setFontBold($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("Item/s"), 15, $top -= 15, 'UTF-8');

        /*....Start to draw barcode on shipment level....*/
        if ($shipment) {

            $orderIncrementId = $shipment->getOrder()->getIncrementId();

            /*....Barcode number format prints from here....*/
            $subOrderId = Collection::ORDER_ID_PREFIX .$shipment->getIncrementId()."|".$orderIncrementId."-".$shipment->getVendorOrderId();

            $shipmentIncrementId = $shipment->getIncrementId();
            $code = [];
            $code['shipment_id'] = $shipmentIncrementId;
            $code['suborder_id'] = $subOrderId;
            $imageData = $this->barcodeGenerator->generateBarcode($code);
            $barcodeImage = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath("$subOrderId.png");
            $feed = 120;
            $maxHeight = 0;
            $maxWidthBarcode = min($this->barcodeGenerator->getBarcodeMaxWidth(), 200);
            $this->barcodeGenerator->createImage($barcodeImage, $imageData);
                //$image_barcode = Zend_Pdf_Image::imageWithPath($barcodeImage);
                $page->drawImage(
                    Zend_Pdf_Image::imageWithPath($barcodeImage),
                    $feed,
                    $this->y - 15 - $this->barcodeGenerator->getBarcodeHeight(),
                    $feed + $maxWidthBarcode,
                    ($this->y - 15)
                );
        }
        $this->y = $top;
        /*....End to draw barcode on shipment level....*/
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
                $x1 = 190;
                $x2 = $x1 + $width;

                $this->_setFontRegular($page, 9);
                //$this->_setFontBold($page, 8);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $page->drawText(__("This shipment completes your order"), 15, $bottom - 15 , 'UTF-8');

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
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
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }
        $vendorOrder = $obj->getVendorOrder();
         /*lineitem block*/

        //$this->y = $this->y;// ? 700 : 815;
        //$this->y = $this->y ? 260 : 335;
        //$this->y = 140;
        $top = $this->y + 10;
        if ($this->y - 30 < 15) {
            $top = $this->y + 250;
            $page = $this->newPage();
        }

        /* Billing Address */
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));
        } else {
            $billingAddress = $this->getStoreAddress();
        }

        /* Shipping Address and Method */
            /* Shipping Address */
        if (!$order->getIsVirtual()) {
            if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            } else {
                $shippingAddress = $this->getStoreAddress();
            }
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        //$this->_setFontBold($page, 12);
        //$page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        /*if (!$order->getIsVirtual()) {*/
            $this->_setFontBold($page, 9);
        if ($this->state->getAreaCode() != FrontNameResolver::AREA_CODE) {
            $page->drawText(__('Shipping Method:'), 150, $top - 50, 'UTF-8');
        }
        $page->drawText(__('Shipping address:'), 15, $top - 50, 'UTF-8');
        /*} else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }*/

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        //$page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        //$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y = $top - 52;
        $addressesStartY = $this->y;


        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;

            /*..Start check for arabic text if arabic it change text direction from right to left..*/
            foreach($shippingAddress as $key => $address){
                if ($this->arabicTextHelper->is_arabic($shippingAddress[$key])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[$key]);
                    $shippingAddress[$key] = $orgText;
                } else {
                    $shippingAddress[$key] = $shippingAddress[$key];
                }
            }

            /*if(isset($shippingAddress[0])) {
                if ($this->arabicTextHelper->is_arabic($shippingAddress[0])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[0]);
                    $shippingAddress[0] = $orgText;
                } else {
                    $shippingAddress[0] = $shippingAddress[0];
                }
            }
            if(isset($shippingAddress[1])) {
                if ($this->arabicTextHelper->is_arabic($shippingAddress[1])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[1]);
                    $shippingAddress[1] = $orgText;
                } else {
                    $shippingAddress[1] = $shippingAddress[1];
                }
            }
            if(isset($shippingAddress[2])) {
                if ($this->arabicTextHelper->is_arabic($shippingAddress[2])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[2]);
                    $shippingAddress[2] = $orgText;
                } else {
                    $shippingAddress[2] = $shippingAddress[2];
                }
            }
            if(isset($shippingAddress[3])) {
                if ($this->arabicTextHelper->is_arabic($shippingAddress[3])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[3]);
                    $shippingAddress[3] = $orgText;
                } else {
                    $shippingAddress[3] = $shippingAddress[3];
                }
            }
            if(isset($shippingAddress[4])) {
                if ($this->arabicTextHelper->is_arabic($shippingAddress[4])) {
                    $orgText = $this->arabicTextHelper->convertArabicRightToLeft($shippingAddress[4]);
                    $shippingAddress[4] = $orgText;
                } else {
                    $shippingAddress[4] = $shippingAddress[4];
                }
            }*/
            /*....end check for arabic text if arabic it change text direction from right to left....*/

            if (!in_array($this->state->getAreaCode(), [FrontNameResolver::AREA_CODE, Area::AREA_WEBAPI_REST])) {
                $page->drawText($order->getShippingDescription(), 150, $top - 70, 'UTF-8');
                $totalShippingChargesText = "("
                    . __('Total Shipping Charges')
                    . " "
                    . $order->formatPriceTxt($order->getShippingAmount())
                    . ")";

                $page->drawText($totalShippingChargesText, 150, $top - 80, 'UTF-8');
            }

            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 26, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        //$this->_setFontBold($page, 10);
                        $page->drawText(strip_tags(ltrim($part)), 15, $this->y - 15, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }



            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }
        $pmethod = $order->getPayment()->getMethodInstance();


        $vendorItemsCollection = $vendorOrder->getItems();
        $allLineItemSubtotal = $discountAmount = 0;
        foreach($vendorItemsCollection as $_items) {
            $allLineItemSubtotal += $_items->getrowTotal();
            $discountAmount += $_items->getDiscountAmount();
        }
        $discountAmount = -$discountAmount;
        $currencyCode = $vendorOrder->getorderCurrencyCode();

        $allLineItemSubtotal = round($allLineItemSubtotal, 2);
        $page->drawText(__("Subtotal"), 160, $top - 7, 'UTF-8');
        $page->drawText($currencyCode." ".$order->formatPriceTxt($allLineItemSubtotal), 215, $top - 7, 'UTF-8');
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $shippingNHandlingCharges = 0;
            $shippingNHandlingCharges = round($shippingNHandlingCharges, 2);
            $discountAmount = round($discountAmount, 2);
            $page->drawText(__("Discount"), 160, $top - 20, 'UTF-8');
            $page->drawText($currencyCode . " " . $order->formatPriceTxt($discountAmount), 215, $top - 20, 'UTF-8');
        } else {
            $discountAmount = $vendorOrder->getdiscountAmount();
            $discountAmount = round($discountAmount, 2);
            $shippingNHandlingCharges = $vendorOrder->getshippingAmount();
            $shippingNHandlingCharges = round($shippingNHandlingCharges, 2);
            $page->drawText(__("Discount"), 160, $top - 20, 'UTF-8');
            $page->drawText($currencyCode . " " . $order->formatPriceTxt($discountAmount), 215, $top - 20, 'UTF-8');
        }
        $totalPaidAmount = $allLineItemSubtotal + $shippingNHandlingCharges + $discountAmount;
        $totalPaidAmount = round($totalPaidAmount, 2);
        $this->_setFontBold($page, 9);
        $page->drawText(__("Total"), 160, $top - 32, 'UTF-8');
        $page->drawText($currencyCode." ".$order->formatPriceTxt($totalPaidAmount), 215, $top - 32, 'UTF-8');

    }


    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 7);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $this->y -= 5;
        $page->drawRectangle(15, $this->y, 270, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 50];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 20];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 250, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        //$this->y -= 10;
    }

    protected function getStoreAddress()
    {
        $vendorAddressArray = [];
        $vendorAddressArray[] = $this->tookanHelper->getStoreName();
        $countryName = $this->getCountryname($this->tookanHelper->getStoreCountry());
        $vendorAddressArray[] = $this->tookanHelper->getStoreStreetAddress();
        $vendorAddressArray[] = $this->tookanHelper->getStoreCity() . ',  ' . $this->tookanHelper->getStoreRegion();
        if ($pincode = $this->tookanHelper->getStorePincode()) {
            $vendorAddressArray[] = $pincode . ' - ' . $countryName;
        } else {
            $vendorAddressArray[] = $countryName;
        }
        return $vendorAddressArray;
    }

    public function newPage(array $settings = [])
    {
        $page = $this->_getPdf()->newPage(self::SIZE_CUSTOM_PACKAGING_SLIP);
        $this->_getPdf()->pages[] = $page;
        /*...shipment slip height...*/
        $this->y = 340;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
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
