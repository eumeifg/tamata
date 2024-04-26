<?php

namespace Ktpl\BarcodeGenerator\Model\Order\Pdf\Items\Shipment;

use Exception;
use Ktpl\BarcodeGenerator\Helper\Data;
use Ktpl\Tookan\Model\ResourceModel\OrderExport\Grid\Collection;
use Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Item\Renderer\DefaultRenderer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\StringUtils;
use Psr\Log\LoggerInterface;

class DefaultShipment extends \Magento\Sales\Model\Order\Pdf\Items\Shipment\DefaultShipment
{
    /**
     * @var Data
     */
    private $barcodeGenerator;
    /**
     * @var DefaultRenderer
     */
    private $vendorOrderRenderer;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * DefaultShipment constructor.
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param Filesystem $filesystem
     * @param FilterManager $filterManager
     * @param StringUtils $string
     * @param Data $barcodeGenerator
     * @param DefaultRenderer $vendorOrderRenderer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        StringUtils $string,
        Data $barcodeGenerator,
        DefaultRenderer $vendorOrderRenderer,
        LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        \Ktpl\BarcodeGenerator\Helper\convertToArabic $arabicTextHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $string,
            $resource,
            $resourceCollection,
            $data
        );
        $this->barcodeGenerator = $barcodeGenerator;
        $this->vendorOrderRenderer = $vendorOrderRenderer;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->arabicTextHelper = $arabicTextHelper;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $order = $this->getOrder();
        $vendorId = '';
        foreach ($order->getAllItems() as $orderItem) {
            $vendorId = $orderItem->getVendorId();
            break;
        }
        $lines = [];

        // draw Product name
        $productname = $item->getName();
        if ($this->arabicTextHelper->is_arabic($productname)) {
            $newProductname = $this->arabicTextHelper->convertArabicRightToLeft($productname);
        } else {
            $newProductname = $productname;
        }
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($newProductname), 20, true, true),
                'feed' => 50
            ]
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 25];

        // Get Required details to generate barcode
        //$subOrderId = $this->getSubOrderId($order, $vendorId);
        /* MAGE0913 */
        //$subOrderId = Collection::ORDER_ID_PREFIX . $item->getShipment()->getIncrementId();
        $subOrderId = $item->getShipment()->getIncrementId();
        /* MAGE0913 */
        $item->setVendorId($vendorId);
        $itemVendorSku = $this->getItemVendorSku($item);
        $itemMarketplaceSku = $this->getItemMarketplaceSku($item);
        $qty = $this->getItemQty($item);

        try {
            // Create barcode image
            $imageData = $this->barcodeGenerator
                ->generateBarcode($subOrderId);
            // Store barcode image to root folder with name as sub order id.
            // this image will be removed once the pdf is generated.
            $barcodeImage = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath("$subOrderId.png");
            $this->barcodeGenerator->createImage($barcodeImage, $imageData);
            // draw Barcode
            $lines[0][] = [
                'image' => $barcodeImage,
                'text' => '',
                'feed' => 250,
                'align' => 'right',
            ];
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 20),
            'feed' => 265,
            'align' => 'right',
        ];

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            $i = 1;
            foreach ($options as $option) {
                // draw options label
                $lines[$i][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 50,
                ];

                // draw options value
                if ($option['value'] !== null) {
                    $printValue = isset(
                        $option['print_value']
                    ) ? $option['print_value'] : $this->filterManager->stripTags(
                        $option['value']
                    );
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[$i][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 80];
                    }
                }
                $i++;
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }

    public function getItemVendorSku($item)
    {
        return $this->vendorOrderRenderer->getVendorProductSku($item);
    }

    public function getItemMarketplaceSku($item)
    {
        return $item->getSku();
    }

    public function getItemQty($item)
    {
        return $item->getQty();
    }

    public function getSubOrderId($order, $vendorId)
    {
        return $order->getIncrementId() . "-" . $vendorId;
    }
}
