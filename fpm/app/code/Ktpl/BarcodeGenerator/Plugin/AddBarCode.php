<?php

namespace Ktpl\BarcodeGenerator\Plugin;

use Closure;
use Ktpl\BarcodeGenerator\Helper\Data;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Zend_Pdf_Image;
use Zend_Pdf_Page;

class AddBarCode
{
    /**
     * @var Data
     */
    private $barcodeHelper;

    /**
     * AddBarCode constructor.
     * @param Data $barcodeHelper
     */
    public function __construct(
        Data $barcodeHelper
    ) {
        $this->barcodeHelper = $barcodeHelper;
    }

    public function aroundDrawLineBlocks(
        AbstractPdf $subject,
        Closure $proceed,
        Zend_Pdf_Page $page,
        array $draw,
        array $pageSettings = []
    ) {
        $page = $proceed($page, $draw, $pageSettings);
        foreach ($draw as $itemsProp) {
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                $maxWidthBarcode = min($this->barcodeHelper->getBarcodeMaxWidth(), 200);
                foreach ($line as $column) {
                    if (!empty($column['image'])) {
                        $feed = $column['feed'];
                        $image_barcode = Zend_Pdf_Image::imageWithPath($column['image']);
                       /* $page->drawImage(
                            $image_barcode,
                            $feed,
                            $subject->y + 35 - $this->barcodeHelper->getBarcodeHeight(),
                            $feed + $maxWidthBarcode,
                            ($subject->y + 35)
                        );
                        $this->barcodeHelper->deleteFile($column['image']);*/
                    }
                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $subject->y -= $maxHeight;
            }
        }
        return $page;
    }
}
