<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
 
class Blacklist extends \Magento\Framework\Model\AbstractModel
{
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Filesystem $fileSystem,
        Csv $fileCsv,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->fileCsv = $fileCsv;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Abandonedcart\Model\ResourceModel\Blacklist::class);
    }
    
    public function importBlacklist($postData)
    {
        $csvData = $this->getCsvData($postData);
        foreach ($csvData as $key => $data) {
            if ($key > 0) {
                $this->setData($data);
                $this->save();
                $this->unsetData();
            }
        }
    }
    
    private function getCsvData($postData)
    {
        $destinationPath = $this->getDestinationPath();
        $filePath = $destinationPath.current($postData['csvfile'])['file'];
        //@codingStandardsIgnoreStart
        if (!file_exists($filePath)) {
            throw new \Exception('CSV file not found');
            return;
        }
        //@codingStandardsIgnoreEnd
        $csvData = $this->fileCsv->getData($filePath);
        return $this->validateCsvData($csvData, $postData);
    }
    
    private function validateCsvData($csvData, $postData)
    {
        foreach ($csvData as $row) {
            $data[] = [
                'email' => current($row),
                'website_id' => ($row[1] !="" && $row[1] !="0")?$row[1]:"1"
            ];
        }
        return $data;
    }
    
    private function getDestinationPath()
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('/');
    }
}
