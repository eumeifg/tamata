<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ktpl\BarcodeGenerator\Model\Order\Pdf\Items\Invoice;

/**
 * Sales Order Invoice Pdf default items renderer
 */
class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;
    private $productRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Ktpl\BarcodeGenerator\Helper\convertToArabic $arabicTextHelper,
        array $data = []
    ) {
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
        $this->string = $string;
        $this->productRepository = $productRepository;
        $this->arabicTextHelper = $arabicTextHelper;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
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
                'text' => $this->string->split(html_entity_decode($newProductname), 30, true, true),
                'feed' => 35
            ]
        ];

        // draw SKU
        /*$lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 17),
            'feed' => 290,
            'align' => 'right',
        ];*/

        // draw Product Description
        $productData = $this->productRepository->get($this->getSku($item));
        //$shortDescription = $this->filterManager->stripTags($productData->getShortDescription());
        $shortDescription = $this->filterManager->stripTags($productData->getShortDescription());
        if ($this->arabicTextHelper->is_arabic($shortDescription)) {
            $newShortDescription = $this->arabicTextHelper->convertArabicRightToLeft($shortDescription);
        } else {
            $newShortDescription = $shortDescription;
        }
        if ($shortDescription) {
            $lines[0][] = [
                'text' => $this->string->split(html_entity_decode($newShortDescription), 32),
                'feed' => 180,
                'align' => 'left'
            ];
        }

        /*$lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($short_description), 17),
            'feed' => 290,
            'align' => 'left',
        ];*/

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 370, 'align' => 'right'];

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 465;
        $feedSubtotal = $feedPrice + 100;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => $feedPrice,
                'font' => 'normal',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'normal',
                'align' => 'right',
            ];
            $i++;
        }

        // draw Tax
        /*$lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];*/

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            $i = 1;
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
                $i++;
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 12];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
