<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
namespace fernanACM\GrapplingHook\commands;

use pocketmine\player\Player;

use pocketmine\command\CommandSender;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\IntegerArgument;

use fernanACM\GrapplingHook\FishingRod;
use fernanACM\GrapplingHook\GrapplingHook;
use fernanACM\GrapplingHook\utils\PluginUtils;

class GrapplingHookCommand extends BaseCommand{

    public function __construct(){
        parent::__construct(GrapplingHook::getInstance(), "grapplinghook", "The best Grapplinghook to play Spider-Man by fernanACM", ["ghook", "gph"]);
        $this->setPermission("grapplinghook.acm");
    }
    /**
     * @return void
     */
    protected function prepare(): void{
        $this->registerArgument(0, new IntegerArgument("amount", true));
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     * @return void
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(!$sender instanceof Player){
            $sender->sendMessage("Use this command in-game");
            return;
        }

        if(!$sender->hasPermission("grapplinghook.acm")){
            $sender->sendMessage(GrapplingHook::Prefix(). GrapplingHook::getMessage($sender, "Messages.no-permission"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }
        
        $amount = $args["amount"] ?? 1;
        if(!is_numeric($amount)){
            $sender->sendMessage(GrapplingHook::Prefix(). GrapplingHook::getMessage($sender, "Messages.not-numeric"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }
        $grapplingHook = FishingRod::giveGrapplingHook($sender);
        if(!$sender->getInventory()->canAddItem($grapplingHook)){
            $sender->sendMessage(GrapplingHook::Prefix(). GrapplingHook::getMessage($sender, "Messages.full-inventory"));
            PluginUtils::PlaySound($sender, "mob.villager.no", 1, 1);
            return;
        }
        $sender->getInventory()->addItem($grapplingHook->setCount($amount));
        $sender->sendMessage(GrapplingHook::Prefix(). GrapplingHook::getMessage($sender, "Messages.received successfully"));
        PluginUtils::PlaySound($sender, "random.levelup", 1, 4.2);
    }
}