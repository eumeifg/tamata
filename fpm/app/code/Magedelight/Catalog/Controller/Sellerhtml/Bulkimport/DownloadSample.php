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

use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadSample extends \Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\Category
{
    public function execute()
    {
        $vendor_id = $this->_auth->getUser()->getVendorId();
        $cid = $this->getRequest()->getParam('cid');
        $categoryName = $this->getRequest()->getParam('name');
        $excludeAttributes = $this->getExcludeAttributeList();

        if ($cid) {
            $this->_initCategory($cid);

            if ($this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId() === null) {
                $this->coreRegistry->registry('vendor_current_category')
                    ->setMdCategoryAttributeSetId($this->_getCategoryDefaultAttributeSetId());
            }

            /* Get category specific attributes.*/
            $attributes = $this->getAttributesByAttributeSet();

            $systemExcludedAttributes = $this->_bulkimportHelper->getSystemExcludedAttributes();
            foreach ($systemExcludedAttributes as $code) {
                $excludeAttributes[] = $this->getAttriButeIdByCode($code);
            }

            $customExcludedAttributes = $this->_bulkimportHelper->getCustomExcludedAttributes();
            foreach ($customExcludedAttributes as $code) {
                $excludeAttributes[] = $this->getAttriButeIdByCode($code);
            }

            /* Set category attributes.*/
            if ($attributes) {
                foreach ($attributes as $key => $attribute) {
                    if (in_array($key, $excludeAttributes)) {
                        unset($attributes[$key]);
                    }
                }
            }

            /* Create a sample csv file.*/
            $fileNameExplode = preg_replace("/[&]/", "_", $categoryName);
            $fileName = preg_replace("/[^a-zA-Z0-_9.]/", "", $fileNameExplode);

            /*Set headers in a sample csv file. */

            $headersArr = $this->_bulkimportHelper->getExtraAttributeHeaders(
                $this->_bulkimportHelper->getCSVHeaders(),
                $attributes
            );

            $headers = new \Magento\Framework\DataObject(
                $headersArr
            );

            $template = $this->_bulkimportHelper->getCSVTemplateText($headersArr);
            $content = $headers->toString($template);
            $content .= "\n";

            /* Set sample row in a sample csv file.*/
            $sampleRowArr = $this->_bulkimportHelper->getSampleRow($cid);

            foreach ($headersArr as $key => $value) {
                if (!array_key_exists($key, $sampleRowArr)) {
                    $sampleRowArr[$key] = '';
                }
            }

            $sampleData = new \Magento\Framework\DataObject(
                $sampleRowArr
            );

            $content .= $sampleData->toString($template);
            return $this->fileFactory->create($fileName . ".csv", $content, DirectoryList::VAR_DIR);
        }
    }

    /**
     * Vendor access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::manage_products');
    }
}
