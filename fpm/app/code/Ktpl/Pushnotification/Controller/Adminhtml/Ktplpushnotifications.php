<?php declare(strict_types=1);


namespace Ktpl\Pushnotification\Controller\Adminhtml;


abstract class Ktplpushnotifications extends \Magento\Backend\App\Action
{

    protected $_coreRegistry;
    const ADMIN_RESOURCE = 'Ktpl_Pushnotification::pushnotifications';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Ktpl'), __('Ktpl'))
            ->addBreadcrumb(__('Ktpl Pushnotifications'), __('Ktpl Pushnotifications'));
        return $resultPage;
    }
}

