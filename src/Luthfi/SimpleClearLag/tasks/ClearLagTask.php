<?php

namespace Luthfi\SimpleClearLag\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Luthfi\SimpleClearLag\Main;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;

class ClearLagTask extends Task {

    /** @var Main */
    private $plugin;
    private $interval;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->interval = $this->plugin->getConfig()->get("interval", 5) * 60;
    }

    public function onRun(): void {
        $warnings = [300, 60, 30, 5, 4, 3, 2, 1];
        $currentTime = time() % $this->interval;

        if (in_array($currentTime, $warnings)) {
            $message = $this->plugin->getWarningMessage($this->interval - $currentTime);
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
        }

        if ($currentTime === 0) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                $this->clearEntities($world);
            }
            $message = $this->plugin->getClearMessage();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
        }
    }

    private function clearEntities(World $world): void {
        foreach ($world->getEntities() as $entity) {
            if (!$entity instanceof Player) {
                $entity->flagForDespawn();
            }
        }
        foreach ($world->getEntities() as $entity) {
            if ($entity instanceof ItemEntity) {
                $entity->flagForDespawn();
            }
        }
    }
}
