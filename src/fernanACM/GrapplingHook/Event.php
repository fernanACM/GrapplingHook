<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
namespace fernanACM\GrapplingHook;

use pocketmine\player\Player;

use pocketmine\event\Listener;

use pocketmine\world\sound\ThrowSound;

use pocketmine\entity\Location;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;

use pocketmine\event\player\PlayerItemUseEvent;

use pocketmine\network\mcpe\protocol\AnimatePacket;

use fernanACM\GrapplingHook\utils\CooldownUtils;
use fernanACM\GrapplingHook\utils\PluginUtils;

class Event implements Listener{

    private const COOLDOWN_ID = "GrapplingHook";
    /**
     * @param EntityDamageEvent $event
     * @return void
     */
	public function onDamage(EntityDamageEvent $event): void{
		$player = $event->getEntity();
		if(!$player instanceof Player || $event->getCause() !== EntityDamageEvent::CAUSE_FALL){
			return;
		}
        $item = $player->getInventory()->getItemInHand();
        $grapplingHook = FishingRod::giveGrapplingHook($player);
        if(!is_null($item->getNamedTag()->getTag("GrapplingHook"))){
            if($player->getInventory()->getItemInHand()->equals($grapplingHook) and $player->hasPermission("grapplinghook.acm")){
                $event->cancel();
            }
        }
	}

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     */
    public function onUse(PlayerItemUseEvent $event): void{
        $player = $event->getPlayer();
        $location = $player->getLocation();
		$world = $player->getWorld();

        if(!$player->hasPermission('grapplinghook.acm')){
            $player->sendMessage(GrapplingHook::Prefix(). GrapplingHook::getMessage($player, "Messages.no-permission"));
            PluginUtils::PlaySound($player, "mob.villager.no", 1, 1);
			return;
		}

        $item = $player->getInventory()->getItemInHand();
        if(!is_null($item->getNamedTag()->getTag(self::COOLDOWN_ID))){
            if(CooldownUtils::hasCooldown($player, self::COOLDOWN_ID)){
                $cooldown = CooldownUtils::getRemainingTime($player, self::COOLDOWN_ID);
                $message = str_replace(["{COOLDOWN}"], [$cooldown], GrapplingHook::getMessage($player, "Messages.you-have-cooldown"));
                $player->sendActionBarMessage($message);
            }
            if(is_null(GrapplingHook::getFishingHook($player))){
                $hook = new FishingHook(Location::fromObject(
                    $player->getEyePos(),
                    $world,
                    ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                    -$location->pitch
                ), $player);
                $ev = new ProjectileLaunchEvent($hook);
                if($ev->isCancelled()) {
                    $hook->flagForDespawn();
                }
                $hook->spawnToAll();
            }else{
                $hook = GrapplingHook::getFishingHook($player);
			    $hook->handleHookRetraction();
                $cooldown = GrapplingHook::getInstance()->config->getNested("GrapplingHook.cooldown");
                CooldownUtils::addCooldown($player, self::COOLDOWN_ID, (int)$cooldown); // COOLDOWN
            }
            $world->broadcastPacketToViewers($location, AnimatePacket::create($player->getId(), AnimatePacket::ACTION_SWING_ARM));
		    $world->addSound($player->getPosition(), new ThrowSound());
        }
    }
}