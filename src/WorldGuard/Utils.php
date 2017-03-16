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
* By Muqsit Rayyan.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Twitter: @muqsitrayyan
* GitHub: https://github.com/Muqsit
*/

namespace WorldGuard;

use pocketmine\Player;
use pocketmine\network\protocol\SetPlayerGameTypePacket;

class Utils {

    const GAMEMODES = [
        "0" => 0,
        "s" => 0,
        "survival" => 0,
        "1" => 1,
        "c" => 1,
        "creative" => 1,
        "2" => 2,
        "a" => 2,
        "adventure" => 2,
        "3" => 3,
        "sp" => 3,
        "spectator" => 3
    ];

    const GM2STRING = [
        0 => "survival",
        1 => "creative",
        2 => "adventure",
        3 => "spectator"
    ];

    public static function getRomanNumber(int $integer, $upcase = true) : string
    {
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1);
        $return = '';
        while($integer > 0) {
            foreach($table as $rom=>$arb) {
                if($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }

    public static function disableFlight(Player $player)
    {
        $player->setAllowFlight(false);
        $pk = new SetPlayerGameTypePacket();
        $pk->gamemode = $player->gamemode & 0x01;
        $player->dataPacket($pk);
        $player->setFlying(false);
        $player->sendSettings();
    }

    public static function gm2string(int $gm) : string
    {
        return self::GM2STRING[$gm] ?? "survival";
    }
}
