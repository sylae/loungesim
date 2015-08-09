<?php

/**
 * Ligrev Communication Network. Fancy JWT wrapper
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class LCN {
  var $res;
  
  function __construct($payload=null) {
    if (is_array($payload)) {
      $this->res = $this->encode($payload);
    } elseif (is_string($payload) && $this->isLWT($payload)) {
      $this->res = $this->decode($payload);
    }
    return false;
  }

  function decode($encoded) {
    global $config;
    $encoded = str_replace('$LCN$', '', $encoded);
    try {
      $r = (array) \Firebase\JWT\JWT::decode($encoded, $config['LCN_key'], array('HS256'));
    } catch (\Exception $ex) {
      $r = array(
        'LCNerror' => $ex->getMessage()
      );
    }
    return $r;
  }

  function encode($payload) {
    global $config;
    return '$LCN$' . \Firebase\JWT\JWT::encode($payload, $config['LCN_key']);
  }
  
  static function isLWT($payload) {
    return (substr($payload, 0, 5)=='$LCN$');
  }

}
