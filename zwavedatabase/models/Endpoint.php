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

class Endpoint
{
  private $xmlDetails;
  private $details;

  public $error = false;

  public function __construct($dbId, $xml, $id)
  {
    if ($this->getDbh())
    {
 
      $this->xmlDetails = $xml
        ->endpoints
        ->entry[$id];

      $this->details = new stdClass;
      $this->details->device = $dbId;
      $epId = (int)$this->xmlDetails->id->int;
      $this->details->number = $epId;
      $this->details->label = 'Endpoint ' . $epId;

      $this->getBasicClass();
      $this->getGenericClass();
      $this->getSpecificClass();
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

  private function getBasicClass()
  { 
	  // Get the ID for ths device class
	  $sql = 'SELECT * FROM zwave_devicebasic WHERE name LIKE :basicclass';
	  $sth = $this->dbh->prepare($sql);

	  $sth->bindValue('basicclass', 
		$this->xmlDetails
		  ->endPoint 
		  ->deviceClass
		  ->basicDeviceClass
	  );
	  $sth->execute();
	  $results  = $sth->fetch(PDO::FETCH_OBJ);

	  if ($results == null) 
	  {
		debug('WARNING - BASIC Class is unknown: "' . 
		  $this->xmlDetails
			->endPoint 
			->deviceClass
			->basicDeviceClass . 
		  '"');

		  $this->error = true;
		  return;
	  } 
	  else
	  {
		  $basicClass = $results->id;
	  }  
    $this->details->basicclass = $basicClass;
	}

  private function getGenericClass()
  {
    // Get the ID for this device class
    $sql = 'SELECT * FROM zwave_devicegeneric WHERE name LIKE :genericDeviceClass';
    $sth = $this->dbh->prepare($sql);

	  $sth->bindValue('genericDeviceClass', 
      $this->xmlDetails
        ->endPoint
        ->deviceClass
        ->genericDeviceClass 
	  );
	  $sth->execute();
	  $results  = $sth->fetch(PDO::FETCH_OBJ);

    if($results == null)
    {
      debug('WARNING - GENERIC Class is unknown: "' . $this->xmlDetails
        ->endPoint
        ->deviceClass->genericDeviceClass . '"');

      $this->error = true;
		  return;
    }
    else
    {
      $genericClass = $results->id;
      $this->details->genericclass = $genericClass;
    }
  }

  private function getSpecificClass()
  {
    // Get the ID for this device class
    $sql = 'SELECT * FROM zwave_devicespecific WHERE name LIKE :specificDeviceClass';
    $sth = $this->dbh->prepare($sql);

	  $sth->bindValue('specificDeviceClass', 
      $this->xmlDetails
        ->endPoint
        ->deviceClass
        ->specificDeviceClass 
	  );
	  $sth->execute();
	  $results  = $sth->fetch(PDO::FETCH_OBJ);
    if ($results == null) {
      debug('WARNING - SPECIFIC Class is unknown: "' . $instance
        ->endPoint
        ->deviceClass->specificDeviceClass . '"');
      
      $this->error = true;
      return;
    } 
    else 
    {
        $specificClass = $results->id;
        $this->details->specificclass = $specificClass;
    }
  }

  public function save()
  {
    debug('Saving Endpoint ' . $this->details->number);
    $placeholders = [];

    $rows = (array)$this->details;
    $columns = array_keys($rows);

    foreach($columns as $column)
    {
        array_push($placeholders, ':' . $column);
    }

    $sql = "INSERT INTO zwave_endpoints (" . 
        implode(", ", $columns) . ") VALUES (" . 
        implode(", ", $placeholders) . 
    ")";

    $sth = $this->dbh->prepare($sql);
    debug('Temporarily Disabled');
    // $sth->execute($rows);

    // Get database id
    $dbId = $this->dbh->lastInsertId();

    // return $dbId;
    return 2321;
  }
}