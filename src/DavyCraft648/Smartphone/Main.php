<?php

namespace DavyCraft648\Smartphone;

use customiesdevs\customies\item\CustomiesItemFactory;
use DavyCraft648\Smartphone\item\Smartphone;
use DavyCraft648\Smartphone\item\SmartphonePM4;
use DavyCraft648\Smartphone\item\SmartphonePM5;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\VanillaItems;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\scheduler\ClosureTask;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;

final class Main extends \pocketmine\plugin\PluginBase{

	protected function onLoad() : void{
		$this->saveResource("Smartphones RE.mcpack");
		$newPack = new ZippedResourcePack(Path::join($this->getDataFolder(), "Smartphones RE.mcpack"));
		$rpManager = $this->getServer()->getResourcePackManager();
		$resourcePacks = new \ReflectionProperty($rpManager, "resourcePacks");
		$resourcePacks->setAccessible(true);
		$resourcePacks->setValue($rpManager, array_merge($resourcePacks->getValue($rpManager), [$newPack]));
		$uuidList = new \ReflectionProperty($rpManager, "uuidList");
		$uuidList->setAccessible(true);
		$uuidList->setValue($rpManager, $uuidList->getValue($rpManager) + [strtolower($newPack->getPackId()) => $newPack]);
		$serverForceResources = new \ReflectionProperty($rpManager, "serverForceResources");
		$serverForceResources->setAccessible(true);
		$serverForceResources->setValue($rpManager, true);
		CustomiesItemFactory::getInstance()->registerItem(VersionInfo::BASE_VERSION[0] === "5" ? SmartphonePM5::class : SmartphonePM4::class, "smartphone:black_smartphone", "Smartphone");
	}

	protected function onEnable() : void{
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void{
			$this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
				[
					"AC",
					"BD",
					"AA"
				],
				[
					"A" => VersionInfo::BASE_VERSION[0] === "5" ? new ExactRecipeIngredient(VanillaItems::IRON_INGOT()) : VanillaItems::IRON_INGOT(),
					"B" => VersionInfo::BASE_VERSION[0] === "5" ? new ExactRecipeIngredient(VanillaBlocks::GLASS_PANE()->asItem()) : VanillaBlocks::GLASS_PANE()->asItem(),
					"C" => VersionInfo::BASE_VERSION[0] === "5" ? new ExactRecipeIngredient(VanillaItems::REDSTONE_DUST()) : VanillaItems::REDSTONE_DUST(),
					"D" => VersionInfo::BASE_VERSION[0] === "5" ? new ExactRecipeIngredient(VanillaItems::DYE()->setColor(DyeColor::BLACK())) : VanillaItems::BLACK_DYE()
				],
				[CustomiesItemFactory::getInstance()->get("smartphone:black_smartphone")]
			));
		}), 2);
	}
}
