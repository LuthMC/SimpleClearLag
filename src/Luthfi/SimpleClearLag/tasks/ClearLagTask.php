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
    private $elapsedTime = 0;

    public function __construct(Main $plugin, int $interval) {
        $this->plugin = $plugin;
        $this->interval = $interval;
    }

    public function onRun(): void {
        $this->elapsedTime += 1;

        $warnings = [300, 60, 30, 5, 4, 3, 2, 1];

        if (in_array($this->interval - $this->elapsedTime, $warnings)) {
            $message = $this->plugin->getWarningMessage($this->interval - $this->elapsedTime);
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
        }

        if ($this->elapsedTime >= $this->interval) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                $this->clearEntities($world);
            }
            $message = $this->plugin->getClearMessage();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
            $this->elapsedTime = 0;
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
