<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Block\Adminhtml\Template;

/**
 * Email template preview block.
 *
 * @api
 * @since 100.0.2
 */
class Preview extends \Magento\Email\Block\Adminhtml\Template\Preview
{
    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $emailConfig;
    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $_maliciousCode;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailFactory;

    /**
     * @var string
     */
    protected $profilerName = 'email_template_proccessing';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Email\Model\TemplateFactory $emailFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Email\Model\TemplateFactory $emailFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        $this->emailConfig = $emailConfig;
        parent::__construct($context, $maliciousCode, $emailFactory, $data);
    }

    /**
     * Prepare html output
     *
     * @return string
     * @throws \Exception
     */
    protected function _toHtml()
    {
        $request = $this->getRequest();

        $storeId = $this->getAnyStoreView()->getId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->_emailFactory->create();

        if ($id = (int)$request->getParam('id')) {
            $template->load($id);
        } else {
            $template->setTemplateType($request->getParam('type'));
            $template->setTemplateText($request->getParam('text'));
            $template->setTemplateStyles($request->getParam('styles'));
            // Emulate DB-loaded template to invoke strict mode
            $template->setTemplateId(123);
        }

        \Magento\Framework\Profiler::start($this->profilerName);

        /* Marketplace customization starts. */

        if ($this->getRequest()->getParam('preview_template_code')) {
            $templateCode = $this->getRequest()->getParam('preview_template_code');
        } else {
            $templateCode = $template->getOrigTemplateCode();
        }

        if($templateCode){
            $area = $this->emailConfig->getTemplateArea($templateCode);
            if ($area == \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                /* For seller area.*/
                $template->emulateDesign($storeId, $area);
                $templateProcessed = $this->_appState->emulateAreaCode(
                    $area,
                    [$template, 'getProcessedTemplate']
                );
            } else {
                $template->emulateDesign($storeId);
                $templateProcessed = $this->_appState->emulateAreaCode(
                    \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
                    [$template, 'getProcessedTemplate']
                );
            }
        }else {
            $template->emulateDesign($storeId);
            $templateProcessed = $this->_appState->emulateAreaCode(
                \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
                [$template, 'getProcessedTemplate']
            );
        }
        /* Marketplace customization ends. */

        $template->revertDesign();
        $templateProcessed = $this->_maliciousCode->filter($templateProcessed);

        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . $this->escapeHtml($templateProcessed) . "</pre>";
        }

        \Magento\Framework\Profiler::stop($this->profilerName);

        return $templateProcessed;
    }

    /**
     * Get either default or any store view
     *
     * @return \Magento\Store\Model\Store|null
     */
    protected function getAnyStoreView()
    {
        $store = $this->_storeManager->getDefaultStoreView();
        if ($store) {
            return $store;
        }
        foreach ($this->_storeManager->getStores() as $store) {
            return $store;
        }
        return null;
    }
}
