<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Ktpl\Productslider\Controller\Adminhtml\Slider;
use Ktpl\Productslider\Model\SliderFactory;

/**
 * Class Edit
 * @package Ktpl\Productslider\Controller\Adminhtml\Slider
 */
class Edit extends Slider
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param SliderFactory $sliderFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $sliderFactory, $coreRegistry);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $slider = $this->_initSlider();
        if ($this->getRequest()->getParam('id') && !$slider->getId()) {
            $this->messageManager->addErrorMessage(__('This Slider no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id'       => $slider->getId(),
                    '_current' => true
                ]
            );

            return $resultRedirect;
        }

        $data = $this->_session->getData('ktpl_productslider_slider_data', true);
        if (!empty($data)) {
            $slider->setData($data);
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Ktpl_Productslider::slider');
        $resultPage->getConfig()->getTitle()
            ->set(__('Sliders'))
            ->prepend($slider->getId() ? $slider->getName() : __('New Slider'));

        return $resultPage;
    }
}
