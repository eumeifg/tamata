<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\Vendor\Edit\Renderer;

/**
 * Description of Store
 *
 * @author Rocket Bazaar Core Team
 */
class Region extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Directory\Helper\Data $directoryHelper,
        $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml()
    {
        $htmlIdPrefix = $this->getForm()->getHtmlIdPrefix();
        $fieldNameSuffix = $this->getForm()->getFieldNameSuffix();
        $elementText = $this->getElementText();
        $elementSelect = $this->getElementSelect();
        $elementCountry = $this->getElementCountry();
        $regions = $this->getRegions();
        $currentRegion = $this->getCurrentRegion();
        $currentSelectedRegion = $this->getCurrentSelectedRegion();

        $html =  '<div class="admin__field field admin__field field-' . $elementText . '" data-ui-id="vendor-edit-tab-website-fieldset-element-form-field-' . $elementText . '" data-index="' . $htmlIdPrefix . $elementText . '">
                    <label class="label admin__field-label" for="' . $htmlIdPrefix . $elementText . '" data-ui-id="vendor-edit-tab-website-fieldset-element-text-vendor-region-label"><span>State/Province</span></label>
            <div class="admin__field-control control">';
        $html .= '<input id="' . $htmlIdPrefix . $elementText . '" name="vendor[' . $elementText . ']" ';
        $html .= 'data-ui-id="vendor-edit-tab-website-fieldset-element-text-vendor-region" value="' . $currentRegion;
        $html .= '" title="State/Province" class="input-text admin__control-text validate-alpha-with-spaces-50" ';
        $html .= 'type="text">';
        $html .= '</div></div>';

        $html .=  '<div class="admin__field field field-' . $elementSelect . '" data-ui-id="vendor-edit-tab-website-fieldset-element-form-field-' . $elementSelect . '" data-index="' . $htmlIdPrefix . $elementSelect . '">
                    <label class="label admin__field-label" for="' . $htmlIdPrefix . $elementSelect . '" data-ui-id="vendor-edit-tab-website-fieldset-element-text-vendor-region-label"><span>State/Province</span></label>
            <div class="admin__field-control control">';
        $html .= '<select name="vendor[' . $elementSelect . ']" defaultValue = "' . $currentSelectedRegion;
        $html .= '" id="' . $htmlIdPrefix . $elementSelect . '" class="select required-entry admin__control-select" ';
        $html .= 'title="State/Province" ><option value="">Select State/Province</option></select>';
        $html .= '</div></div>';

        $html .= "<script type=\"text/javascript\">" .
            "require(['prototype','mage/adminhtml/form'], function(){";
        $html .= '$("' . $htmlIdPrefix . $elementSelect . '").setAttribute("defaultValue", "' .
            $currentSelectedRegion . '");' . "\n";
        $html .=" window.updater = new RegionUpdater('" . $htmlIdPrefix . $elementCountry . "'," .
            " '" . $htmlIdPrefix . $elementText . "', '" . $htmlIdPrefix . $elementSelect . "', " .
            $this->_directoryHelper->getRegionJson() . ", 'hide');});";
        $html .="</script>";

        if (empty($regions)) {
            $html .= '<script>
                        require([
                             "jquery",
                        ], function($){
                            $("#' . $htmlIdPrefix . $elementText . '").show();
                            $("#' . $htmlIdPrefix . $elementSelect . '").hide();
                          });
                   </script>';
        } else {
            $html .= '
                    <script>
                        require([
                             "jquery",
                        ], function($){
                            $(document).ready(function () {
                                $("#' . $htmlIdPrefix . $elementText . '").hide();
                                $("#' . $htmlIdPrefix . $elementSelect . '").show();
                            });
                          });
                   </script>
                ';
        }

        $html .= '
            <script>
                require([
                     "jquery",
                     "prototype"
                ], function($){
                    $(document).ready(function () {
                        if($("#' . $htmlIdPrefix . $elementText . '").css("display") == "none"){
                            $$("label[for=\"' . $htmlIdPrefix . $elementText . '\"]")[0].up("div").hide();
                        }else if($("#' . $htmlIdPrefix . $elementSelect . '").css("display") == "none"){
                            $$("label[for=\"' . $htmlIdPrefix . $elementSelect . '\"]")[0].up("div").hide();
                        }
                        $(document).on("change", "#' . $htmlIdPrefix . $elementCountry . '", function () {
                            if($("#' . $htmlIdPrefix . $elementText . '").css("display") == "none"){
                                $$("label[for=\"' . $htmlIdPrefix . $elementText . '\"]")[0].up("div").hide();
                                $$("label[for=\"' . $htmlIdPrefix . $elementSelect . '\"]")[0].up("div").show();
                            }else if($("#' . $htmlIdPrefix . $elementSelect . '").css("display") == "none"){
                                $$("label[for=\"' . $htmlIdPrefix . $elementText . '\"]")[0].up("div").show();
                                $$("label[for=\"' . $htmlIdPrefix . $elementSelect . '\"]")[0].up("div").hide();
                            }
                        });
                    });
                  });
           </script>
        ';
        return $html;
    }
}
