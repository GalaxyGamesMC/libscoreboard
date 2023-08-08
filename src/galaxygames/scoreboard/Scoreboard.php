<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 *  /\___/\
 *  )     (     @author Arisify
 *  \     /
 *   )   (      @link   https://github.com/Arisify
 *  /     \     @license https://opensource.org/licenses/MIT MIT License
 *  )     (
 * /       \
 * \       /
 *  \__ __/
 *     ))
 *    //
 *   ((
 *    \)
*/
declare(strict_types=1);

namespace galaxygames\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class Scoreboard{
    use SingletonTrait;

    public const MIN_SCORE = 1;
    public const MAX_SCORE = 15;

    protected array $scoreboards = [];
    protected array $change_entries = [];
    protected array $remove_entries = [];

    public function create(Player $player, string $objectiveName, string $displayName) : self{
        if (isset($this->scoreboards[$player->getName()])) {
            $this->removeObjective($player);
        }
        $player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, $objectiveName, $displayName, "dummy", SetDisplayObjectivePacket::SORT_ORDER_ASCENDING));
        $this->scoreboards[$player->getName()] = $objectiveName;
        return $this;
    }

    public function getObjectiveName(Player $player) : ?string{
        return $this->scoreboards[$player->getName()] ?? null;
    }

    public function setDisplayName(Player $player, string $displayName) : self{
        if (!isset($this->scoreboards[$player->getName()])) {
            return $this;
        }
        $player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, $this->scoreboards[$player->getName()], $displayName, "dummy", SetDisplayObjectivePacket::SORT_ORDER_ASCENDING));
        return $this;
    }

    public function removeObjective(Player $player) : void{
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }
        $pk = RemoveObjectivePacket::create($this->scoreboards[$player->getName()]);
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->clearPlayerCache($player);
    }

    public function setLine(PLayer $player, int $line, string $message) : self{
        if ($line < self::MIN_SCORE || $line > self::MAX_SCORE || !isset($this->scoreboards[$player->getName()])) {
            return $this;
        }

        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->scoreboards[$player->getName()];
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $line;
        $entry->scoreboardId = $line;

        $this->change_entries[$player->getName()][] = $entry;
        return $this;
    }

    public function removeLine(PLayer $player, int $line) : self{
        if ($line < self::MIN_SCORE || $line > self::MAX_SCORE || !isset($this->scoreboards[$player->getName()])) {
            return $this;
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->getObjectiveName($player);
        $entry->score = $line;
        $entry->scoreboardId = $line;

        $this->remove_entries[$player->getName()][] = $entry;
        return $this;
    }

    public function floodLine(Player $player, int $start = self::MIN_SCORE, int $end = self::MAX_SCORE, string $flood = "") : self{
        while ($start <= $end) {
            $this->setLine($player, $start++, $flood);
        }

        return $this;
    }

    public function update(Player $player) : self{
        if (!empty($this->change_entries[$player->getName()])) {
            $player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_CHANGE, $this->change_entries));

        }
        if (!empty($this->remove_entries[$player->getName()])) {
            $player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_REMOVE, $this->remove_entries));
        }
        unset($this->change_entries[$player->getName()], $this->change_entries[$player->getName()]);
        return $this;
    }

    public function clearPlayerCache(Player $player) : void{
        unset($this->scoreboards[$player->getName()], $this->change_entries[$player->getName()], $this->remove_entries[$player->getName()]);
    }

    public function clearCache() : void{
        unset($this->scoreboards, $this->change_entries, $this->remove_entries);
    }
}
