<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model;

use \Magedelight\Sales\Api\Data\FileDownloadInterface;

class FileDownload extends \Magento\Framework\DataObject implements FileDownloadInterface
{

    /**
     * Get PDF generation status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(FileDownloadInterface::STATUS);
    }

    /**
     * Set PDF generation status.
     *
     * @param string $text
     * @return $this
     */
    public function setStatus($text)
    {
        return $this->setData(FileDownloadInterface::STATUS, $text);
    }

    /**
     * Get PDF generated URL
     * @return string
     */
    public function getFilePath()
    {
        return $this->getData(FileDownloadInterface::FILE_PATH);
    }

    /**
     * Set PDF generated URL
     * @param string|null $path
     * @return $this
     */
    public function setFilePath($path)
    {
        return $this->setData(FileDownloadInterface::FILE_PATH, $path);
    }
}
