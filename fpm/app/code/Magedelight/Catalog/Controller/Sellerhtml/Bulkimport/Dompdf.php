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
namespace Magedelight\Catalog\Controller\Sellerhtml\Bulkimport;

use Dompdf\Dompdf as PDF;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

class Dompdf extends AbstractResult
{
    public $pdf;

    protected $fileName = 'catalogue_upload_guidelines.pdf';

    protected $attachment = 'attachment';

    protected $output;

    /**
     *
     * @param PDF $domPdf
     */
    public function __construct(PDF $domPdf)
    {
        $this->pdf = $domPdf;
    }

    /**
     * Load html
     *
     * @param $html
     */
    public function setData($html)
    {
        $this->pdf->loadHtml($html);
    }

    /**
     * Set output from $this->renderOutput() to allow multiple renders
     *
     * @param $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Set filename
     *
     * @param $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Set attachment type, either 'attachment' or 'inline'
     *
     * @param $mode
     */
    public function setAttachment($mode)
    {
        $this->attachment = $mode;
    }

    /**
     * Render PDF output
     *
     * @return string
     */
    public function renderOutput()
    {
        if ($this->output) {
            return $this->output;
        }

        $this->pdf->render();

        return $this->pdf->output();
    }

    /**
     * Render PDF
     *
     * @param ResponseInterface $response
     *
     * @return $this
     */
    protected function render(HttpResponseInterface $response)
    {
        $output = $this->renderOutput();
        $response->setHeader('Cache-Control', 'private');
        $response->setHeader('Content-type', 'application/pdf');
        $response->setHeader('Content-Length', mb_strlen($output, '8bit'));

        $filename = $this->fileName;
        $filename = str_replace(["\n", "'"], '', basename($filename, '.pdf')) . '.pdf';

        $encoding                = mb_detect_encoding($filename);
        $fallbackfilename        = mb_convert_encoding($filename, "UTF-8", $encoding);
        $encodedfallbackfilename = rawurlencode($fallbackfilename);
        $encodedfilename         = rawurlencode($filename);

        $response->setHeader(
            'Content-Disposition',
            $this->attachment . '; filename=' . $encodedfallbackfilename . "; filename*=UTF-8''" . $encodedfilename
        );

        $response->setBody($output);

        return $this;
    }
}
