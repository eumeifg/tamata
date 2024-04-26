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
namespace Magedelight\Backend\Block\Widget\Button;

use Magento\Framework\View\LayoutInterface;

class Toolbar implements ToolbarInterface
{
    /**
     * {@inheritdoc}
     */
    public function pushButtons(
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magedelight\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        foreach ($buttonList->getItems() as $buttons) {
            /** @var \Magento\Backend\Block\Widget\Button\Item $item */
            foreach ($buttons as $item) {
                $containerName = $context->getNameInLayout() . '-' . $item->getButtonKey();

                $container = $this->createContainer($context->getLayout(), $containerName, $item);

                if ($item->hasData('name')) {
                    $item->setData('element_name', $item->getName());
                }

                if ($container) {
                    $container->setContext($context);
                    $toolbar = $this->getToolbar($context, $item->getRegion());
                    $toolbar->setChild($item->getButtonKey(), $container);
                }
            }
        }
    }

    /**
     * Create button container
     *
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param string $containerName
     * @param \Magento\Backend\Block\Widget\Button\Item $buttonItem
     * @return \Magento\Backend\Block\Widget\Button\Toolbar\Container
     */
    protected function createContainer(LayoutInterface $layout, $containerName, $buttonItem)
    {
        $container = $layout->createBlock(
            '\Magento\Backend\Block\Widget\Button\Toolbar\Container',
            $containerName,
            ['data' => ['button_item' => $buttonItem]]
        );
        return $container;
    }

    /**
     * Return button parent block
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param string $region
     * @return \Magento\Backend\Block\Template
     */
    protected function getToolbar(\Magento\Framework\View\Element\AbstractBlock $context, $region)
    {
        $parent = null;
        $layout = $context->getLayout();
        if (!$region || $region == 'header' || $region == 'footer') {
            $parent = $context;
        } elseif ($region == 'toolbar') {
            $parent = $layout->getBlock('page.actions.toolbar');
        } else {
            $parent = $layout->getBlock($region);
        }

        if ($parent) {
            return $parent;
        }
        return $context;
    }
}
