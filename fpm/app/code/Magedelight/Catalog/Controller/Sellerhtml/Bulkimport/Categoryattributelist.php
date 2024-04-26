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

/**
 * Product Select Category
 */
class Categoryattributelist extends \Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\Category
{

    /**
     * @var \Magedelight\Catalog\Block\Sellerhtml\Bulkimport\Attributeselector $attributeselector
     */
    protected $attributeselector;

    /**
     * @var \Magedelight\Catalog\Block\Bulkimport\DompdfFactory $dompdfFactory
     */
    protected $dompdfFactory;

    /**
     * Vendor product landing page
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function execute()
    {
        $cid = $this->getRequest()->get('cid');
        $tab = $this->getRequest()->getParam('tab');
        if (!$cid) {
            $this->messageManager->addError(__("Category data does not exist"));
            $this->_redirect('*/*/import/tab/' . $tab);
        }
        $multipleCategoryIdsArray = explode(",", $cid);
        $categoryIdsRemovedEmpty = array_filter($multipleCategoryIdsArray);
        $categoryIdsRemovedDuplicate = array_unique($categoryIdsRemovedEmpty);
        $excludeAttributes = $this->getExcludeAttributeList();
        $systemExcludedAttributes = $this->_bulkimportHelper->getSystemExcludedAttributes();
        foreach ($systemExcludedAttributes as $code) {
            $excludeAttributes[] = $this->getAttriButeIdByCode($code);
        }

        $customExcludedAttributes = $this->_bulkimportHelper->getCustomExcludedAttributes();
        foreach ($customExcludedAttributes as $code) {
            $excludeAttributes[] = $this->getAttriButeIdByCode($code);
        }

        $attId = [];
        foreach ($categoryIdsRemovedDuplicate as $categoryId) {
            $this->_initCategory($categoryId);
            if ($this->coreRegistry->registry('vendor_current_category')->getMdCategoryAttributeSetId() === null) {
                $this->coreRegistry->registry('vendor_current_category')
                    ->setRbCategoryAttributeSetId($this->_getCategoryDefaultAttributeSetId());
            }
            $attributesData = $this->getAttributesByAttributeSet();
            /* Set category attributes.*/
            foreach ($attributesData as $key => $value) {
                if (in_array($key, $excludeAttributes)) {
                    unset($attributesData[$key]);
                }
            }
            $attId[$categoryId] = $attributesData;
        }

        if (count($attId) === 0) {
            $this->messageManager->addError(__("Category data does not exist"));
            $this->_redirect('*/*/import/tab/' . $tab);
        }
        $html = $this->getAttributeSelector()->setData('attribulteData', $attId)
            ->setTemplate("Magedelight_Catalog::bulkimport/attribute_optionsvalue.phtml")->toHtml();
        $result['html'] = preg_replace('/>\s+</', "><", $html);
        $fileName = 'Category_Attributes.pdf';
        if (count($categoryIdsRemovedDuplicate) === 1) {
            $fileName = $this->_category->getName($categoryIdsRemovedDuplicate) . '_Attributes';
        }
        $response = $this->getDompdf();
        $response->setFileName($fileName);
        $response->setData($result['html']);
        return $response;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated
     */
    protected function getAttributeSelector()
    {
        if ($this->attributeselector === null) {
            $this->attributeselector = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magedelight\Catalog\Block\Sellerhtml\Bulkimport\Attributeselector::class);
        }
        return $this->attributeselector;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @deprecated
     */
    protected function getDompdf()
    {
        if ($this->dompdfFactory === null) {
            $this->dompdfFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magedelight\Catalog\Controller\Sellerhtml\Bulkimport\DompdfFactory::class)->create();
        }
        return $this->dompdfFactory;
    }
}
