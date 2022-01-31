<?php

class Xml 
{
  private $xmlData;
  public $error = false;

  public function __construct($post) 
  {
      // debug($post['device_name']);

      $this->xmlData = simplexml_load_string($post['device_xml']);

        if (!$this->checkInput($post))
        {
          debug("FAILED");
          $this->error = true;
          return null;
        } 
  }

  private function checkInput($input)
  {
    if ($input['device_name'] = null || strlen($input['device_name']) == 0 || $input['device_desc'] == null || strlen($input['device_desc']) == 0) 
    {
        debug('This is a new device, but the device label and description are not completed. Please correct and resubmit.');
        $this->error = true;
        return false;
    }

    if (!(isset($this->xmlData->manufacturer) && isset($this->xmlData->deviceId) && isset($this->xmlData->deviceType)))
    {
      debug("XML file format is not known, or has incomplete information.");
      $this->error = true;
      return false;
    }

    return true;
  }

  public function getXmlData()
  {
    return $this->xmlData;
  }
}