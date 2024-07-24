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
    private $nextClearTime;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->interval = $this->plugin->getConfig()->get("interval", 5) * 60;
        $this->nextClearTime = time() + $this->interval;
    }

    public function onRun(): void {
        $currentTime = time();
        $timeLeft = $this->nextClearTime - $currentTime;

        $warnings = [300, 60, 30, 5, 4, 3, 2, 1];
        if (in_array($timeLeft, $warnings)) {
            $message = $this->plugin->getWarningMessage($timeLeft);
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
        }

        if ($timeLeft <= 0) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                $this->clearEntities($world);
            }
            $message = $this->plugin->getClearMessage();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage($message);
            }
            $this->nextClearTime = $currentTime + $this->interval;
        }
    }

    private function clearEntities(World $world): void {
        foreach ($world->getEntities() as $entity) {
            if ($this->isProtectedEntity($entity)) {
                continue;
            }
            $entity->flagForDespawn();
        }
    }

    private function isProtectedEntity(Entity $entity): bool {
        if ($entity instanceof Player) {
            return true;
        }
        
        $protectedEntityTags = ["HumanNPC", "Slapper"];
        foreach ($protectedEntityTags as $tag) {
            if ($entity->namedtag->hasTag($tag)) {
                return true;
            }
        }

        return $entity instanceof ItemEntity;
    }
}
