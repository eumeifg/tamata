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
namespace Magedelight\ProductAlert\Block\Adminhtml\Stock\Renderer;

use \Magento\Framework\DataObject;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    protected $_objectManger;

    protected $_websiteFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->_objectManger = $objectManager;
        $this->_websiteFactory = $websiteFactory;
    }

    public function render(DataObject $row)
    {
        $website = $row->getWebsiteId();
        $websites = $this->_websiteFactory->create()->getCollection()->toOptionArray();
        $sites = explode(',', $website);
        $webSitesLabels = [];
        foreach ($websites as $v) {
            if (array_search($v['value'], $sites) !== false) {
                $webSitesLabels[] = $v['label'];
            }
        }
        $website = implode(", ", array_unique($webSitesLabels));
        return $website;
    }
}
