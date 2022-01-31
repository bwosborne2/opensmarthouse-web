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

class Device 
{
  private $xmlData;
  private $details;
  private $dbh;
  private $firmwareVersion = - 1;
  private $mfgLabel;
  public $error = false;

  public function __construct($user, $label, $description, $category, $xml)

  {

    if ($this->getDbh())
    {
        $this->details = new stdClass;

        $this->details->created_by = $user;
        $this->details->label = $label;
        $this->details->description = $description;
        $this->details->category = $category;
        $this->xmlData = $xml;
    
        if ($this->getMfr())
        {
            $this->details->manufacturer = hexdec($xml->manufacturer);
        }
        else
        {
            return;
        }
    
        $typeId = sprintf("%04X:%04X", hexdec($xml->deviceType), hexdec($xml->deviceId));
        $this->details->type_id = $typeId;
    
        $thingId = str_replace(' ', '', $this->details->label); // Replaces all spaces with hyphens.
        $thingId = preg_replace('/[^A-Za-z0-9\-]/', '', $thingId); // Removes special chars.
        $this->details->thingid = strtolower($thingId);
    
        $this->details->versionmin = '0.0';
        $this->details->versionmax = '255.255';
        $this->details->listening =  $xml->listening == 'true' ? 1 : 0;
        $this->details->routing = $xml->routing == 'true' ? 1 : 0;
        $this->details->security = $xml->security == 'true' ? 1 : 0;
        $this->details->frequently_listening = $xml->frequentlyListening == 'true' ? 1 : 0;
        $this->details->beaming = $xml->beaming == 'true' ? 1 : 0;
    
        $maxBaud = (array) $xml->maxBaudRate;
        $this->details->max_baud = $maxBaud[0];
    }
    else
    {
        return;
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
  public function debug()
  { 
    debug(print_r($this->details, true));
  }

  private function getMfr()
  {
    $dbManufacturer = null;
    $manufacturerId = hexdec($this->xmlData->manufacturer);

    $sql = 'SELECT * FROM zwave_manufacturers WHERE reference LIKE :mfg';
    $sth = $this->dbh->prepare($sql);

    $sth->execute(['mfg'=> $manufacturerId]);
    $dbManufacturer = $sth->fetch(PDO::FETCH_OBJ);

    if ($dbManufacturer == null)  {
      debug('Manufacturer ' . sprintf("%04X", $manufacturerId) . ' is not known! Please update the manufacturer database and try again.');
      $this->error = true;
      return false;
    } 
    
    $this->mfgLabel = $dbManufacturer->label;
    debug("Manufacturer: $this->mfgLabel");
    return true;
  }

  public function getVersions()
  {
    // Specifically search for the VERSION class so we can get the application version information
    $protocolVersion = 0.0;
    $libraryType = 0;
    $versionCommandClass = null;
    
    // Loop over all endpoints
    for ($cntEndpoint = 0;$cntEndpoint < count($this->xmlData
        ->endpoints
        ->entry);$cntEndpoint++) 
    {
        $instance = $this->xmlData
            ->endpoints
            ->entry[$cntEndpoint]->endPoint;
        for ($cnt = 0;$cnt < count($instance
            ->supportedCommandClasses
            ->entry);$cnt++) 
        {
            if ($instance
                ->supportedCommandClasses
                ->entry[$cnt]->commandClass == 'COMMAND_CLASS_VERSION') 
            {
                $className = $this->getxmlClass($instance
                    ->supportedCommandClasses
                    ->entry[$cnt]);
                $versionCommandClass = $instance
                    ->supportedCommandClasses
                    ->entry[$cnt]->{$className};
                break;
            }
        }
    }


    if ($versionCommandClass != null) 
    {
        $parts = explode('.', $versionCommandClass->protocolVersion);
        
        $protocolVersion = sprintf("%d.%03d", (int)$parts[0], (int)$parts[1]);
        
        switch ($versionCommandClass->libraryType) 
        {
            case 'LIB_CONTROLLER_STATIC':
                $libraryType = 1;
            break;
            case 'LIB_CONTROLLER':
                $libraryType = 2;
            break;
            case 'LIB_SLAVE_ENHANCED':
                $libraryType = 3;
            break;
            case 'LIB_SLAVE':
                $libraryType = 4;
            break;
            case 'LIB_INSTALLER':
                $libraryType = 5;
            break;
            case 'LIB_SLAVE_ROUTING':
                $libraryType = 6;
            break;
            case 'LIB_CONTROLLER_BRIDGE':
                $libraryType = 7;
            break;
            case 'LIB_TEST':
                $libraryType = 8;
            break;
        }
        
        $parts = explode('.', $versionCommandClass->applicationVersion);
        $this->firmwareVersion = sprintf("%d.%03d", (int)$parts[0], (int)$parts[1]);
		debug("Firmware: $this->firmwareVersion");
    }

    if ($this->firmwareVersion == - 1) 
    {
        debug('The firmware version for this device is not known. Processing of the XML halted.');
        
        $this->firmwareVersion = "0.000";
        $this->error = true;
    }
    $this->details->library_type = $libraryType; 
    $this->details->protocol_version = $protocolVersion;
  }

  private function getxmlClass($data)
  {
    foreach ($data as $key => $value)
    {
        if ($key != 'commandClass')
        {
            return $key;
        }
    }
    return null;
  }

  public function checkExisting()
  {
    debug('Checking for existing entry');
    // Now look for a device with this type/id

    $sql = 'SELECT * FROM zwave_devices WHERE type_id LIKE :type
     AND manufacturer LIKE :mfg
     AND (versionmin<= :fwVer 
     OR versionmin=0 AND versionmax>=:fwVer 
     OR versionmax=255.255)';
    $sth = $this->dbh->prepare($sql);

    $sth->bindValue('type', '%' . $this->details->type_id . '%');
    $sth->bindValue('mfg', $this->details->manufacturer);
    $sth->bindValue('fwVer', floatval($this->firmwareVersion));
    $sth->execute();
    $results  = $sth->fetch(PDO::FETCH_OBJ);

    if ($results != null) 
    {
        debug('WARNING: Device with these IDs is already in the database as ' . $this->mfgLabel . ', ' . $results->label . ' and endpoints are already defined.');
        $this->error = true;
    } 

  }

  public function save()
  {
    debug('Saving device');
    $placeholders = [];

    $rows = (array)$this->details;
    $columns = array_keys($rows);

    foreach($columns as $column)
    {
        array_push($placeholders, ':' . $column);
    }

    $sql = "INSERT INTO zwave_devices (" . 
        implode(", ", $columns) . ") VALUES (" . 
        implode(", ", $placeholders) . 
    ")";

    $sth = $this->dbh->prepare($sql);
    debug('Temporarily Disabled');
    // $sth->execute($rows);

    // Get database id
    $dbId = $this->dbh->lastInsertId();

    // return $dbId;
    return 1460;
    
  }
}