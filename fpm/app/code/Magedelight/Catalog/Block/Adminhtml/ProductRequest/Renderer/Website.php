<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    protected $_objectManger;

    protected $_websiteFactory;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        SystemStore $systemStore,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->_objectManger = $objectManager;
        $this->_websiteFactory = $websiteFactory;
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
    }

    public function render(DataObject $row)
    {
        $websiteIds = $row->getWebsiteIds();
        $Ids = explode(',', $websiteIds);
        $content = '';
        $websiteCollection = $this->systemStore->getWebsiteCollection();

        /** @var \Magento\Store\Model\Website $website */
        foreach ($websiteCollection as $website) {
            if (in_array($website->getId(), $Ids)) {
                $content .= $website['name'] . "<br/>";
            }
        }

        return $content;
    }
}
