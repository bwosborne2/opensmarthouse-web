<?php

// Get the database definition

if(ServerProperties::PRODUCTION)
{
  require_once(__DIR__ . '/../../api/dbdefinition.php');
}
else
{
  require_once(__DIR__ . '/../dbdefinition.php');
}

class CommandClass
{
  private $supportedClasses;
  private $hasBasic = false;
  private $isBasic = 0;
  private $userClassCnt = 0;
  private $endpoint;
  private $endpointId;

  public function __construct($dbId, $endpoint, $supportedClasses)
  {
    if ($this->getDbh())
    {
      debug('CommandClass db connection successful');
      $this->supportedClasses = $supportedClasses;
      $this->endpoint = $endpoint;
      $this->endpointId = $endpoint->number;
      $this->checkBasic();

    }

  }

  private function getDbh()
  {
    global $dbserver;
    global $dbuser;
    global $dbpass;
    global $dbname;

    // Set DSN
    $dsn = 'mysql:host=' . $dbserver . ';dbname=' . $dbname;
    $options = array (
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
    );

    // Create a new PDO instance
    try {
        $this->dbh = new PDO ($dsn, $dbuser, $dbpass, $options);
    }		
    // Catch any errors
    catch ( PDOException $e ) {
        $this->error = $e->getMessage();
        debug("DB Handler ERROR:" . $this->error);
        return false;
    }
    if(!ServerProperties::PRODUCTION)
    {
      $sql = "SET GLOBAL sql_mode='ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
      $sth = $this->dbh->prepare($sql);
      $sth->execute();
    }
    return true;
  }

  private function checkBasic()
  {
    for ($cnt = 0;$cnt < count($this->supportedClasses->entry);$cnt++)
    {
        if ($this->isUserClass($this->supportedClasses->entry[$cnt]->commandClass))
        {
            $$this->userClassCnt++;
        }
        if ($this->supportedClasses->entry[$cnt]->commandClass == "BASIC")
        {
            $this->hasBasic = true;
        }
    }

    if ($this->userClassCnt == 2 && $this->hasBasic)
    {
        $this->isBasic = 1;
    }

  }

  private function isUserClass($className)
  {
    $userClasses = array(
      'BASIC',
      'SWITCH_BINARY',
      'SWITCH_MULTILEVEL',
      'METER',
      'THERMOSTAT_OPERATING_STATE',
      'THERMOSTAT_MODE',
      'THERMOSTAT_FAN_MODE',
      'SENSOR_MULTILEVEL',
      'SENSOR_ALARM',
      'THERMOSTAT_FAN_STATE',
      'THERMOSTAT_SETPOINT',
      'SENSOR_BINARY',
      'ALARM',
      'COLOR',
      'SCENE_ACTIVATION',
      'CENTRAL_SCENE',
      'DOOR_LOCK',
      'CLOCK',
      'TIME_PARAMETERS',
      'METER_TBL_MONITOR',
      'METER_PULSE',
      'BARRIER_OPERATOR',
      'CONFIGURATION',
      'MANUFACTURER_PROPRIETARY',
      'PROTECTION',
      'INDICATOR'
    );

    return in_array(strtoupper($className), $userClasses);
  }

  private function getXmlCommandClass($xml)
  {
    foreach ($xml as $key => $value)
    {
        if ($key != 'commandClass')
        {
            return $key;
        }
    }

    return null;
  }

}