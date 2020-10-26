<?php

/**
*
*  _     _  _______  ______    ___      ______   _______  __   __  _______  ______    ______  
* | | _ | ||       ||    _ |  |   |    |      | |       ||  | |  ||   _   ||    _ |  |      | 
* | || || ||   _   ||   | ||  |   |    |  _    ||    ___||  | |  ||  |_|  ||   | ||  |  _    |
* |       ||  | |  ||   |_||_ |   |    | | |   ||   | __ |  |_|  ||       ||   |_||_ | | |   |
* |       ||  |_|  ||    __  ||   |___ | |_|   ||   ||  ||       ||       ||    __  || |_|   |
* |   _   ||       ||   |  | ||       ||       ||   |_| ||       ||   _   ||   |  | ||       |
* |__| |__||_______||___|  |_||_______||______| |_______||_______||__| |__||___|  |_||______| 
*
* By MihaiChirculete.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* GitHub: https://github.com/MihaiChirculete
*/

namespace MihaiChirculete\WorldGuard;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\{Effect, EffectInstance};

class Region {

    private $pos1 = [];
    private $pos2 = [];
    private $levelname = "";
    private $name = "";
    private $flags = [];

    private $vector1;
    private $vector2;
    private $level;
    private $effects = [];

    public function __construct(string $name, array $pos1, array $pos2, string $level, array $flags)
    {
        $this->name = $name;
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->levelname = $level;

        foreach (WorldGuard::FLAGS as $k => $v) {
            if (!isset($flags[$k])) $flags[$k] = $v;
        }
        $this->flags = $flags;

        foreach ($this->flags["effects"] as $id => $amplifier) {
            $this->effects[$id] = new EffectInstance(Effect::getEffect($id), 999999999, $amplifier, false);
        }

        $this->vector1 = new Vector3(...$pos1);
        $this->vector2 = new Vector3(...$pos2);
        $this->level = Server::getInstance()->getLevelByName($level);
    }

    public function getPos1() : array
    {
        return $this->pos1;
    }

    public function getPos2() : array
    {
        return $this->pos2;
    }

    public function getVectorPoint1() : Vector3
    {
        return $this->vector1;
    }

    public function getVectorPoint2() : Vector3
    {
        return $this->vector2;
    }

    public function getLevelName() : string
    {
        return $this->levelname;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getLevel() : Level
    {
        return $this->level;
    }

    public function getFlags() : array
    {
        return $this->flags;
    }

    public function getFlag(string $flag)
    {
        //TODO: Move priority from returning the region name, to just returning the value of the flag on the highest priority region.
        return $this->flags[$flag];
    }

    public function setFlag(string $flag, array $avalue)
    {
        $value = $avalue[0];

        if ($flag === "effects") {
            if (!is_numeric($value)) {
                return TF::RED."Value of effect flag must be numeric.";
            }
            if (!$value > 0) {
                $this->flags["effects"] = [];
                return TF::YELLOW.'All "effects" (of "'.$this->name.'") would be removed.';
            }
            if (isset($avalue[1])) {
                if (is_numeric($avalue[1])) {
                    $this->flags["effects"][$value] = $avalue[1];
                    $effectType = Effect::getEffect($value);
                    $this->effects[$value] = new EffectInstance($effectType, 999999999, --$avalue[1], false);
                    return TF::YELLOW.'Added "'.($this->effects[$value])->getType()->getName().' '.Utils::getRomanNumber(++$avalue[1]).'" effect to "'.$this->name.'" region.';
                } else {
                    return TF::RED."Amplifier must be numerical.\n".TF::GRAY.'Example: /region flags set '.$this->name.' '.$value.' 1';
                }
            } else {
                $this->flags["effects"][$value] = 0;
                $this->effects[$value] = new EffectInstance(Effect::getEffect($value), 999999999, 0);
                return TF::YELLOW.'Added "'.($this->effects[$value])->getId().'" effect to "'.$this->name.'" region.';
            }
            return;
        }

        switch (WorldGuard::FLAG_TYPE[$flag]) {
            case "integer":
                if ($flag === "fly-mode") {
                    if ($value < 0 || $value > 3) {
                        return implode("\n", [
                            TF::RED.'Flight flag should be either 0, 1, 2 or 3.',
                            TF::GRAY.'0 => No changes to flight. Vanilla behaviour.',
                            TF::GRAY.'1 => Enable flight.',
                            TF::GRAY.'2 => Disable flight.',
                            TF::GRAY.'3 => Enable flight, but disable it when the player leaves the area.'
                        ]);
                    }
                    $this->flags["fly-mode"] = (int)$value;
                    return TF::YELLOW.'"Flight mode" (of "'.$this->name.'") was changed to: '.$value.'.';
                }
                break;
            case "boolean":
                if ($value !== "true" && $value !== "false") {
                    return TF::RED.'Value of "'.$flag.'" must either be "true" or "false"';
                }
                break;
            case "array":
                if (!is_string($value)) {
                    return TF::RED.'Value of '.$flag.' must be a string.';
                }
                $this->flags[$flag][$value] = "";
                if ($flag === "game-mode") {
                    $gm = Utils::GAMEMODES[$value] ?? 0;
                    $this->flags["game-mode"] = $gm;
                    return TF::YELLOW.$this->name."'s gamemode has been set to ".Utils::gm2string($gm).'.';
                }
                return;
        }

        if ($flag === "notify-enter" || $flag === "notify-leave") {
            $this->flags[$flag] = implode(" ", str_replace("&","§",$avalue));
        }
        if ($flag === "console-cmd-on-enter" || $flag === "console-cmd-on-leave") {
            $this->flags[$flag] = implode(" ", $avalue);
        }
        else {
            $this->flags[$flag] = str_replace("&","§",$value);
        }
        return TF::YELLOW.'Flag "'.$flag.'" (of "'.$this->name.'") has been updated to "'.$this->flags[$flag].'".';
    }

    public function resetFlag(string $flag)
    {
        $this->flags[$flag] = WorldGuard::FLAGS[$flag];
    }

    public function getBlockedCmds() : string
    {
        $blocked = $this->flags["blocked-cmds"];
        return empty($blocked) ? "none" : "[".implode(", ", array_keys($blocked))."]";
    }

    public function getAllowedCmds() : string
    {
        $allowed = $this->flags["allowed-cmds"];
        return empty($allowed) ? "none" : "[".implode(", ", array_keys($allowed))."]";
    }

    public function getEffectsString()
    {
        return empty($effects = $this->flags["effects"]) ? "none" : implode(", ", $effects);
    }

    public function getFlagsString(): string
    {
        $array = [];
        foreach ($this->flags as $flag => $value) {
            switch ($flag) {
                case "blocked-cmds":
                    $array[] = $flag.' => '.TF::GRAY.$this->getBlockedCmds();
                    break;
                case "allowed-cmds":
                    $array[] = $flag.' => '.TF::GRAY.$this->getAllowedCmds();
                    break;
                case "effects":
                    $array[] = $flag.' => '.TF::GRAY.$this->getEffectsString();
                    break;
                default:
                    $array[] = $flag.' => '.TF::GRAY.'"'.$value.'"';
                    break;
            }
        }
        return TF::LIGHT_PURPLE.implode(TF::WHITE.', '.TF::LIGHT_PURPLE, $array);
    }

    public function isCommandAllowed(string $command) : bool
    {
        if (empty($allowed = $this->flags["allowed-cmds"])) {
            if (!empty($blocked = $this->flags["blocked-cmds"])) {
                return !isset($blocked[$command]);
            }
            return true;
        }
        return isset($allowed[$command]);
    }

    public function getEffects() : array
    {
        return $this->effects;
    }

    public function getGamemode() : string
    {
        return $this->flags["game-mode"];
    }

    public function getFlight() : int
    {
        return $this->flags["fly-mode"];
    }

    public function toArray() : array
    {
        return ["pos1" => $this->pos1, "pos2" => $this->pos2, "level" => $this->levelname, "flags" => $this->flags];
    }
}
