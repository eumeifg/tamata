<?php

namespace MDC\Sales\Model\Sales\Order\Pdf;

use Magedelight\Backend\Model\Auth\Session;
use Magedelight\Sales\Model\OrderFactory;
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

class Shipment extends \Magedelight\Sales\Model\Sales\Order\Pdf\Shipment
{
    /**
     * @var Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Ktpl\Tookan\Helper\Data
     */
    private $tookanHelper;

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
        array $data = []
    )
    {
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver, $orderRepository, $authSession, $countryFactory, $vendorFactory, $registry, $data);
        $this->state = $state;
        $this->tookanHelper = $tookanHelper;
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
        $y2 = 745;
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

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0);
        $page->drawRectangle(275, 838, 570, 813);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));

        $this->_setFontRegular($page, 10);
        $this->_setFontBold($page, 12);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__('Seller:'), 285, 820, 'UTF-8');

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 800;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        if (isset($vendorDataArray['vat'])) {
            $page->drawRectangle(275, 815, 570, $y2);
        } else {
            $page->drawRectangle(275, 815, 570, $y2+10);
        }
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        foreach ($vendorAddressArray as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    public function getCountryname($countryCode)
    {
        if (!$countryCode || $countryCode == '') return '';
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
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
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, 10);
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);
        /* Calculate blocks info */

        /* Billing Address */
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));
        } else {
            $billingAddress = $this->getStoreAddress();
        }

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            } else {
                $shippingAddress = $this->getStoreAddress();
            }
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }
        $pmethod = $order->getPayment()->getMethodInstance();

        /**
         * hack for payment method.
         * @todo Payment methods should be render using payment/info block.
         */
        $page->drawText(strip_tags(trim($pmethod->getTitle())), $paymentLeft, $yPayments, 'UTF-8');
//        foreach ($payment as $value) {
//            if (trim($value) != '') {
//                //Printing "Payment Method" lines
//                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
//                foreach ($this->string->split($value, 45, true, true) as $_value) {
//                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
//                    $yPayments -= 15;
//                }
//            }
//        }

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

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $order->formatPriceTxt(
                    $vendorOrder->getShippingAmount()
                ) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
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
}
