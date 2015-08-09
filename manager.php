<?php

namespace Ligrev;

define("L_DEBUG", 0);
define("L_INFO", 1);
define("L_CAUT", 2);
define("L_WARN", 3);
define("L_AAAA", 4);

// Default error reporting level
define("L_REPORT", L_DEBUG);

// Take over PHP's error handling, since it's a picky whore sometimes.
function php_error_handler($no, $str, $file, $line) {
  switch ($no) {
    case E_ERROR:
    case E_RECOVERABLE_ERROR:
      l("[PHP] " . $str . " at " . $file . ":" . $line, L_AAAA);
      die();
      break;
    case E_WARNING:
    case E_PARSE:
      l("[PHP] " . $str . " at " . $file . ":" . $line, L_WARN);
      break;
    case E_NOTICE:
      l("[PHP] " . $str . " at " . $file . ":" . $line, L_CAUT);
      break;
    case E_DEPRECATED:
    case E_STRICT:
      l("[PHP] " . $str . " at " . $file . ":" . $line, L_DEBUG);
      break;
    default:
      l("[PHP] " . $str . " at " . $file . ":" . $line, L_INFO);
      break;
  }
  return true;
}

// Function to log/echo to the console. Includes timestamp and what-not
function l($text, $level = L_INFO) {
  // get current log time
  $time = date("H:i:s");
  switch ($level) {
    case L_DEBUG:
      $tag = "[\033[0;36mDBUG\033[0m]";
      break;
    case L_INFO:
      $tag = "[\033[0;37mINFO\033[0m]";
    default:
      break;
    case L_CAUT:
      $tag = "[\033[0;33mCAUT\033[0m]";
      break;
    case L_WARN:
      $tag = "[\033[0;31mWARN\033[0m]";
      break;
    case L_AAAA:
      $tag = "[\033[41mAAAA\033[0m]";
      break;
  }
  if ($level >= L_REPORT) {
    echo "[" . $time . "] " . $tag . " " . html_entity_decode($text) . PHP_EOL;
  }
}

set_error_handler("Ligrev\\php_error_handler");

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR . 'lib');

// Hey, let's load some things
l("Reading config.php...");
require_once 'config.php';

l("Loading libraries...");
require_once 'JAXL/jaxl.php';
require_once 'jwt/src/JWT.php';
require_once 'jwt/src/SignatureInvalidException.php';

require_once 'classes/LCN.php';

l("[JAXL] Loading JAXL and connecting...");
$client = new \JAXL($config['jaxl']);

$client->require_xep(array(
  '0045', // MUC
  '0203', // Delayed Delivery
  '0199'  // XMPP Ping
));

$client->add_cb('on_auth_success', function() {
  global $client, $config, $rooms;
  l("[JAXL] Connected with jid " . $client->full_jid->to_string());
  $client->get_vcard();
  $client->get_roster();
  $client->set_status("", "chat", 10);
  \Ev::run();
});

$client->add_cb('on_auth_failure', function($reason) {
  global $client;
  $client->send_end_stream();
  l("[JAXL] Auth failure: " . $reason, L_WARN);
});

$w1 = new \EvTimer(1, 1, function ($w) {
  global $client, $config;
  $say = mt_rand(1,1000);
  l($say);
  if ($say > 9) {
    $client->send_chat_msg($config['jaxl']['jid'], $say);
  }
});

$client->start();
