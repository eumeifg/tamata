<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     *
     * @param Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param PageFactory $resultPageFactory
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        \Magedelight\Vendor\Model\Design $design,
        PageFactory $resultPageFactory,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->design = $design;
        $this->resultPageFactory = $resultPageFactory;
        $this->ruleRepository = $ruleRepository;
        $this->resultRawFactory = $resultRawFactory;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return type
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_auth->isLoggedIn() || !$request->getParam('form_key')) {
            $data = '<script>window.location.href="' . $this->getUrl('rbvendor/index/index') . '"</script>';
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $this->design->applyVendorDesign();
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = ObjectManager::getInstance()->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->ruleFactory->create())
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        return $this->resultRawFactory->create()->setContents($html);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::promotion');
    }
}
