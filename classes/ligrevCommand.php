<?php

/**
 * Template class for any ligrev :commands
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class ligrevCommand {

  protected $client;
  protected $rooms;
  protected $origin;
  protected $stanza;
  protected $from;
  protected $text;
  protected $author;
  protected $room;

  function __construct(\XMPPStanza $stanza, $origin) {
    global $client, $rooms, $roster, $db, $config;
    $this->client = $client;
    $this->rooms = $rooms;

    $this->origin = $origin;
    $this->stanza = $stanza;

    $this->from = new \XMPPJid($stanza->from);
    if ($this->from->resource) {
      if (!$this->stanza->exists('delay', NS_DELAYED_DELIVERY)) {
        l("[" . $this->from->node . "] " . $this->from->resource . (($this->origin == "chat") ? " (PM)" : "") . ": " . $this->stanza->body);
        $this->text = $this->stanza->body;
        $this->room = $this->from->bare;
        $this->author = $this->from->resource;

        // log it. for 'science'
        if ($config['log']) {
          $jid = new \XMPPJid($roster->nickToJid($this->room, $this->author));
          $jid = $jid->bare;
          $length = count(explode(" ", $this->text));
          $query = 'INSERT INTO markov (user, words, text) VALUES ('
            . $db->quote($jid, 'text') . ', '
            . $db->quote($length, 'integer') . ', '
            . $db->quote($this->text, 'text') . ')';
          $db->exec($query);
        }
      } else {
        l("[MUC] Rec'd message (delayed)");
      }
    }
  }

}
