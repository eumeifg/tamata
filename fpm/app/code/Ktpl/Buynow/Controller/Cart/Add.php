<?php

namespace Ktpl\Buynow\Controller\Cart;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            if (!$product) {
                return $this->goBack();
            }

            //Return if product already in cart
            $cartItems = $this->cart->getQuote()->getAllVisibleItems();
            foreach ($cartItems as $item) {
                if($item->getProduct()->getId() == $product->getId()) {
                    $baseUrl = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                        ->getStore()->getBaseUrl();
                    return $this->goBack($baseUrl.'checkout/', $product);
                }
            }

            $this->cart->addProduct($product, $params);
            $this->_eventManager->dispatch('ktpl_buynow_add_product', ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

           /* $this->_eventManager->dispatch(
                'checkout_cart_product_add_after',
                ['quote_item' => $result, 'product' => $product]
            );*/
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                $baseUrl = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                            ->getStore()->getBaseUrl();
                //return $this->resultRedirectFactory->create()->setPath($baseUrl.'checkout');
                return $this->goBack($baseUrl.'checkout/', $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }
            $url = $this->_checkoutSession->getRedirectUrl(true);
            if (!$url) {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->goBack();
        }
    }
}
