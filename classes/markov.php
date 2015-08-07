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

    $query = 'SELECT * FROM markov WHERE user='.$db->quote($from, 'text').';';
    $res = & $db->query($query);
    $input = "";
    while (($resu = $res->fetchRow(MDB2_FETCHMODE_ASSOC))) {
      $input .= $resu['text']." ";
    }
    $analysis = w_build_relations($input, 2);
    $output = w_generate_text($analysis, 20);
    $this->markoved = $output;
  }

}
