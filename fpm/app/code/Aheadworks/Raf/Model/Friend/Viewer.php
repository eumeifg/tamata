<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Friend;

use Aheadworks\Raf\Model\Config;
use Aheadworks\Raf\Model\Renderer\Cms\Block as CmsBlockRenderer;
use Aheadworks\Raf\Model\Source\Cms\Block as CmsBlockSource;

/**
 * Class Viewer
 *
 * @package Aheadworks\Raf\Model\Friend
 */
class Viewer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CmsBlockRenderer
     */
    private $cmsBlockRenderer;

    /**
     * @param Config $config
     * @param CmsBlockRenderer $cmsBlockRenderer
     */
    public function __construct(
        Config $config,
        CmsBlockRenderer $cmsBlockRenderer
    ) {
        $this->config = $config;
        $this->cmsBlockRenderer = $cmsBlockRenderer;
    }

    /**
     * Retrieve static block html for welcome popup
     *
     * @param int $storeId
     * @return string|null
     */
    public function getStaticBlockHtmlForWelcomePopup($storeId)
    {
        $blockId = $this->config->getStaticBlockIdForWelcomePopup($storeId);
        if ($blockId && $blockId != CmsBlockSource::DONT_DISPLAY) {
            return $this->cmsBlockRenderer->render($blockId, $storeId);
        }

        return null;
    }
}
