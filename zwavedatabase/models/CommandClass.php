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
  public function __construct($product, $endpoint, $supportedClasses)
  {
    if ($this->getDbh())
    {
      debug('CommandClass db connection successful');
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
}