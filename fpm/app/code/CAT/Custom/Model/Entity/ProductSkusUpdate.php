<?php

namespace CAT\Custom\Model\Entity;

use CAT\Custom\Helper\Automation;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use CAT\Custom\Model\Source\Option;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ProductSkusUpdate
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Csv $csv
     * @param DateTime $date
     * @param Filesystem $filesystem
     * @param Automation $automationHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Csv $csv,
        DateTime $date,
        Filesystem $filesystem,
        Automation $automationHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->csv = $csv;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->automationHelper = $automationHelper;
        $this->productRepository = $productRepository;
    }

    public function productSkusUpdate() {
        $isEnabled = $this->automationHelper->getEntityAutomationEnable(Option::PRODUCT_SKU_KEYWORD);
        if ($isEnabled) {
            $automationModel = $this->automationHelper->checkIfSheetAvailable(Option::PRODUCT_SKU_KEYWORD);
            if ($automationModel) {
                $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath('cat/'.Option::PRODUCT_SKU_KEYWORD.'/').$automationModel->getFileName();

                $batchCounter = $automationModel->getBatchCounter();
                $csvData = $this->csv->getData($filePath);

                unset($csvData[0]); // removing header

                $batchLimit = (int)$this->automationHelper->getSkuBatchLimit();

                $fileCounter = count($csvData);
                $remainingCount = $fileCounter - (int)$batchCounter;
                $limit = ($remainingCount > $batchLimit) ? $batchLimit : $remainingCount;
                for ($i= ($batchCounter + 1); $i <= ($batchCounter + $limit); $i++) {
                    $oldSku = $csvData[$i][0];
                    $newSku = $csvData[$i][1];

                    if (!empty($oldSku) && !empty($newSku)) {
                        try {
                            $_product = $this->productRepository->get($oldSku, true, null, true);
                            $_product->setSku($newSku)->save($_product);
                        } catch (NoSuchEntityException $e) {
                            $logger = $this->automationHelper->getLogger(Option::PRODUCT_SKU_KEYWORD);
                            $logger->info(__('OLD SKU Entity Issue : %1', $oldSku));
                        } catch (\Exception $e) {
                            $logger = $this->automationHelper->getLogger(Option::PRODUCT_SKU_KEYWORD);
                            $logger->info(__('Old Sku : %1 & New Sku', [$oldSku, $newSku]));
                        }
                    }
                }
                /* Updating the process data */
                if (($batchCounter + $limit) == $fileCounter) {
                    $processedTime = $this->_date->gmtDate('Y-m-d H:i:s');
                    $automationModel->setStatus(1);
                    $automationModel->setProcessedAt($processedTime);
                }
                $automationModel->setBatchCounter($batchCounter + $limit);
                $automationModel->save();
                /* Updating the process data */
            }
        }
    }
}
