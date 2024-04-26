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
namespace Magedelight\Catalog\Block\Adminhtml\Product\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\System\Store as SystemStore;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select
{

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $converter, $data);
    }

    public function render(DataObject $row)
    {
        $websiteIds = $row->getWebsites();
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
