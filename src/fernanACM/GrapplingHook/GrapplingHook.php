<?php
// thank you very much for helping JackNoordhuis

namespace fernanACM\GrapplingHook;

use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;

class GrapplingHook extends PluginBase implements Listener {

	private static $fishing = [];

	public static function getFishingHook(Player $player) : ?FishingHook {
		return self::$fishing[$player->getName()] ?? null;
	}

	public static function setFishingHook(?FishingHook $fish, Player $player) {
		self::$fishing[$player->getName()] = $fish;
	}

	public function onEnable() : void {
		ItemFactory::getInstance()->register(new FishingRod(new ItemIdentifier(ItemIds::FISHING_ROD, 0), 'Fishing Rod'), true);

//		EntityFactory::getInstance()->register(FishingHook::class, function(World $world, CompoundTag $nbt, Entity $owner) : FishingHook {
//			return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), $owner, $nbt);
//		}, ['FishingHook', 'minecraft:fishinghook'], EntityLegacyIds::FISHING_HOOK);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDamage(EntityDamageEvent $event) : void {
		$player = $event->getEntity();
		if(!$player instanceof Player || $event->getCause() !== EntityDamageEvent::CAUSE_FALL) {
			return;
		}

		if($player->getInventory()->getItemInHand()->getId() === ItemIds::FISHING_ROD and $player->hasPermission("grapplinghook.acm")) {
			$event->cancel();
		}
	}

}
