<?php

namespace DavyCraft648\Smartphone\item;

use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class SmartphonePM4 extends Smartphone{
	/** @noinspection PhpHierarchyChecksInspection */
	public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
		return $this->openScreen($player);
	}
}
