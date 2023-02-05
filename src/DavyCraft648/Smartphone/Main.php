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
use pocketmine\plugin\DisablePluginException;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\scheduler\ClosureTask;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;

final class Main extends \pocketmine\plugin\PluginBase{

	private bool $loadFailed;
	public static bool $enableNetheriteItems = true;

	protected function onLoad() : void{
		if($this->loadFailed = !$this->checkRequirement()){
			return;
		}
		$this->saveResource("Smartphones RE.mcpack");
		$rpManager = $this->getServer()->getResourcePackManager();
		$rpManager->setResourceStack($rpManager->getResourceStack() + [new ZippedResourcePack(Path::join($this->getDataFolder(), "Smartphones RE.mcpack"))]);
		($serverForceResources = new \ReflectionProperty($rpManager, "serverForceResources"))->setAccessible(true);
		$serverForceResources->setValue($rpManager, true);
		CustomiesItemFactory::getInstance()->registerItem(VersionInfo::BASE_VERSION[0] === "5" ? SmartphonePM5::class : SmartphonePM4::class, "smartphone:black_smartphone", "Smartphone");
	}

	private function checkRequirement() : bool{
		$method = new \ReflectionMethod(Smartphone::class . "::initComponent");
		if($method->getParameters()[1]->getType()->getName() === "int"){
			$this->getLogger()->warning("You are using an outdated version of Customies.");
			$this->getLogger()->warning("Please download the latest pm" . VersionInfo::BASE_VERSION[0] . " Customies from https://poggit.pmmp.io/ci/DavyCraft648/Customies-NG/Customies");
			return false;
		}
		$plManager = $this->getServer()->getPluginManager();
		// pm5 already has netherite items
		if(VersionInfo::BASE_VERSION[0] === "4" && $plManager->getPlugin("Netherite") === null && $plManager->getPlugin("VanillaX") === null){
			$this->getLogger()->notice("You need Netherite plugin (by Wertzui123) or VanillaX plugin (by CLADevs) to use netherite items");
			self::$enableNetheriteItems = false;
		}
		return true;
	}

	protected function onEnable() : void{
		if($this->loadFailed){
			throw new DisablePluginException();
		}
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
