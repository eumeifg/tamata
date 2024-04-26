<?php

namespace Ktpl\BarcodeGenerator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BarcodeFormat implements ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ["value" => "C39", "label" => "TYPE_CODE_39"],
            ["value" => "C39+", "label" => "TYPE_CODE_39_CHECKSUM"],
            ["value" => "C39E", "label" => "TYPE_CODE_39E"],
            ["value" => "C39E+", "label" => "TYPE_CODE_39E_CHECKSUM"],
            ["value" => "C93", "label" => "TYPE_CODE_93"],
            ["value" => "S25", "label" => "TYPE_STANDARD_2_5"],
            ["value" => "S25+", "label" => "TYPE_STANDARD_2_5_CHECKSUM"],
            ["value" => "I25", "label" => "TYPE_INTERLEAVED_2_5"],
            ["value" => "I25+", "label" => "TYPE_INTERLEAVED_2_5_CHECKSUM"],
            ["value" => "C128", "label" => "TYPE_CODE_128"],
            ["value" => "C128A", "label" => "TYPE_CODE_128_A"],
            ["value" => "C128B", "label" => "TYPE_CODE_128_B"],
            ["value" => "C128C", "label" => "TYPE_CODE_128_C"],
            ["value" => "EAN2", "label" => "TYPE_EAN_2"],
            ["value" => "EAN5", "label" => "TYPE_EAN_5"],
            ["value" => "EAN8", "label" => "TYPE_EAN_8"],
            ["value" => "EAN13", "label" => "TYPE_EAN_13"],
            ["value" => "UPCA", "label" => "TYPE_UPC_A"],
            ["value" => "UPCE", "label" => "TYPE_UPC_E"],
            ["value" => "MSI", "label" => "TYPE_MSI"],
            ["value" => "MSI+", "label" => "TYPE_MSI_CHECKSUM"],
            ["value" => "POSTNET", "label" => "TYPE_POSTNET"],
            ["value" => "PLANET", "label" => "TYPE_PLANET"],
            ["value" => "RMS4CC", "label" => "TYPE_RMS4CC"],
            ["value" => "KIX", "label" => "TYPE_KIX"],
            ["value" => "IMB", "label" => "TYPE_IMB"],
            ["value" => "CODABAR", "label" => "TYPE_CODABAR"],
            ["value" => "CODE11", "label" => "TYPE_CODE_11"],
            ["value" => "PHARMA", "label" => "TYPE_PHARMA_CODE"],
            ["value" => "PHARMA2T", "label" => "TYPE_PHARMA_CODE_TWO_TRACKS"]
        ];
    }

    public function toArray()
    {
        return [];
    }
}
