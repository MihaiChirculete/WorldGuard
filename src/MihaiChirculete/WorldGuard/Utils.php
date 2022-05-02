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

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\entity\Entity;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\world\biome\Biome;

class Utils {

    const GAMEMODES = [
        "0","s" => "survival",
        "1","c" => "creative",
        "2","a" => "adventure",
        "3","sp" => "spectator",
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
        $player->setFlying(false);
    }

    public static function gm2string(int $gm) : string
    {
        return self::GM2STRING[$gm] ?? "survival";
    }

    /**
     * @param Player $player
     * @param string $msg
     * @return mixed
     *
     * Use this to parse aliases in a string
     */
    public static function aliasParse(Player $player, string $msg)
    {
        $parsedMsg = str_replace("{player_name}", $player->getName() ,$msg);
        $parsedMsg = str_replace("&", "§", $parsedMsg);

        return $parsedMsg;
    }


    /**
     * @param Entity $ent
     * @return bool
     *
     * Pass an entity to this function and it checks if its an animal or not.
     */
     
    public static function isAnimal(Entity $ent)
    {
        if($ent instanceof Animal)
            return true;
        $classname = strtolower(get_class($ent));
        if(str_contains($classname, "bat") !== false ||
            str_contains($classname, "chicken") !== false ||
            str_contains($classname, "cow") !== false ||
            str_contains($classname, "donkey") !== false ||
            str_contains($classname, "horse") !== false ||
            str_contains($classname, "llama") !== false ||
            str_contains($classname, "mooshroom") !== false ||
            str_contains($classname, "mule") !== false ||
            str_contains($classname, "ocelot") !== false ||
            str_contains($classname, "parrot") !== false ||
            str_contains($classname, "pig") !== false ||
            str_contains($classname, "polarbear") !== false ||
            str_contains($classname, "rabbit") !== false ||
            str_contains($classname, "sheep") !== false ||
            str_contains($classname, "wolf") !== false ||
            strpos($classname, "animal"))
            return true;
        return false;
    }

    /**
     * @param string $classname
     * @return bool
     *
     * Pass an entity to this function and it checks if its a monster or not.
     */
    
    public static function isMonster(Entity $ent)
    {
        if($ent instanceof Monster)
            return true;
        $classname = strtolower(get_class($ent));
        if(str_contains($classname, "blaze") !== false ||
            str_contains($classname, "cavespider") !== false ||
            str_contains($classname, "elderguardian") !== false ||
            str_contains($classname, "enderdragon") !== false ||
            str_contains($classname, "enderman") !== false ||
            str_contains($classname, "endermite") !== false ||
            str_contains($classname, "evoker") !== false ||
            str_contains($classname, "ghast") !== false ||
            str_contains($classname, "guardian") !== false ||
            str_contains($classname, "husk") !== false ||
            str_contains($classname, "magmacube") !== false ||
            str_contains($classname, "pigzombie") !== false ||
            str_contains($classname, "shulker") !== false ||
            str_contains($classname, "silverfish") !== false ||
            str_contains($classname, "skeleton") !== false ||
            str_contains($classname, "slime") !== false ||
            str_contains($classname, "spider") !== false ||
            str_contains($classname, "stray") !== false ||
            str_contains($classname, "undead") !== false ||
            str_contains($classname, "vex") !== false ||
            str_contains($classname, "vindicator") !== false ||
            str_contains($classname, "witch") !== false ||
            str_contains($classname, "wither") !== false ||
            str_contains($classname, "witherskeleton") !== false ||
            str_contains($classname, "zombievillager") !== false ||
            strpos($classname, "monster"))
            return true;
        return false;
    }

    private static function biomeParse($biomeName) : int
    {
        $biomeName = strtolower($biomeName);
        switch ($biomeName){
            case "ocean":
                return BiomeIDs::OCEAN;
                break;
                
            case "plains":
                return BiomeIDs::PLAINS;
                break;
                
            case "desert":
                return BiomeIDs::DESERT;
                break;
                
             case "mountains":
                return BiomeIDs::EXTREME_HILLS;
                break;
                
            case "forest":
                return BiomeIDs::FOREST;
                break;
                
            case "taiga":
                return BiomeIDs::TAIGA;
                break;
            
            case "swamp":
                return BiomeIDs::SWAMPLAND;
                break;
                
            case "river":
                return BiomeIDs::RIVER;
                break;
                
            case "hell":
                return BiomeIDs::HELL;
                break;
                
            case "ice_plains":
                return BiomeIDs::ICE_PLAINS;
                break;

            case "small_mountain":
                return BiomeIDs::EXTREME_HILLS_EDGE;
                break;
                
            case "birch_forest":
                return BiomeIDs::BIRCH_FOREST;
                break;
        }
    }

    public static function setBiome($plugin, Region $reg, $biomeName)
    {
        $pos1 = $reg->getPos1();
        $pos2 = $reg->getPos2();

        $x1 = $pos1[0];
        $z1 = $pos1[2];

        $x2 = $pos2[0];
        $z2 = $pos2[2];

        if($x1>$x2)
        {
            $tmp = $x1;
            $x1 = $x2;
            $x2 = $tmp;
        }

        if($z1>$z2)
        {
            $tmp = $z1;
            $z1 = $z2;
            $z2 = $tmp;
        }

        for ($i=$x1; $i<=$x2; $i++)
            for($j=$z1; $j<=$z2; $j++)
                $plugin->getServer()->getWorldManager()->getWorldByName()($reg->getLevelName())->setBiomeId($i, $j, self::biomeParse($biomeName));
    }

    // given an issuer object, returns the plugin object
    // useful for static functions when you need the plugin refference
    public static function getPluginFromIssuer(Player $issuer)
    {
        return $issuer->getServer()->getPluginManager()->getPlugin("WorldGuard");
    }
}
