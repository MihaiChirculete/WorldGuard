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

use pocketmine\data\bedrock\BiomeIds;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\entity\{Entity};
use pocketmine\world\biome\Biome;

class Utils
{

    const GAMEMODES = [
        "0" => "survival",
        "s" => "survival",
        "1", "c" => "creative",
        //"c" => "creative",
        "2" => "adventure",
        "a" => "adventure",
        "3" => "spectator",
        "sp" => "spectator",
    ];

    public static function getRomanNumber(int $integer, $upcase = true): string
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
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

    public static function gm2string(int $gm): string
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
        $parsedMsg = str_replace("{player_name}", $player->getName(), $msg);
        $parsedMsg = str_replace("&", "§", $parsedMsg);

        return $parsedMsg;
    }


    /**
     * @param Entity $ent
     * @return bool
     *
     * Pass an entity to this function and it checks if its an animal or not.
     */
    public static function isAnimal(Entity $ent): bool
    {
        if ($ent instanceof Animal)
            return true;

        $classname = strtolower(get_class($ent));

        if (str_contains($classname, "bat") ||
            str_contains($classname, "chicken") ||
            str_contains($classname, "cow") ||
            str_contains($classname, "donkey") ||
            str_contains($classname, "horse") ||
            str_contains($classname, "llama") ||
            str_contains($classname, "mooshroom") ||
            str_contains($classname, "mule") ||
            str_contains($classname, "ocelot") ||
            str_contains($classname, "parrot") ||
            str_contains($classname, "pig") ||
            str_contains($classname, "polarbear") ||
            str_contains($classname, "rabbit") ||
            str_contains($classname, "sheep") ||
            str_contains($classname, "wolf") ||
            strpos($classname, "animal")
        )
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
        if ($ent instanceof Monster)
            return true;

        $classname = strtolower(get_class($ent));

        if (str_contains($classname, "blaze") ||
            str_contains($classname, "cavespider") ||
            str_contains($classname, "elderguardian") ||
            str_contains($classname, "enderdragon") ||
            str_contains($classname, "enderman") ||
            str_contains($classname, "endermite") ||
            str_contains($classname, "evoker") ||
            str_contains($classname, "ghast") ||
            str_contains($classname, "guardian") ||
            str_contains($classname, "husk") ||
            str_contains($classname, "magmacube") ||
            str_contains($classname, "pigzombie") ||
            str_contains($classname, "shulker") ||
            str_contains($classname, "silverfish") ||
            str_contains($classname, "skeleton") ||
            str_contains($classname, "slime") ||
            str_contains($classname, "spider") ||
            str_contains($classname, "stray") ||
            str_contains($classname, "undead") ||
            str_contains($classname, "vex") ||
            str_contains($classname, "vindicator") ||
            str_contains($classname, "witch") ||
            str_contains($classname, "wither") ||
            str_contains($classname, "witherskeleton") ||
            str_contains($classname, "zombievillager") ||
            strpos($classname, "monster")
        )
            return true;

        return false;
    }

    private static function biomeParse($biomeName): int
    {
        $biomeName = strtolower($biomeName);
        switch ($biomeName) {
            case "hell":
            case "birch_forest":
                return BiomeIds::BIRCH_FOREST;
                break;

            case "desert":
                return BiomeIds::DESERT;
                break;

            case "forest":
                return BiomeIds::FOREST;
                break;

            case "ice_plains":
                return BiomeIds::ICE_PLAINS;
                break;

            case "mountains":
                return BiomeIds::EXTREME_HILLS;
                break;

            case "ocean":
                return BiomeIds::OCEAN;
                break;

            case "plains":
                return BiomeIds::PLAINS;
                break;

            case "river":
                return BiomeIds::RIVER;
                break;

            case "small_mountains":
                return BiomeIds::TAIGA_HILLS;
                break;

            case "swamp":
                return BiomeIds::SWAMPLAND;
                break;

            case "taiga":
                return BiomeIds::TAIGA;
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

        if ($x1 > $x2) {
            $tmp = $x1;
            $x1 = $x2;
            $x2 = $tmp;
        }

        if ($z1 > $z2) {
            $tmp = $z1;
            $z1 = $z2;
            $z2 = $tmp;
        }

        for ($i = $x1; $i <= $x2; $i++)
            for ($j = $z1; $j <= $z2; $j++)
                $plugin->getServer()->getLevelByName($reg->getLevelName())->setBiomeId($i, $j, self::biomeParse($biomeName));
    }

    // given an issuer object, returns the plugin object
    // useful for static functions when you need the plugin refference
    public static function getPluginFromIssuer(Player $issuer)
    {
        return $issuer->getServer()->getPluginManager()->getPlugin("WorldGuard");
    }
}
