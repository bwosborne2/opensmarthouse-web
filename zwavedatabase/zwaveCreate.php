<?php

class ServerProperties 
{

  // Server parameters
  
  // Production flag
  // Try using environment variable & $_ENV
  const PRODUCTION = false; 

  // debug flag - to save debug info to DEBUG_FILE
  const DEBUG_ENABLED = true;

  const SERVER_TMP_DIR = __DIR__ . "/logs";
  const DEBUG_FILE = __DIR__ . "/logs/zwave-debug.log";
}

// echo ServerProperties::PRODUCTION ? 'true' : 'false';

include(__DIR__ . '/logging.php');

require_once (__DIR__ . '/libraries/Xml.php');
// require_once (__DIR__ . '/models/Endpoint.php');
require_once (__DIR__ . '/models/Device.php');

debug("Started processing... " . date("h:i:sa"));

if(ServerProperties::PRODUCTION)
{
  // Manage security
  require('../dmxConnectLib/dmxConnect.php');

  $app = new \lib\App();
  $app->exec(<<<'JSON'
  {
    "steps": [
      "Connections/opensmarthouse",
      "SecurityProviders/sitesecurity",
      {
        "module": "auth",
        "action": "restrict",
        "options": {"permissions":"ZwaveEditor","provider":"sitesecurity"}
      }
    ]
  }
  JSON, TRUE);

  debug("Authorisation completed");
  // debug("Session variables " . print_r($_SESSION, TRUE));

  $userId = $_SESSION["user_id"];
}
else
{
  $userId = 123;
}

debug("User ID: $userId");

debug("Processing..." . $_POST['device_name']);
$xml = new Xml($_POST);
if ($xml->error == true)
{
  debug("Validation ERROR");
  return;
}
$xmlData = $xml->getXmlData();

debug('Start populating Device model');
$device = new Device(
    $userId,
    $_POST['device_name'], 
    $_POST['device_desc'], 
    $_POST['device_category'], 
    $xmlData
);
if ($device->error == true)
{
  debug("Manufacturer ERROR");
  return;
}
