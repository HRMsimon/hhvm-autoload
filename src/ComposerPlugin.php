<?php

namespace FredEmmott\AutoloadMap;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class ComposerPlugin
  implements PluginInterface, EventSubscriberInterface {

  private $vendor;
  private $root;
  public function activate(Composer $composer, IOInterface $io) {
    var_dump('activating');
    $vendor = $composer->getConfig()->get('vendor-dir', '/');

    $this->vendor = $vendor;
    $this->root = dirname($vendor);
/*
    $composer->getEventDispatcher()->addListener(
      ScriptEvents::POST_AUTOLOAD_DUMP,
      function($event) { $this->onPostAutoloadDump($event); }
    );
    */
  }

  public static function getSubscribedEvents() {
    return [
      ScriptEvents::POST_AUTOLOAD_DUMP => [
        ['onPostAutoloadDump', 0],
      ],
    ];
  }

  public function onPostAutoloadDump(Event $event) {
    var_dump('writing dump');
    require_once(__DIR__.'/unsupported/AutoTypecheckGuard.php');
    $typechecker_guard = new __UNSUPPORTED__\AutoTypecheckGuard();
    require_once($this->vendor.'/autoload.php');

    $importer = new RootImporter($this->root);
    
    (new Writer())
      ->setBuilder($importer)
      ->setRoot($this->root)
      ->writeToFile($this->vendor.'/hh_autoload.php');
  }
}
