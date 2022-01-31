<?php

class ServerProperties 
{

  // Server parameters
  
  // Production flag
  // Try usinf environment variabke & $_ENV
  const PRODUCTION = false; 

  // debug flag - to save debug info to DEBUG_FILE
  const DEBUG_ENABLED = true;

  const SERVER_TMP_DIR = __DIR__ . "/logs";
  const DEBUG_FILE = __DIR__ . "/logs/zwave-debug.log";
}

// echo ServerProperties::PRODUCTION ? 'true' : 'false';

include(__DIR__ . '/logging.php');

// require_once (__DIR__ . '/libraries/Xml.php');
// require_once (__DIR__ . '/models/Endpoint.php');
// require_once (__DIR__ . '/models/Device.php');
