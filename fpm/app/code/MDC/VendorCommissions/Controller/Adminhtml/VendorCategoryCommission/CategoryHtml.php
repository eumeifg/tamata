<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   MDC_VendorCommissions
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission;

use Magedelight\Vendor\Api\VendorRepositoryInterface;

class CategoryHtml extends \MDC\VendorCommissions\Controller\Adminhtml\VendorCategoryCommission
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \MDC\VendorCommissions\Model\VendorCategoryCommissionFactory
     */
    protected $vendorCategoryCommissionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \MDC\VendorCommissions\Model\VendorCategoryCommissionFactory $vendorCategoryCommissionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        VendorRepositoryInterface $vendorRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->vendorCategoryCommissionFactory = $vendorCategoryCommissionFactory;
        $this->registry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $resultPageFactory, $resultForwardFactory, $vendorCategoryCommissionFactory, $registry);
    }

    public function execute()
    {
        if($this->getRequest()->isAjax()){
            echo $this->getCategories();
        }
        return false;
    }

    /**
     * Retrieve Categories List
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();
        foreach ($collection as $category) {
            return '<table>'.$this->_getTreeCategories($category, false).'</table>';
        }
    }

    /**
     * @param $parent
     * @param $isChild
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getTreeCategories($parent, $isChild, $prevClass = [])
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();

        $vid = $this->getRequest()->getParam('vendorid');
        // $vid = 387;
        $vendor = $this->vendorRepository->getById($vid);
        $vendorCats = $vendor->getCategory();

        if(!count($vendorCats)) {
            $html .='
                <tr class="data-row"><td colspan="8">No Records Found</td></tr>
            ';

            return $html;
        }

        ($vendorCats === null) ? $vendorCats = [] : $vendorCats;
        $membershipCategories = [];
        $collection->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', ['eq' => $parent->getId()])
            ->addAttributeToFilter('entity_id', ['neq' => $parent->getId()])
            ->addAttributeToSort('position', 'asc')
            ->load();

        $catValues = [];

        $currentlevel = $parent->getLevel() + 1;

        $spaceCat = '';
        if($currentlevel > 3) {
            for($i=1; $i<=($parent->getLevel()-1); $i++){
                $spaceCat .= '-- ';
            }
        }

        $html = '';

        $vendorCategoryCommissionCollection = $this->vendorCategoryCommissionFactory
            ->create()->getCollection()->addFieldToFilter('vendor_id', $vid);

        $catValues["id"] = [];

        foreach($vendorCategoryCommissionCollection as $vendorCat){
            $catValues["id"][$vendorCat->getProductCategory()] = $vendorCat->getVendorCategoryCommissionId();
            $catValues["calculation_type"][$vendorCat->getProductCategory()] = $vendorCat->getCalculationType();
            $catValues["commission_value"][$vendorCat->getProductCategory()] = $vendorCat->getCommissionValue();
            $catValues["marketplace_fee_type"][$vendorCat->getProductCategory()] = $vendorCat->getMarketplaceFeeType();
            $catValues["marketplace_fee"][$vendorCat->getProductCategory()] = $vendorCat->getMarketplaceFee();
            $catValues["cancellation_fee_commission_value"][$vendorCat->getProductCategory()] = $vendorCat->getCancellationFeeCommissionValue();
            $catValues["cancellation_fee_calculation_type"][$vendorCat->getProductCategory()] = $vendorCat->getCancellationFeeCalculationType();
            $catValues["status"][$vendorCat->getProductCategory()] = $vendorCat->getStatus();
        }

        foreach ($collection as $category) {
            if (!$category->hasChildren()) {
                if(in_array($category->getId(), $vendorCats, true)) {
                    $fId = $status = $cfv = $cft = $mft = $mfv = $ct = $cv = '';
                    if(array_key_exists($category->getId(), $catValues["id"])) {
                       $fId = $catValues["id"][$category->getId()];
                       $status = $catValues["status"][$category->getId()] == '2' ? 'selected' : '';
                       $ct = $catValues["calculation_type"][$category->getId()] == '2' ? 'selected' : '';
                       $cv = $catValues["commission_value"][$category->getId()];
                       $mft = $catValues["marketplace_fee_type"][$category->getId()] == '2' ? 'selected' : '';
                       $mfv = $catValues["marketplace_fee"][$category->getId()];
                       $cfv = $catValues["cancellation_fee_commission_value"][$category->getId()];
                       $cft = $catValues["cancellation_fee_calculation_type"][$category->getId()] == '2' ? 'selected' : '';
                    }

                    $cvClass = $prevClass['cv'];
                    $html .='<tr class="data-row">
            <td>'.$spaceCat.$category->getName().'
                <input type="hidden" name="id['.$category->getId().']" value="'.$fId.'" />
            </td>
            <td><input size="6" value="'.$cv.'" name="commission['.$category->getId().']" class="input-text '.$cvClass.'" type="text" /></td>
            <td>
                <select name="calculation_type['.$category->getId().']" class="select admin__control-select">
                    <option value="1">Flat</option>
                    <option '.$ct.' value="2">Percentage</option>
                </select>
            </td>
            <td><input value="'.$mfv.'" size="6" name="marketplaceCommission['.$category->getId().']" class="input-text" type="text" /></td>
            <td>
                <select name="marketplace['.$category->getId().']" class="select admin__control-select">
                    <option value="1">Flat</option>
                    <option '.$mft.' value="2">Percentage</option>
                </select>
            </td>
            <td><input value="'.$cfv.'" name="cancellationCommission['.$category->getId().']" size="6" class="input-text" type="text" /></td>
            <td>
                <select name="cancellation['.$category->getId().']" class="select admin__control-select">
                    <option value="1">Flat</option>
                    <option '.$cft.' value="2">Percentage</option>
                </select>
            </td>
            <td>
                <select name="status['.$category->getId().']" class="select admin__control-select">
                    <option value="1">Enable</option>
                    <option '.$status.' value="2">Disabled</option>
                </select>
            </td>
        </tr>';
                }
            } else {
                if($isChild == true){
                    $title = false;
                    $childrens = explode(',', $category->getAllChildren());
                    foreach ($childrens as $childid) {
                        if(in_array($childid, $vendorCats, true)) {
                            $title = true;
                            break;
                        }
                    }
                    if ($title) {
                        if($category->getLevel() <= 3) {
                            $prevClass = [];
                        }
                        $prevClass['cv'] = array_key_exists('cv', $prevClass) ? $prevClass['cv'].' .cv_'.$category->getId() : 'cv_'.$category->getId();
                        $cvClass = $prevClass['cv'];
                        $html .='<tr class="data-row">
                            <td><strong>'.$spaceCat.$category->getName().'</strong>
                            <input type="hidden" name="parent_ids['.$category->getId().']" value="'.$category->getAllChildren().'" />
                            </td>
                            <td><input size="6" name="parent_commission['.$category->getId().']" class="input-text '.$cvClass.'" type="text" /></td>
                            <td>
                                <select name="parent_calculation_type['.$category->getId().']" class="select admin__control-select">
                                    <option value="1">Flat</option>
                                    <option value="2">Percentage</option>
                                </select>
                            </td>
                            <td><input size="6" name="parent_marketplaceCommission['.$category->getId().']" class="input-text" type="text" /></td>
                            <td>
                                <select name="parent_marketplace['.$category->getId().']" class="select admin__control-select">
                                    <option value="1">Flat</option>
                                    <option value="2">Percentage</option>
                                </select>
                            </td>
                            <td><input name="parent_cancellationCommission['.$category->getId().']" size="6" class="input-text" type="text" /></td>
                            <td>
                                <select name="parent_cancellation['.$category->getId().']" class="select admin__control-select">
                                    <option value="1">Flat</option>
                                    <option value="2">Percentage</option>
                                </select>
                            </td>
                            <td>
                                <select name="parent_status['.$category->getId().']" class="select admin__control-select">
                                    <option value="1">Enable</option>
                                    <option value="2">Disabled</option>
                                </select>
                            </td>
                        </tr>';
                    }
                }
            }

            if ($category->hasChildren()) {
                $html .= $this->_getTreeCategories($category, true, $prevClass);
            }
        }

        return $html;
    }
}
