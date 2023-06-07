<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
declare(strict_types=1);

namespace fernanACM\GrapplingHook\utils;

use pocketmine\player\Player;

use fernanACM\GrapplingHook\GrapplingHook as Loader;

class CooldownUtils{

    /** @var array<string, array{timestamp: int, duration: int}> */
    private static $cooldowns = [];

    /**
     * @param Player $player
     * @param string $id
     * @param integer $duration
     * @return void
     */
    public static function addCooldown(Player $player, string $id, int $duration): void{
        $cooldownData = [
            "player" => $player->getName(),
            "id" => $id,
            "timestamp" => time(),
            "duration" => $duration
        ];
        $plugin = Loader::getInstance();
        $database = $plugin->getProvider()->getDatabase();
        $database->executeGeneric("table.cooldowns");
        $database->executeInsert("table.cooldowns", $cooldownData);
        self::saveCooldown($player->getName(), $id, $cooldownData);
    }

    /**
     * @param Player $player
     * @param string $id
     * @return void
     */
    public static function removeCooldown(Player $player, string $id): void{
        $cooldownData = [
            "player" => $player->getName(),
            "id" => $id
        ];
        $plugin = Loader::getInstance();
        $database = $plugin->getProvider()->getDatabase();
        $database->executeGeneric("table.cooldowns");
        $database->executeChange("table.cooldowns", $cooldownData);
        self::removeCooldownFromCache($player->getName(), $id);
    }

    /**
     * @param Player $player
     * @param string $id
     * @return bool
     */
    public static function hasCooldown(Player $player, string $id): bool{
        return isset(self::$cooldowns[$player->getName()][$id]);
    }

    /**
     * @param Player $player
     * @param string $id
     * @return string|null
     */
    public static function getRemainingTime(Player $player, string $id): ?string{
        $config = Loader::getInstance()->config;
        $cooldownData = self::getCooldownFromCache($player->getName(), $id);
        if(is_null($cooldownData)){
            return null;
        }
        $cooldownEnd = $cooldownData["timestamp"] + $cooldownData["duration"];
        $secondsLeft = $cooldownEnd - time();
        if($secondsLeft <= 0){
            self::removeCooldown($player, $id);
            return null;
        }
        $years = floor($secondsLeft / (365*24*60*60));
        $months = floor($secondsLeft / (30*24*60*60));
        $days = floor(($secondsLeft - $months*30*24*60*60) / (24*60*60));
        $hours = floor(($secondsLeft - $months*30*24*60*60 - $days*24*60*60) / (60*60));
        $minutes = floor(($secondsLeft - $months*30*24*60*60 - $days*24*60*60 - $hours*60*60) / 60);
        $seconds = $secondsLeft - $months*30*24*60*60 - $days*24*60*60 - $hours*60*60 - $minutes*60;
        $output = "";
        if($years > 0){
            $time = str_replace(["{YEAR}"], [$years], $config->getNested("TimeMode.years"));
            $output .= $time." ";
        }
        if($months > 0){
            $time = str_replace(["{MONTH}"], [$months], $config->getNested("TimeMode.months"));
            $output .= $time." ";
        }
        if($days > 0){
            $time = str_replace(["{DAY}"], [$days], $config->getNested("TimeMode.days"));
            $output .= $time." ";
        }
        if($hours > 0){
            $time = str_replace(["{HOUR}"], [$hours], $config->getNested("TimeMode.hours"));
            $output .= $time." ";
        }
        if($minutes > 0) {
            $time = str_replace(["{MINUTE}"], [$minutes], $config->getNested("TimeMode.minutes"));
            $output .= $time." ";
        }
        if($seconds > 0){
            $time = str_replace(["{SECOND}"], [$seconds], $config->getNested("TimeMode.seconds"));
            $output .= $time." ";
        }
        return trim($output);
    }

    /**
     * @param Player $player
     * @param string $id
     * @return void
     */
    public static function startCooldown(Player $player, string $id): void{
        self::addCooldown($player, $id, 0);
    }

    /**
     * @param Player $player
     * @param string $id
     * @return void
     */
    public static function cancelCooldown(Player $player, string $id): void{
        if(self::hasCooldown($player, $id)){
            self::removeCooldown($player, $id);
        }
    }

    /**
     * @return void
     */
    public static function loadCooldownsFromDatabase(): void{
        $plugin = Loader::getInstance();
        $database = $plugin->getProvider()->getDatabase();
        $database->executeGeneric("table.cooldowns");
        $database->executeSelect("table.cooldowns", [], function(array $rows){
            foreach($rows as $row){
                $player = $row["player"];
                $id = $row["id"];
                $cooldownData = [
                    "timestamp" => (int)$row["timestamp"],
                    "duration" => (int)$row["duration"]
                ];
                self::saveCooldown($player, $id, $cooldownData);
            }
        });
    }

    /**
     * @param string $player
     * @param string $id
     * @param array $cooldownData
     * @return void
     */
    public static function saveCooldown(string $player, string $id, array $cooldownData): void {
        self::$cooldowns[$player][$id] = $cooldownData;
    }

    /**
     * @param string $player
     * @param string $id
     * @return void
     */
    public static function removeCooldownFromCache(string $player, string $id): void{
        unset(self::$cooldowns[$player][$id]);
        if (empty(self::$cooldowns[$player])) {
            unset(self::$cooldowns[$player]);
        }
    }

    /**
     * @param string $player
     * @param string $id
     * @return array|null
     */
    public static function getCooldownFromCache(string $player, string $id): ?array{
        return isset(self::$cooldowns[$player][$id]) ? self::$cooldowns[$player][$id] : null;
    }

    /**
     * @return void
     */
    public static function saveCooldownsToDatabase(): void{
        $plugin = Loader::getInstance();
        $database = $plugin->getProvider()->getDatabase();
        foreach(self::$cooldowns as $player => $cooldownData){
            foreach($cooldownData as $id => $cooldown){
                $database->executeGeneric("table.cooldowns");
                $database->executeInsert("table.cooldowns", [
                    $player,
                    $id,
                    $cooldown['timestamp'],
                    $cooldown['duration']
                ]);
            }
        }
    }
}
