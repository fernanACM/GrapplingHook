<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\GrapplingHook;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\world\World;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

# Libs
use muqsit\simplepackethandler\SimplePacketHandler;

use DaPigGuy\libPiggyUpdateChecker\libPiggyUpdateChecker;

use CortexPE\Commando\PacketHooker;
use CortexPE\Commando\BaseCommand;
# My files
use fernanACM\GrapplingHook\commands\GrapplingHookCommand;

use fernanACM\GrapplingHook\provider\ProviderDataStorage;
use fernanACM\GrapplingHook\utils\CooldownUtils;
use fernanACM\GrapplingHook\utils\PluginUtils;

use fernanACM\GrapplingHook\Event;

class GrapplingHook extends PluginBase{

    /** @var Config $config */
    public Config $config;

    /** @var GrapplingHook $instance */
    private static GrapplingHook $instance;

    /** @var ProviderDataStorage $provider */
    private ProviderDataStorage $provider;

    /** @var array $fishing */
	private static array $fishing = [];

    # CheckConfig
    public const CONFIG_VERSION = "1.0.0";

    /**
     * @return void
     */
    public function onLoad(): void{
        $this->loadVars();
        $this->loadFiles();
    }

    /**
     * @return void
     */
    public function onEnable(): void{
        $this->loadCheck();
        $this->loadVirions();
        $this->loadEvents();
        $this->loadCommands();
        $this->loadEntitites();
        CooldownUtils::loadCooldownsFromFile();
    }

    /**
     * @return void
     */
    public function onDisable(): void{
        CooldownUtils::saveCooldownsToFile();
    }

    /**
     * @return void
     */
    public function loadFiles(): void{
        @mkdir($this->getDataFolder(). "data");
        $this->saveResource("config.yml");
	    $this->config = new Config($this->getDataFolder() . "config.yml");
    }

    /**
     * @return void
     */
    public function loadCheck(): void{
        # CONFIG
        if((!$this->config->exists("config-version")) || ($this->config->get("config-version") != self::CONFIG_VERSION)){
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config_old.yml");
            $this->saveResource("config.yml");
            $this->getLogger()->critical("Your configuration file is outdated.");
            $this->getLogger()->notice("Your old configuration has been saved as config_old.yml and a new configuration file has been generated. Please update accordingly.");
        }
    }

    /**
     * @return void
     */
    public function loadVirions(): void{
        foreach([
            "SimplePacketHandler" => SimplePacketHandler::class,
            "Commando" => BaseCommand::class,
            "libPiggyUpdateChecker" => libPiggyUpdateChecker::class
            ] as $virion => $class
        ){
            if(!class_exists($class)){
                $this->getLogger()->error($virion . " virion not found. Please download GrapplingHook from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }
        if(!PacketHooker::isRegistered()){
            PacketHooker::register($this);
        }
        # Database
        $this->getProvider()->loadPlayerData();
        # Update
        libPiggyUpdateChecker::init($this);
    }
    
    /**
     * @return void
     */
    public function loadCommands(): void{
        Server::getInstance()->getCommandMap()->register("grapplinghook", new GrapplingHookCommand);
    }

    public function loadEntitites(): void{
        EntityFactory::getInstance()->register(FishingHook::class, function(World $world, CompoundTag $nbt): FishingHook{
            return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['FishingHook', 'minecraft:fishinghook'], EntityIds::FISHING_HOOK);
    }

    /**
     * @return void
     */
    private function loadVars(): void{
        self::$instance = $this;
        $this->provider = new ProviderDataStorage();
    }

    /**
     * @return void
     */
    public function loadEvents(): void{
        Server::getInstance()->getPluginManager()->registerEvents(new Event, $this);
    }

    /**
     * @param Player $player
     * @return FishingHook|null
     */
    public static function getFishingHook(Player $player): ?FishingHook{
		return self::$fishing[$player->getName()] ?? null;
	}

    /**
     * @param FishingHook|null $fish
     * @param Player $player
     * @return void
     */
	public static function setFishingHook(?FishingHook $fish, Player $player): void{
		self::$fishing[$player->getName()] = $fish;
	}
    
    /**
     * @return GrapplingHook
     */
    public static function getInstance(): GrapplingHook{
        return self::$instance;
    }

    /**
     * @return ProviderDataStorage
     */
    public function getProvider(): ProviderDataStorage{
        return $this->provider;
    }

    /**
     * @param Player $player
     * @param string $key
     * @return string
     */
    public static function getMessage(Player $player, string $key): string{
        $messageArray = self::$instance->config->getNested($key, []);
        if(!is_array($messageArray)){
            $messageArray = [$messageArray];
        }
        $message = implode("\n", $messageArray);
        return PluginUtils::codeUtil($player, $message);
    }

    /**
     * @return string
     */
    public static function Prefix(): string{
        return TextFormat::colorize(self::$instance->config->get("Prefix"));
    }
}