<?php

namespace MDC\Sales\Plugin;

/**
 * ConfirmOrder class
 */
class ConfirmOrder
{

    /* public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $subject->addButton(
            'confirm_order',
            [
                'label' => __('Create Warranty Order'),
                'onclick' => "",
                'class' => 'action-default action-warranty-order',
            ]
        );
        return [$layout];
    } */

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if($subject->getNameInLayout() == 'sales_order_edit'){
            $customBlockHtml = $subject->getLayout()->createBlock(
                \MDC\Sales\Block\Adminhtml\Order\ModalBox::class,
                $subject->getNameInLayout().'_modal_box'
            )->setOrder($subject->getOrder())
                ->setTemplate('MDC_Sales::sales/order/view/confirm-popup.phtml')
                ->toHtml();
            return $result.$customBlockHtml;
        }
        return $result;
    }
}