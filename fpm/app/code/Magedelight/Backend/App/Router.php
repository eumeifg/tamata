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
namespace Magedelight\Backend\App;

/**
 * Description of Router
 *
 * @author Rocket Bazaar Core Team
 */
class Router extends \Magento\Framework\App\Router\Base
{

    /**
     * @var \Magento\Framework\UrlInterface $url
     */
    protected $_url;

    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     */
    protected $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @var bool
     */
    protected $applyNoRoute = true;
    
    /**
     * @var string
     */
    protected $pathPrefix = \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE;
    
    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }
}
