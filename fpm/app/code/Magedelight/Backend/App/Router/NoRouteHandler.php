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
namespace Magedelight\Backend\App\Router;

class NoRouteHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    /**
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $routeConfig;

    /**
     * @param \Magedelight\Backend\Helper\Data $helper
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     */
    public function __construct(
        \Magedelight\Backend\Helper\Data $helper,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig
    ) {
        $this->helper = $helper;
        $this->routeConfig = $routeConfig;
    }

    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function process(\Magento\Framework\App\RequestInterface $request)
    {
        $requestPathParams = explode('/', trim($request->getPathInfo(), '/'));
        $areaFrontName = array_shift($requestPathParams);
        if ($areaFrontName === $this->helper->getAreaFrontName(true)) {
            $moduleName = $this->routeConfig->getRouteFrontName('rbvendor');
            $actionNamespace = 'index';
            $actionName = 'index';
            $request->setModuleName($moduleName)->setControllerName($actionNamespace)->setActionName($actionName);
            return true;
        }
        return false;
    }
}
