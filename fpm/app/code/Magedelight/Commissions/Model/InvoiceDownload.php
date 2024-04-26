<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model;

use \Magedelight\Commissions\Api\Data\InvoiceDownloadInterface;

class InvoiceDownload extends \Magento\Framework\DataObject implements InvoiceDownloadInterface
{

    /**
     * Get PDF generation status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(InvoiceDownloadInterface::STATUS);
    }

    /**
     * Set PDF generation status.
     *
     * @param string $text
     * @return $this
     */
    public function setStatus($text)
    {
        return $this->setData(InvoiceDownloadInterface::STATUS, $text);
    }

    /**
     * Get PDF generated URL
     * @return string
     */
    public function getFilePath()
    {
        return $this->getData(InvoiceDownloadInterface::FILE_PATH);
    }

    /**
     * Set PDF generated URL
     * @param string|null $path
     * @return $this
     */
    public function setFilePath($path)
    {
        return $this->setData(InvoiceDownloadInterface::FILE_PATH, $path);
    }
}
