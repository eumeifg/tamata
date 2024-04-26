<?php

namespace Ktpl\BarcodeGenerator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Picqer\Barcode\BarcodeGeneratorPNG as BarcodeGenerator;

class Data extends AbstractHelper
{

    const BARCODE_DELIMITER = "|";

    /**
     * @var BarcodeGenerator
     */
    private $barcodeGenerator;
    /**
     * @var File
     */
    private $file;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param BarcodeGenerator $barcodeGenerator
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        BarcodeGenerator $barcodeGenerator,
        File $file
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->barcodeGenerator = $barcodeGenerator;
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigValue('barcode_generator/general/enable');
    }

    public function getConfigValue(string $path): string
    {
        $value = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            null
        );

        return $value ?? '';
    }

    public function getBarcodeMaxWidth()
    {
        return (int)$this->getConfigValue('barcode_generator/general/max_width');
    }

    public function getBarcodeHeight()
    {
        return (int)$this->getConfigValue('barcode_generator/general/height');
    }

    public function generateBarcode($subOrderId)
    {
        return $this->barcodeGenerator->getBarcode(
            $subOrderId,
            $this->getBarcodeFormat()
        );
    }

    public function getBarcodeFormat()
    {
        return $this->getConfigValue('barcode_generator/general/barcode_format');
    }

    public function deleteFile($filepath)
    {
        if ($this->file->isExists($filepath)) {
            $this->file->deleteFile($filepath);
        }
    }

    public function createImage($path, $content)
    {
        $this->file->filePutContents($path, $content);
    }
}
