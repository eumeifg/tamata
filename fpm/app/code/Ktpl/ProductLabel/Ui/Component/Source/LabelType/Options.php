<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Ui\Component\Source\LabelType;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{

    private $labelTypeList;

    public function toOptionArray()
    {
        return $this->getLabelTypeList();
    }

    private function getLabelTypeList()
    {
        $this->labelTypeList[0] = [
            'id'      => 0,
            'value'   => 'image',
            'label'   => 'Image',
        ];
        $this->labelTypeList[1] = [
            'id'      => 1,
            'value'   => 'text',
            'label'   => 'Text',
        ];

        return $this->labelTypeList;
    }
}
