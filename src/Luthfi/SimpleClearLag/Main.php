<?php

namespace Luthfi\SimpleClearLag;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\Config;
use Luthfi\SimpleClearLag\tasks\ClearLagTask;

class Main extends PluginBase {

    /** @var Config */
    private $config;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $interval = $this->config->get("interval", 5) * 60;

        $this->getScheduler()->scheduleRepeatingTask(new ClearLagTask($this, $interval), 20);
    }

    public function getClearMessage(): string {
        return $this->config->get("clear_message", "Entities and items have been cleared!");
    }

    public function getWarningMessage(int $seconds): string {
        $time = $this->formatTime($seconds);
        $message = $this->config->get("warning_message", "Clearing entities and items in {time}");
        return str_replace("{time}", $time, $message);
    }

    private function formatTime(int $seconds): string {
        if ($seconds >= 60) {
            return ($seconds / 60) . " minutes";
        } elseif ($seconds > 1) {
            return $seconds . " seconds";
        } else {
            return $seconds . " second";
        }
    }
}
