<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Controller;

abstract class AbstractRemove extends AbstractController
{
    protected $customer;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Customer $customer
    ) {
        parent::__construct($context, $customerUrl, $customerSession, $storeManger, $resultPageFactory);
        $this->customer = $customer;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('item');

        $modelName = "Magento\\ProductAlert\\Model\\" . ucfirst(strtolower(static::TYPE));
        $item = $this->_objectManager->get($modelName)->load($id);
        $_customer = $this->customer->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
            ->load($this->_customerSession->getCustomerId());

        // check if not a guest subscription (cust. id is set) and is matching with logged in customer
        if ($item->getCustomerId() > 0
            && $item->getCustomerId() == $_customer->getId()
        ) {
            try {
                $item->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __(
                        'An error occurred while deleting the item from Subscriptions: %s',
                        $e->getMessage()
                    )
                );
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __(
                        'An error occurred while deleting the item from Subscriptions.'
                    )
                );
            }
        }
        $redirect = $this->_redirectFactory->create();
        $redirect->setPath($this->_url->getUrl('*/*'));

        return $redirect;
    }
}
