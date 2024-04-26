<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.1.96
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Block\Adminhtml;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'Mirasvit_Helpdesk';
        $this->_headerText = __('Workflow Rules');
        $this->_addButtonLabel = __('Add New Rule');
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

    /************************/
}
