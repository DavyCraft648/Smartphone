<?php

namespace DavyCraft648\Smartphone\item;

use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class SmartphonePM5 extends Smartphone{
	/** @noinspection PhpHierarchyChecksInspection */
	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		return $this->openScreen($player);
	}
}
