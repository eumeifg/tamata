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
namespace Magedelight\Sales\Api\Data;

/**
 * @api
 */
interface FileDownloadInterface
{

    const STATUS = 'status';
    const FILE_PATH = 'file_path';

    /**
     * Get PDF generation status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set PDF generation status.
     *
     * @param string $text
     * @return $this
     */
    public function setStatus($text);

    /**
     * Get PDF generated URL
     * @return string
     */
    public function getFilePath();

    /**
     * Set PDF generated URL
     * @param string|null $path
     * @return $this
     */
    public function setFilePath($path);
}
