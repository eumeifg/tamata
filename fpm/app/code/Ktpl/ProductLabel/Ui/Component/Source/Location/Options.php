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

namespace Ktpl\ProductLabel\Ui\Component\Source\Location;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    private $locationsList;

    public function toOptionArray()
    {
        return $this->getLocationsList();
    }

    private function getLocationsList()
    {
        $this->locationsList[0] = [
            'id'      => 0,
            'value'   => 'top_right',
            'label'   => 'Top Right',
        ];
        $this->locationsList[1] = [
            'id'      => 1,
            'value'   => 'top_left',
            'label'   => 'Top Left',
        ];
        $this->locationsList[2] = [
            'id'      => 2,
            'value'   => 'lower_right',
            'label'   => 'Lower Right',
        ];
        $this->locationsList[3] = [
            'id'      => 3,
            'value'   => 'lower_left',
            'label'   => 'Lower Left',
        ];

        return $this->locationsList;
    }
}
