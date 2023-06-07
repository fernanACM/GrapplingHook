<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
declare(strict_types=1);

namespace fernanACM\GrapplingHook;

use pocketmine\player\Player;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;

use fernanACM\GrapplingHook\GrapplingHook;

class FishingRod{

	/**
	 * @param Player $player
	 * @return Item
	 */
	public static function giveGrapplingHook(Player $player): Item{
		$grapplingHook = VanillaItems::FISHING_ROD();
		$grapplingHook->setNamedTag(CompoundTag::create()->setString("GrapplingHook", "Fishing_Rod"));
		$grapplingHook->setUnbreakable(true);
		$grapplingHook->setCustomName(GrapplingHook::getMessage($player, "GrapplingHook.name"));
		$grapplingHook->setLore([GrapplingHook::getMessage($player, "GrapplingHook.lore")]);
		$grapplingHook->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING()));
		return $grapplingHook;
	}
}