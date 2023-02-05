<?php

namespace DavyCraft648\Smartphone\item;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponentsTrait;
use DavyCraft648\Smartphone\SmartphoneForm;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\player\Player;

abstract class Smartphone extends \pocketmine\item\Durable implements \customiesdevs\customies\item\ItemComponents{
	use ItemComponentsTrait;

	private ?string $owner = null;

	public function __construct(ItemIdentifier $identifier, string $name = "Unknown"){
		parent::__construct($identifier, $name);
		$this->initComponent("black_smartphone", new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT));
	}

	public function getMaxDurability() : int{
		return 100;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function openScreen(Player $player) : ItemUseResult{
		if(($this->getMaxDurability() - $this->damage) <= 0){
			$player->getNetworkSession()->sendDataPacket(SetTitlePacket::create(
				SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE_JSON,
				'{"rawtext":[{"translate":"text.smartphone.no_battery"}]}',
				0, 0, 0, "", ""
			));
			return ItemUseResult::FAIL();
		}
		if($this->owner !== null && $this->owner !== $player->getName()){
			return ItemUseResult::FAIL();
		}

		$batteryColor = "§r§c ";
		if($this->damage <= 10){
			$batteryColor = "§r§a ";
		}elseif($this->damage <= 20){
			$batteryColor = "§r§a ";
		}elseif($this->damage <= 30){
			$batteryColor = "§r§a ";
		}elseif($this->damage <= 40){
			$batteryColor = "§r§a ";
		}elseif($this->damage <= 50){
			$batteryColor = "§r§e ";
		}elseif($this->damage <= 60){
			$batteryColor = "§r§e ";
		}elseif($this->damage <= 70){
			$batteryColor = "§r§e ";
		}elseif($this->damage <= 80){
			$batteryColor = "§r§c ";
		}elseif($this->damage <= 90){
			$batteryColor = "§r§c ";
		}

		$lore = $this->getLore();
		$this->setLore([
			"",
			$lore[1] ?? "§r§7Owner: {$player->getName()}",
			$battery = ($batteryColor . ($this->getMaxDurability() - $this->damage))
		]);
		if(!SmartphoneForm::sendHomeForm($player, $battery)){
			return ItemUseResult::FAIL();
		}
		$this->applyDamage(1);
		return ItemUseResult::SUCCESS();
	}

	public function getOwner() : ?string{
		return $this->owner;
	}

	public function setOwner(?string $owner) : void{
		$this->owner = $owner;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		$owner = $tag->getString("smartphone:owner", "");
		if($owner !== ""){
			$this->owner = $owner;
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		$this->owner !== null ? $tag->setString("smartphone:owner", $this->owner) : $tag->removeTag("smartphone:owner");
	}

	protected function onBroken() : void{
		// do nothing
	}
}
