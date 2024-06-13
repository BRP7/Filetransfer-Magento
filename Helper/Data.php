<?php

class Ccc_Filetransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getXmlAttribute($arrAttributes, $csvArray)
  {
    $arrayData = [];
    foreach ($arrAttributes as $arrAttributekey => $arrAttribute) {
      $parts = explode(':', $arrAttribute);
      $tagName = str_replace('.', '_', $parts[0]);
      $tagName = str_replace('items_', '', $tagName);
      $att = $parts[1];
      foreach ($csvArray as $csvkey => $rowData) {
        if (!isset($arrayData[$csvkey])) {
          $arrayData[$csvkey] = [];
        }
        foreach ($rowData as $key => $value) {
          if ($key == $tagName) {
            $arrayData[$csvkey][$arrAttributekey] = (string) $value->attributes()->$att;
          }
        }
      }
    }
    return $arrayData;
  }
}
