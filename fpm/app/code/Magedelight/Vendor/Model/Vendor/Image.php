<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\Vendor;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image
{
   /**
    * media sub folder
    * @var string
    */
    protected $subDir = '';

    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir;
    }

    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($path = '')
    {
        if ($path != '') {
            return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($path);
        }
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($this->subDir);
    }
    
    public function getUrl($file)
    {
        if (!$this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->isExist($this->subDir.$file)) {
            return false;
        }
        return $this->getBaseUrl().$file;
    }
    
    public function setDestinationSubdir($subDir = '')
    {
        $this->subDir = $subDir;
        return $this;
    }
    public function getDestinationSubdir()
    {
        return $this->subDir;
    }
}
