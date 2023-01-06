<?php

namespace DavyCraft648\Smartphone;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\world\World;
use function array_map;
use function array_values;
use function count;
use function is_numeric;
use function is_string;
use function json_encode;
use function mt_rand;
use function trim;

class SmartphoneForm{

	public static array $waitingFormResponse = [];

	public static function sendHomeForm(Player $player, string $battery) : bool{
		if(isset(SmartphoneForm::$waitingFormResponse[$player->getName()])){
			return false;
		}
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}
			match ($data) {
				0 => self::sendSettingsAppForm($player, $battery),
				1 => self::sendAppStoreAppForm($player, $battery),
				2 => self::sendStoreAppForm($player, $battery),
				3 => self::sendBankAppForm($player, $battery),
				4 => self::sendMusicAppForm($player, $battery),
				5 => self::sendDayNightAppForm($player, $battery),
				6 => self::sendWeatherAppForm($player, $battery),
				7 => self::sendEffectAppForm($player, $battery),
				8 => self::sendDiscraftAppForm($player, $battery),
				default => null
			};
		});
		$form->setTitle("title.smartphone_home");
		$form->setContent("$battery%%");
		$form->addButton("button.settings_app", 0, "textures/icons_apps/settings.png");
		$form->addButton("button.appstore_app", 0, "textures/icons_apps/app_store.png");
		$form->addButton("button.store_app", 0, "textures/icons_apps/store.png");
		$form->addButton("button.bank_app", 0, "textures/icons_apps/bank.png");
		$form->addButton("button.music_app", 0, "textures/icons_apps/music.png");
		$form->addButton("button.daynight_app", 0, "textures/icons_apps/daynight.png");
		$form->addButton("button.weather_app", 0, "textures/icons_apps/weather.png");
		$form->addButton("button.effect_app", 0, "textures/icons_apps/effect.png");
		$form->addButton("button.discraft_app", 0, "textures/icons_apps/discraft.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
		return true;
	}

	public static function sendSettingsAppForm(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}
			switch($data){
				case 0:
					self::sendHomeForm($player, $battery);
					return;
				case 1://Todo: Enable notification
				case 2://Todo: Disable notification
					break;
			}
			self::sendSettingsAppForm($player, $battery);
		});
		$form->setTitle("title.smartphone_settings");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("button.enable_notific", 0, "textures/icons_apps/notific_off.png");
		$form->addButton("button.disable_notific", 0, "textures/icons_apps/notific_on.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendAppStoreAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}
				switch($data){
					case 0:
						self::sendHomeForm($player, $battery);
						return;
					// Todo: Purchase app
				}
				self::sendAppStoreAppForm($player, $battery);
			});
			$purchased_purchase = [];
			foreach(["store_app", "bank_app", "music_app", "daynight_app", "weather_app", "effect_app", "discraft_app"] as $app){
				$purchased_purchase[$app] = "purchased";// Todo: Purchase app
			}
			$form->setTitle("title.smartphone_appstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back_full.png");
			$form->addButton("button.store_app_{$purchased_purchase['store_app']}", 0, "textures/icons_apps/app_store/store_{$purchased_purchase['store_app']}.png");
			$form->addButton("button.bank_app_{$purchased_purchase['bank_app']}", 0, "textures/icons_apps/app_store/bank_{$purchased_purchase['bank_app']}.png");
			$form->addButton("button.music_app_{$purchased_purchase['music_app']}", 0, "textures/icons_apps/app_store/music_{$purchased_purchase['music_app']}.png");
			$form->addButton("button.daynight_app_{$purchased_purchase['daynight_app']}", 0, "textures/icons_apps/app_store/daynight_{$purchased_purchase['daynight_app']}.png");
			$form->addButton("button.weather_app_{$purchased_purchase['weather_app']}", 0, "textures/icons_apps/app_store/weather_{$purchased_purchase['weather_app']}.png");
			$form->addButton("button.effect_app_{$purchased_purchase['effect_app']}", 0, "textures/icons_apps/app_store/effect_{$purchased_purchase['effect_app']}.png");
			$form->addButton("button.discraft_app_{$purchased_purchase['discraft_app']}", 0, "textures/icons_apps/app_store/discraft_{$purchased_purchase['discraft_app']}.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}
				switch($data){
					case 0:
						self::sendHomeForm($player, $battery);
						return;
					case 1:
						self::sendStoreItemsAppForm($player, $battery);
						return;
					case 2:
						$pk = new TextPacket();
						$pk->type = TextPacket::TYPE_JSON;
						$pk->message = '{"rawtext":[{"translate":"text.coming_soon"}]}';
						$player->getNetworkSession()->sendDataPacket($pk);
						return;
				}
			});
			$form->setTitle("title.smartphone_store§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.items", 0, "textures/ui/icon_recipe_item.png");
			$form->addButton("button.blocks", 0, "textures/ui/icon_recipe_construction.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}
				match ($data) {
					0 => self::sendStoreAppForm($player, $battery),
					1 => self::sendStoreItemsFoodsAppForm($player, $battery),
					2 => self::sendStoreItemsDropsAppForm($player, $battery),
					3 => self::sendStoreItemsArmorsAppForm($player, $battery),
					4 => self::sendStoreItemsWeaponsAppForm($player, $battery),
				};
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.foods", 0, "textures/icons_apps/store_app/foods.png");
			$form->addButton("button.drops", 0, "textures/icons_apps/store_app/drops.png");
			$form->addButton("button.armors", 0, "textures/icons_apps/store_app/armors.png");
			$form->addButton("button.weapons", 0, "textures/icons_apps/store_app/weapons.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsFoodsAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 2, 3, 4, 5, 6, 7, 14, 16 => 5,
					8, 9, 10 => 10,
					11, 12 => 2,
					13, 15 => 20
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::STEAK(),
						2 => VanillaItems::COOKED_MUTTON(),
						3 => VanillaItems::COOKED_CHICKEN(),
						4 => VanillaItems::COOKED_RABBIT(),
						5 => VanillaItems::COOKED_PORKCHOP(),
						6 => VanillaItems::COOKED_FISH(),
						7 => VanillaItems::COOKED_SALMON(),
						8 => VanillaItems::MUSHROOM_STEW(),
						9 => VanillaItems::RABBIT_STEW(),
						10 => VanillaItems::BEETROOT_SOUP(),
						11 => VanillaItems::BAKED_POTATO(),
						12 => VanillaItems::COOKIE(),
						13 => VanillaItems::PUMPKIN_PIE(),
						14 => VanillaItems::APPLE(),
						15 => VanillaBlocks::CAKE()->asItem(),
						16 => VanillaItems::BREAD()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsFoodsAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enough_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.beef_cooked", 0, "textures/items/beef_cooked.png");
			$form->addButton("button.mutton_cooked", 0, "textures/items/mutton_cooked.png");
			$form->addButton("button.chicken_cooked", 0, "textures/items/chicken_cooked.png");
			$form->addButton("button.rabbit_cooked", 0, "textures/items/rabbit_cooked.png");
			$form->addButton("button.porkchop_cooked", 0, "textures/items/porkchop_cooked.png");
			$form->addButton("button.fish_cooked", 0, "textures/items/fish_cooked.png");
			$form->addButton("button.fish_salmon_cooked", 0, "textures/items/fish_salmon_cooked.png");
			$form->addButton("button.mushroom_stew", 0, "textures/items/mushroom_stew.png");
			$form->addButton("button.rabbit_stew", 0, "textures/items/rabbit_stew.png");
			$form->addButton("button.beetroot_soup", 0, "textures/items/beetroot_soup.png");
			$form->addButton("button.potato_baked", 0, "textures/items/potato_baked.png");
			$form->addButton("button.cookie", 0, "textures/items/cookie.png");
			$form->addButton("button.pumpkin_pie", 0, "textures/items/pumpkin_pie.png");
			$form->addButton("button.apple", 0, "textures/items/apple.png");
			$form->addButton("button.cake", 0, "textures/items/cake.png");
			$form->addButton("button.bread", 0, "textures/items/bread.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsDropsAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 2 => 1,
					3, 10, 14, 15, 16 => 5,
					4, 12 => 2,
					5, 8 => 10,
					6, 11 => 20,
					7 => 15,
					9 => 50,
					13 => 100
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::ROTTEN_FLESH(),
						2 => VanillaItems::BONE(),
						3 => VanillaItems::SPIDER_EYE(),
						4 => VanillaItems::STRING(),
						5 => VanillaItems::GUNPOWDER(),
						6 => VanillaItems::BLAZE_ROD(),
						7 => VanillaItems::MAGMA_CREAM(),
						8 => VanillaItems::ENDER_PEARL(),
						9 => VanillaItems::PHANTOM_MEMBRANE(),
						10 => VanillaItems::SLIMEBALL(),
						11 => VanillaItems::GHAST_TEAR(),
						12 => VanillaItems::FEATHER(),
						13 => VanillaItems::SHULKER_SHELL(),
						14 => VanillaItems::LEATHER(),
						15 => VanillaItems::RABBIT_HIDE(),
						16 => VanillaItems::RABBIT_FOOT()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsDropsAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.rotten_flesh", 0, "textures/items/rotten_flesh.png");
			$form->addButton("button.bone", 0, "textures/items/bone.png");
			$form->addButton("button.spider_eye", 0, "textures/items/spider_eye.png");
			$form->addButton("button.string", 0, "textures/items/string.png");
			$form->addButton("button.gunpowder", 0, "textures/items/gunpowder.png");
			$form->addButton("button.blaze_rod", 0, "textures/items/blaze_rod.png");
			$form->addButton("button.magma_cream", 0, "textures/items/magma_cream.png");
			$form->addButton("button.ender_pearl", 0, "textures/items/ender_pearl.png");
			$form->addButton("button.phantom_membrane", 0, "textures/items/phantom_membrane.png");
			$form->addButton("button.slime_ball", 0, "textures/items/slimeball.png");
			$form->addButton("button.ghast_tear", 0, "textures/items/ghast_tear.png");
			$form->addButton("button.feather", 0, "textures/items/feather.png");
			$form->addButton("button.shulker_shell", 0, "textures/items/shulker_shell.png");
			$form->addButton("button.leather", 0, "textures/items/leather.png");
			$form->addButton("button.rabbit_hide", 0, "textures/items/rabbit_hide.png");
			$form->addButton("button.rabbit_foot", 0, "textures/items/rabbit_foot.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsArmorsAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsAppForm($player, $battery);
					return;
				}
				match ($data) {
					1 => self::sendStoreItemsArmorsIronAppForm($player, $battery),
					2 => self::sendStoreItemsArmorsGoldAppForm($player, $battery),
					3 => self::sendStoreItemsArmorsDiamondAppForm($player, $battery),
					4 => self::sendStoreItemsArmorsNetheriteAppForm($player, $battery)
				};
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.iron_armor", 0, "textures/items/iron_chestplate.png");
			$form->addButton("button.gold_armor", 0, "textures/items/gold_chestplate.png");
			$form->addButton("button.diamond_armor", 0, "textures/items/diamond_chestplate.png");
			$form->addButton("button.netherite_armor", 0, "textures/items/netherite_chestplate.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsArmorsIronAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsArmorsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1 => 50,
					2 => 80,
					3 => 70,
					4 => 40
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::IRON_HELMET(),
						2 => VanillaItems::IRON_CHESTPLATE(),
						3 => VanillaItems::IRON_LEGGINGS(),
						4 => VanillaItems::IRON_BOOTS()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsArmorsIronAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.iron_helmet", 0, "textures/items/iron_helmet.png");
			$form->addButton("button.iron_chestplate", 0, "textures/items/iron_chestplate.png");
			$form->addButton("button.iron_leggings", 0, "textures/items/iron_leggings.png");
			$form->addButton("button.iron_boots", 0, "textures/items/iron_boots.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsArmorsGoldAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsArmorsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1 => 125,
					2 => 200,
					3 => 175,
					4 => 100
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::GOLDEN_HELMET(),
						2 => VanillaItems::GOLDEN_CHESTPLATE(),
						3 => VanillaItems::GOLDEN_LEGGINGS(),
						4 => VanillaItems::GOLDEN_BOOTS()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsArmorsGoldAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.gold_helmet", 0, "textures/items/gold_helmet.png");
			$form->addButton("button.gold_chestplate", 0, "textures/items/gold_chestplate.png");
			$form->addButton("button.gold_leggings", 0, "textures/items/gold_leggings.png");
			$form->addButton("button.gold_boots", 0, "textures/items/gold_boots.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsArmorsDiamondAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsArmorsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1 => 250,
					2 => 400,
					3 => 350,
					4 => 200
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::DIAMOND_HELMET(),
						2 => VanillaItems::DIAMOND_CHESTPLATE(),
						3 => VanillaItems::DIAMOND_LEGGINGS(),
						4 => VanillaItems::DIAMOND_BOOTS()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsArmorsDiamondAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.diamond_helmet", 0, "textures/items/diamond_helmet.png");
			$form->addButton("button.diamond_chestplate", 0, "textures/items/diamond_chestplate.png");
			$form->addButton("button.diamond_leggings", 0, "textures/items/diamond_leggings.png");
			$form->addButton("button.diamond_boots", 0, "textures/items/diamond_boots.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsArmorsNetheriteAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsArmorsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1 => 2500,
					2 => 4000,
					3 => 3500,
					4 => 2000
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::NETHERITE_HELMET(),
						2 => VanillaItems::NETHERITE_CHESTPLATE(),
						3 => VanillaItems::NETHERITE_LEGGINGS(),
						4 => VanillaItems::NETHERITE_BOOTS()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsArmorsNetheriteAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.netherite_helmet", 0, "textures/items/netherite_helmet.png");
			$form->addButton("button.netherite_chestplate", 0, "textures/items/netherite_chestplate.png");
			$form->addButton("button.netherite_leggings", 0, "textures/items/netherite_leggings.png");
			$form->addButton("button.netherite_boots", 0, "textures/items/netherite_boots.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsWeaponsAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsAppForm($player, $battery);
					return;
				}
				match ($data) {
					1 => self::sendStoreItemsWeaponsIronAppForm($player, $battery),
					2 => self::sendStoreItemsWeaponsGoldAppForm($player, $battery),
					3 => self::sendStoreItemsWeaponsDiamondAppForm($player, $battery),
					4 => self::sendStoreItemsWeaponsNetheriteAppForm($player, $battery)
				};
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.iron_weapons", 0, "textures/items/iron_sword.png");
			$form->addButton("button.gold_weapons", 0, "textures/items/gold_sword.png");
			$form->addButton("button.diamond_weapons", 0, "textures/items/diamond_sword.png");
			$form->addButton("button.netherite_weapons", 0, "textures/items/netherite_sword.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsWeaponsIronAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsWeaponsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 4 => 20,
					2 => 39,
					3 => 30
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::IRON_SWORD(),
						2 => VanillaItems::IRON_PICKAXE(),
						3 => VanillaItems::IRON_AXE(),
						4 => VanillaItems::IRON_SHOVEL()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsWeaponsIronAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.iron_sword", 0, "textures/items/iron_sword.png");
			$form->addButton("button.iron_pickaxe", 0, "textures/items/iron_pickaxe.png");
			$form->addButton("button.iron_axe", 0, "textures/items/iron_axe.png");
			$form->addButton("button.iron_shovel", 0, "textures/items/iron_shovel.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsWeaponsGoldAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsWeaponsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 4 => 50,
					2, 3 => 75
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::GOLDEN_SWORD(),
						2 => VanillaItems::GOLDEN_PICKAXE(),
						3 => VanillaItems::GOLDEN_AXE(),
						4 => VanillaItems::GOLDEN_SHOVEL()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsWeaponsGoldAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.gold_sword", 0, "textures/items/gold_sword.png");
			$form->addButton("button.gold_pickaxe", 0, "textures/items/gold_pickaxe.png");
			$form->addButton("button.gold_axe", 0, "textures/items/gold_axe.png");
			$form->addButton("button.gold_shovel", 0, "textures/items/gold_shovel.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsWeaponsDiamondAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsWeaponsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 4 => 100,
					2, 3 => 150
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::DIAMOND_SWORD(),
						2 => VanillaItems::DIAMOND_PICKAXE(),
						3 => VanillaItems::DIAMOND_AXE(),
						4 => VanillaItems::DIAMOND_SHOVEL()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsWeaponsDiamondAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.diamond_sword", 0, "textures/items/diamond_sword.png");
			$form->addButton("button.diamond_pickaxe", 0, "textures/items/diamond_pickaxe.png");
			$form->addButton("button.diamond_axe", 0, "textures/items/diamond_axe.png");
			$form->addButton("button.diamond_shovel", 0, "textures/items/diamond_shovel.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendStoreItemsWeaponsNetheriteAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}elseif($data === 0){
					self::sendStoreItemsWeaponsAppForm($player, $battery);
					return;
				}
				BedrockEconomyAPI::beta()->deduct($player->getName(), match ($data) {
					1, 4 => 1000,
					2, 3 => 1500
				})->onCompletion(function(?bool $_) use ($battery, $data, $player) : void{
					$player->getInventory()->addItem(match ($data) {
						1 => VanillaItems::NETHERITE_SWORD(),
						2 => VanillaItems::NETHERITE_PICKAXE(),
						3 => VanillaItems::NETHERITE_AXE(),
						4 => VanillaItems::NETHERITE_SHOVEL()
					});
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.item_purchased_sucessfully"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.levelup", $pos->x, $pos->y, $pos->z, 1, 1));
					self::sendStoreItemsWeaponsNetheriteAppForm($player, $battery);
				}, function() use ($player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.not_enought_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
				});
			});
			$form->setTitle("title.smartphone_itemsstore§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.netherite_sword", 0, "textures/items/netherite_sword.png");
			$form->addButton("button.netherite_pickaxe", 0, "textures/items/netherite_pickaxe.png");
			$form->addButton("button.netherite_axe", 0, "textures/items/netherite_axe.png");
			$form->addButton("button.netherite_shovel", 0, "textures/items/netherite_shovel.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendBankAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}
				match ($data) {
					0 => self::sendHomeForm($player, $battery),
					1 => self::sendBankTransferAppForm($player, $battery),
					2 => self::sendBankDepositeAppForm($player, $battery)
				};
			});
			$form->setTitle("title.smartphone_bank§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.back", 0, "textures/icons_apps/back.png");
			$form->addButton("button.transfer_money", 0, "textures/icons_apps/transfer.png");
			$form->addButton("button.deposite_money", 0, "textures/icons_apps/deposite.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendBankTransferAppForm(Player $player, string $battery) : void{
		$onlinePlayers = array_map(fn(Player $p) => $p->getName(), array_values($player->getServer()->getOnlinePlayers()));
		$form = new CustomForm(function(Player $player, ?array $data) use ($onlinePlayers, $battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}
			if(is_numeric($data[1])){
				$transfer = (int) $data[1];
				if($transfer < 1){
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.you_cant_send_negative_money"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					return;
				}
				if($onlinePlayers[$data[0]] === $player->getName()){
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.you_cant_send_money_to_yourself"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					return;
				}

				BedrockEconomyAPI::beta()->transfer($player->getName(), $onlinePlayers[$data[0]], $transfer)->onCompletion(function(?bool $_) use ($transfer, $player) : void{
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.transfer_sucessfull"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					$pos = $player->getLocation();
					$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.notification", $pos->x, $pos->y, $pos->z, 1, 1)); // Todo: Disable notification
					$player->getNetworkSession()->sendDataPacket(SetTitlePacket::create(
						SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE_JSON,
						"{\"rawtext\":[{\"translate\":\"text_smartphoneaddon.message\"},{\"text\":\"\n\"},{\"text\":\" {$player->getName()} \"},{\"translate\":\"text_smartphoneaddon.made_a_transfer\"},{\"text\":\" §2$$transfer \"},{\"translate\":\"text_smartphoneaddon.for_you\"}]}",
						0, 0, 0, "", ""
					));
				}, function() : void{ });
			}
		});
		$form->setTitle("title.smartphone_bank $battery%%");
		$form->addDropdown("text.players_online", $onlinePlayers);
		$form->addInput("text.quantity", "text.type_here");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendBankDepositeAppForm(Player $player, string $battery) : void{
		BedrockEconomyAPI::beta()->get($player->getName())->onCompletion(function(int $balance) use ($player, $battery) : void{
			$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
				unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
				if($data === null){
					return;
				}
				$item = match ($data) {
					0 => VanillaItems::IRON_INGOT(),
					1 => VanillaItems::GOLD_INGOT(),
					2 => VanillaItems::DIAMOND(),
					3 => VanillaItems::EMERALD(),
				};
				if(count($player->getInventory()->all($item)) === 0){
					$pk = new TextPacket();
					$pk->type = TextPacket::TYPE_JSON;
					$pk->message = '{"rawtext":[{"translate":"text.you_dont_have_this_item"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					return;
				}
				BedrockEconomyAPI::beta()->add($player->getName(), match ($data) {
					0 => 10,
					1 => 25,
					2 => 50,
					3 => 100,
				})->onCompletion(function(?bool $_) use ($battery, $item, $player) : void{
					$player->getInventory()->removeItem($item);
					SmartphoneForm::sendBankDepositeAppForm($player, $battery);
				}, function() : void{ });
			});
			$form->setTitle("title.smartphone_deposite§2$$balance");
			$form->setContent("$battery%%");
			$form->addButton("button.iron_ingot", 0, "textures/items/iron_ingot.png");
			$form->addButton("button.gold_ingot", 0, "textures/items/gold_ingot.png");
			$form->addButton("button.diamond", 0, "textures/items/diamond.png");
			$form->addButton("button.emerald", 0, "textures/items/emerald.png");
			$player->sendForm($form);
		}, function() : void{ });
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendMusicAppForm(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}
			match ($data) {
				0 => self::sendHomeForm($player, $battery),
				1 => self::sendMusicAppAlbum1Form($player, $battery),
			};
		});
		$form->setTitle("title.smartphone_music");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("Minecraft Musics\n§8C418, Lena Raine... - Album", 0, "textures/icons_apps/music_app/minecraft_volume_alpha.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendMusicAppAlbum1Form(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}elseif($data === 0){
				self::sendHomeForm($player, $battery);
				return;
			}
			$pos = $player->getLocation()->asVector3();
			// $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::STOP_RECORD, $pos, true));
			$player->getNetworkSession()->sendDataPacket(StopSoundPacket::create("", true));
			match ($data) {
				1 => null,
				2 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_PIGSTEP, $pos, true)),
				3 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_13, $pos, true)),
				4 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_CAT, $pos, true)),
				5 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_BLOCKS, $pos, true)),
				6 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_CHIRP, $pos, true)),
				7 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_FAR, $pos, true)),
				8 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_MALL, $pos, true)),
				9 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_MELLOHI, $pos, true)),
				10 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_STAL, $pos, true)),
				11 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_STRAD, $pos, true)),
				12 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_WARD, $pos, true)),
				13 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_WAIT, $pos, true)),
				14 => $player->getNetworkSession()->sendDataPacket(LevelSoundEventPacket::nonActorSound(LevelSoundEvent::RECORD_OTHERSIDE, $pos, true))
			};
		});
		$form->setTitle("title.smartphone_music");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("button.stop_music", 0, "textures/icons_apps/cancel.png");
		$form->addButton("Pigstep\n§8Lena Raine", 0, "textures/items/record_pigstep.png");
		$form->addButton("Thirteen (Disc 13)\n§8C418", 0, "textures/items/record_13.png");
		$form->addButton("Cat\n§8C418", 0, "textures/items/record_cat.png");
		$form->addButton("Blocks\n§8C418", 0, "textures/items/record_blocks.png");
		$form->addButton("Chirp\n§8C418", 0, "textures/items/record_chirp.png");
		$form->addButton("Far\n§8C418", 0, "textures/items/record_far.png");
		$form->addButton("Mall\n§8C418", 0, "textures/items/record_mall.png");
		$form->addButton("Mellohi\n§8C418", 0, "textures/items/record_mellohi.png");
		$form->addButton("Stal\n§8C418", 0, "textures/items/record_stal.png");
		$form->addButton("Strad\n§8C418", 0, "textures/items/record_strad.png");
		$form->addButton("Ward\n§8C418", 0, "textures/items/record_ward.png");
		$form->addButton("Wait\n§8C418", 0, "textures/items/record_wait.png");
		$form->addButton("Otherside\n§8Lena Raine", 0, "textures/items/record_otherside.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendDayNightAppForm(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}elseif($data === 0){
				self::sendHomeForm($player, $battery);
				return;
			}

			match ($data) {
				1 => $player->getWorld()->setTime(World::TIME_MIDNIGHT),
				2 => $player->getWorld()->setTime(World::TIME_NIGHT),
				3 => $player->getWorld()->setTime(World::TIME_SUNSET),
				4 => $player->getWorld()->setTime(World::TIME_NOON),
				5 => $player->getWorld()->setTime(World::TIME_SUNRISE),
				6 => $player->getWorld()->setTime(World::TIME_DAY)
			};
			self::sendDayNightAppForm($player, $battery);
		});
		$form->setTitle("title.smartphone_daynight");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("button.midnight", 0, "textures/icons_apps/day_night_app/midnight.png");
		$form->addButton("button.night", 0, "textures/icons_apps/day_night_app/night.png");
		$form->addButton("button.sunset", 0, "textures/icons_apps/day_night_app/sunset.png");
		$form->addButton("button.noon", 0, "textures/icons_apps/day_night_app/noon.png");
		$form->addButton("button.sunrise", 0, "textures/icons_apps/day_night_app/sunrise.png");
		$form->addButton("button.day", 0, "textures/icons_apps/day_night_app/day.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendWeatherAppForm(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}elseif($data === 0){
				self::sendHomeForm($player, $battery);
				return;
			}

			$player->getNetworkSession()->sendDataPacket(LevelEventPacket::create(LevelEvent::STOP_RAIN, 0, null));
			$player->getNetworkSession()->sendDataPacket(LevelEventPacket::create(LevelEvent::STOP_THUNDER, 0, null));
			match ($data) {
				1 => null,
				2 => $player->getNetworkSession()->sendDataPacket(LevelEventPacket::create(LevelEvent::START_RAIN, mt_rand(90000, 100000), null)),
				3 => $player->getNetworkSession()->sendDataPacket(LevelEventPacket::create(LevelEvent::START_THUNDER, mt_rand(90000, 100000) + 10000, null))
			};
			self::sendWeatherAppForm($player, $battery);
		});
		$form->setTitle("title.smartphone_daynight");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("button.clear", 0, "textures/icons_apps/weather_app/clear.png");
		$form->addButton("button.rain", 0, "textures/icons_apps/weather_app/rain.png");
		$form->addButton("button.storm", 0, "textures/icons_apps/weather_app/storm.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendEffectAppForm(Player $player, string $battery) : void{
		$form = new SimpleForm(function(Player $player, ?int $data) use ($battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}elseif($data === 0){
				self::sendHomeForm($player, $battery);
				return;
			}

			match ($data) {
				1 => $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 30, 5)),
				2 => $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 30, 5)),
				3 => $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 30, 5)),
				4 => $player->getEffects()->add(new EffectInstance(VanillaEffects::HASTE(), 20 * 30, 5)),
				5 => $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 30, 5)),
				6 => $player->getEffects()->add(new EffectInstance(VanillaEffects::WATER_BREATHING(), 20 * 30, 5))
			};
			self::sendEffectAppForm($player, $battery);
		});
		$form->setTitle("title.smartphone_effect");
		$form->setContent("$battery%%");
		$form->addButton("button.back", 0, "textures/icons_apps/back.png");
		$form->addButton("button.regeneration", 0, "textures/ui/regeneration_effect.png");
		$form->addButton("button.speed", 0, "textures/ui/speed_effect.png");
		$form->addButton("button.jump_boost", 0, "textures/ui/jump_boost_effect.png");
		$form->addButton("button.haste", 0, "textures/ui/haste_effect.png");
		$form->addButton("button.night_vision", 0, "textures/ui/night_vision_effect.png");
		$form->addButton("button.water_breathing", 0, "textures/ui/water_breathing_effect.png");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}

	public static function sendDiscraftAppForm(Player $player, string $battery) : void{
		$onlinePlayers = array_map(fn(Player $p) => $p->getName(), array_values($player->getServer()->getOnlinePlayers()));
		$form = new CustomForm(function(Player $player, ?array $data) use ($onlinePlayers, $battery) : void{
			unset(SmartphoneForm::$waitingFormResponse[$player->getName()]);
			if($data === null){
				return;
			}
			if(is_string($data[1]) && trim($data[1]) !== ""){
				$message = $data[1];
				$pk = new TextPacket();
				$pk->type = TextPacket::TYPE_JSON;
				if($onlinePlayers[$data[0]] === $player->getName()){
					$pk->message = '{"rawtext":[{"translate":"text.you_cant_send_a_message_to_yourself"}]}';
					$player->getNetworkSession()->sendDataPacket($pk);
					return;
				}

				$pk->message = json_encode([
					"rawtext" => [
						["translate" => "text.from_you"],
						["text" => "§r{$onlinePlayers[$data[0]]}"],
						["text" => "> "],
						["text" => $message]
					]
				]);
				$player->getNetworkSession()->sendDataPacket($pk);
				$target = $player->getServer()->getPlayerExact($onlinePlayers[$data[0]]);
				$pk2 = new TextPacket();
				$pk2->type = TextPacket::TYPE_JSON;
				$pk2->message = json_encode([
					"rawtext" => [
						["translate" => "text.from"],
						["text" => " §r{$player->getName()} "],
						["translate" => "text.to_you"],
						["text" => $message]
					]
				]);
				$target->getNetworkSession()->sendDataPacket($pk2);
				$pos = $target->getLocation();
				$target->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("random.notification", $pos->x, $pos->y, $pos->z, 1, 1));// Todo: Disable notification
				$target->getNetworkSession()->sendDataPacket(SetTitlePacket::create(
					SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE_JSON,
					json_encode([
						"rawtext" => [
							["translate" => "text_smartphoneaddon.message"],
							["text" => "\n"],
							["text" => " {$player->getName()}"],
							["translate" => "text_smartphoneaddon.send_a_message"]
						]
					]),
					0, 0, 0, "", ""
				));
			}
		});
		$form->setTitle("title.smartphone_discraft $battery%%");
		$form->addDropdown("text.players_online", $onlinePlayers);
		$form->addInput("text.message", "text.type_here");
		$player->sendForm($form);
		SmartphoneForm::$waitingFormResponse[$player->getName()] = true;
	}
}
