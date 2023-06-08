<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
namespace fernanACM\GrapplingHook\provider;

use pocketmine\utils\Config;

use fernanACM\GrapplingHook\GrapplingHook;

class ProviderDataStorage{

    /** @var Config $data */
    private Config $data;

    private const YAML = "data/PlayerData.yml";
    private const JSON = "data/PlayerData.json";
    private const ARCHIVE = "data/PlayerData";

    /**
     * @return void
     */
    public function loadPlayerData(): void{
        $plugin = GrapplingHook::getInstance();
        $dataStorage = strtolower($plugin->config->getNested("Provider.data-storage"));
        switch($dataStorage){
            case "json":
                $resourcePath = self::JSON;
            break;
    
            case "yml":
            case "yaml":
                $resourcePath = self::YAML;
            break;
    
            default:
                $resourcePath = self::JSON;
            break;
        }
        $plugin->saveResource($resourcePath);
        $filePath = $plugin->getDataFolder() . $resourcePath;
        $this->data = new Config($filePath);
    }
    
    /**
     * @param array $data
     * @return void
     */
    public function saveDataToFile(array $data): void{
        $plugin = GrapplingHook::getInstance();
        $filePath = $plugin->getDataFolder() . self::ARCHIVE;
        $format = strtolower($plugin->config->getNested("Provider.data-storage"));
        switch($format){
            case "json":
                $filePath .= ".json";
                $formattedData = json_encode($data);
                break;
            case "yml":
            case "yaml":
                $filePath .= ".yml";
                $formattedData = yaml_emit($data);
                break;
            default:
                $filePath .= ".json";
                $formattedData = json_encode($data);
            return;
        }
        file_put_contents($filePath, $formattedData);
    }

    /**
     * @return array|null
     */
    public static function loadDataToFile(): ?array{
        $plugin = GrapplingHook::getInstance();
        $filePath = $plugin->getDataFolder() . self::ARCHIVE;
        $extension = strtolower($plugin->getConfig()->getNested("Provider.data-storage"));
        switch($extension){
            case "json":
                $filePath .= ".json";
                $fileData = file_get_contents($filePath);
                $cooldownData = json_decode($fileData, true);
                break;
            case "yml":
            case "yaml":
                $filePath .= ".yml";
                $fileData = file_get_contents($filePath);
                $cooldownData = yaml_parse($fileData);
                break;
            default:
            return null;
        }
        return $cooldownData;
    }
    

    /**
     * @return Config
     */
    public function getData(): Config{
        return $this->data;
    }
}