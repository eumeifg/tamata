<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class CleanTmpData
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    const XML_PATH_CLEANUP_ENABLED = 'md_bulkimport/cleanup/enabled';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $driver;

    /**
     *
     * @param \Magento\Framework\Filesystem\Io\File $driver
     * @param Filesystem $fileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Filesystem\Io\File $driver,
        Filesystem $fileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->driver = $driver;
        $this->fileSystem = $fileSystem;
        $this->scopeConfig = $scopeConfig;
    }

    public function process()
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_CLEANUP_ENABLED)) {
            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $dir = $mediaDirectory->getAbsolutePath() . 'tmp/catalog/';
            try {
                $result = $this->driver->rmdir($dir, true);
            } catch (\Exception $e) {
                throw $e;
            }

            return $result;
        }
    }
}
