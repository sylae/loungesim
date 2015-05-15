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
    global $client, $rooms;
    $this->client = $client;
    $this->rooms = $rooms;

    $this->origin = $origin;
    $this->stanza = $stanza;

    $this->from = new \XMPPJid($stanza->from);
    if ($this->from->resource) {
      if (!$this->stanza->exists('delay', NS_DELAYED_DELIVERY)) {
        l("[" . $this->from->bare . "] " . $this->from->resource . (($this->origin == "chat") ? " (PM)" : "") . ": " . $this->stanza->body);
        $this->text = $this->stanza->body;
        $this->room = $this->from->bare;
        $this->author = $this->from->resource;
      } else {
        l("[MUC] Rec'd message (delayed)");
      }
      $preg = "/^[\/:!](\w+)(\s|$)/";
      if (preg_match($preg, $this->text, $match) && class_exists("Ligrev\\" . $match[1])) {
        $class = "Ligrev\\" . $match[1];
        $command = new $class($stanza, $this->origin);
        $command->process();
      }
    }
  }

}