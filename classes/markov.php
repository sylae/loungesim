<?php

/**
 * Use Aran's library to markovify something
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class markov {

  public $markoved;

  function __construct($from) {
    global $db;

    $query = 'SELECT * FROM markov WHERE user=' . $db->quote($from, 'text') . ';';
    $res = & $db->query($query);
    $input = "";
    $count = 0;
    $length_sum = 0;
    $length_square_sum = 0;
    while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
      $input .= $resu['text'] . " ";
      $length_sum += $resu['words'];
      $length_square_sum += pow($resu['words'], 2);
      $count++;
    }
    $mean = $length_sum / $count;
    $r1 = mt_rand(0, mt_getrandmax()) / mt_getrandmax();
    $r2 = mt_rand(0, mt_getrandmax()) / mt_getrandmax();
    $dev = sqrt(($length_square_sum / $count - ($mean * $mean)));
    $r = $mean + $dev * sqrt(-2 * log($r1)) * cos(2 * pi() * $r2);
    $analysis = w_build_relations($input, 2);
    $output = w_generate_text($analysis, $r);
    $this->markoved = html_entity_decode(trim($output));
  }

}
