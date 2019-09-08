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
* By Chalapa13.
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* GitHub: https://github.com/Chalapa13
*/

namespace WorldGuard;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\permission\{Permission, Permissible, PermissionManager};

class WorldGuard extends PluginBase {

    const FLAGS = [
        "pvp" => "true",
        "effects" => [],
        "blocked-cmds" => [],
        "allowed-cmds" => [],
        "use" => "false",
        "item-drop" => "true",
        "explosion" => "false",
        "notify-enter" => "",
        "notify-leave" => "",
        "potions" => "true",
        "allowed-enter" => "true",
        "allowed-leave" => "true",
        "game-mode" => 0,
        "sleep" => "true",
        "send-chat" => "true",
        "receive-chat" => "true",
        "enderpearl" => "true",
        "fly-mode" => 0,
        "eat" => "true",
        /*
        "allow-damage-animals" => "true",
        "allow-damage-monsters" => "true",
        */
        "allow-leaves-decay" => "true",
        "allow-plant-growth" => "true",
        "allow-spreading" => "true",
        "priority" => 0
    ];

    const FLAG_TYPE = [
        "pvp" => "boolean",
        "effects" => "array",
        "blocked-cmds" => "array",
        "allowed-cmds" => "array",
        "use" => "boolean",
        "item-drop" => "boolean",
        "explosion" => "boolean",
        "notify-enter" => "string",
        "notify-leave" => "string",
        "potions" => "boolean",
        "allowed-enter" => "boolean",
        "allowed-leave" => "boolean",
        "game-mode" => "integer",
        "sleep" => "boolean",
        "send-chat" => "boolean",
        "receive-chat" => "boolean",
        "enderpearl" => "boolean",
        "fly-mode" => "integer",
        "eat" => "boolean",
        /*
        "allow-damage-animals" => "boolean",
        "allow-damage-monsters" => "boolean",
        */
        "allow-leaves-decay" => "boolean",
        "allow-plant-growth" => "boolean",
        "allow-spreading" => "boolean",
        "priority" => "integer"
    ];

    const FLY_VANILLA = 0;
    const FLY_ENABLE = 1;
    const FLY_DISABLE = 2;
    const FLY_SUPERVISED = 3;

    public $creating = [];
    private $process = [];
    private $regions = [];
    private $players = [];
    public $muted = [];

    public function onEnable()
    {
        if (!is_dir($path = $this->getDataFolder())) {
            mkdir($path);
        }

        if (is_file($path.'regions.yml')) {
            $regions = yaml_parse_file($path.'regions.yml');
        } else {
            yaml_emit_file($path.'regions.yml', []);
        }
        
        if (isset($regions)) {
            foreach ($regions as $name => $data) {
                $this->regions[$name] = new Region($name, $data["pos1"], $data["pos2"], $data["level"], $data["flags"]);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable()
    {
        $data = [];
        foreach ($this->regions as $name => $region) {
            $data[$name] = $region->toArray();
        }
        yaml_emit_file($this->getDataFolder().'regions.yml', $data);
    }

    public function getRegion(string $region)
    {
        return $this->regions[$region] ?? "";
    }

    public function getRegionByPlayer(Player $player)
    {
        $reg = $this->getRegionOf($player);
        return $reg !== "" ? $this->getRegion($reg) : "";
    }

    public function getRegionOf(Player $player): string
    {
        return $this->players[$player->getRawUniqueId()] ?? "";
    }

    public function regionExists(string $name) : bool
    {
        return isset($this->regions[$name]);
    }

    public function flagExists(string $flag) : bool
    {
        return isset(self::FLAGS[$flag]);
    }

    public function sessionizePlayer(Player $player)
    {
        foreach ($player->getEffects() as $effect) {
            if ($effect->getDuration() >= 999999) {
                $player->removeEffect($effect->getId());
            }
        }
        $this->players[$player->getRawUniqueId()] = "";
        $this->updateRegion($player);
    }

    public function getRegionFromPosition(Position $pos)
    {
        $name = $this->getRegionNameFromPosition($pos);
        return $name !== "" ? $this->getRegion($name) : "";
    }

    public function getRegionNameFromPosition(Position $pos) : string
    {
        $highestPriorityName = "";
        $highestPriority = -1;

        foreach ($this->regions as $name => $region) {
            if ($region->getLevelName() === $pos->getLevel()->getName()) {
                $reg1 = $region->getPos1();
                $reg2 = $region->getPos2();
                $x = array_flip(range($reg1[0], $reg2[0]));
                if (isset($x[$pos->x])) {
                    $y = array_flip(range($reg1[1], $reg2[1]));
                    if (isset($y[$pos->y])) {
                        $z = array_flip(range($reg1[2], $reg2[2]));
                        if (isset($z[$pos->z])) {
                            if($highestPriority<intval($region->getFlag("priority")))
                            {
                                $highestPriority = intval($region->getFlag("priority"));
                                $highestPriorityName = $name;
                            }
                        }
                    }
                }
            }
        }
        return $highestPriorityName;
    }

    public function onRegionChange(Player $player, string $oldregion, string $newregion)
    {
        $new = $this->getRegion($newregion);
        $old = $this->getRegion($oldregion);

        if ($old !== "") {
            if ($old->getFlag("allowed-leave") === "false") 
            {
            	if(!$player->hasPermission("worldguard.leave." . $oldregion))
            	{
	                $player->sendMessage(TF::RED.'You cannot leave this area.');
	                return false;
	            }
            }
            if (($msg = $old->getFlag("notify-leave")) !== "") {
                $player->sendMessage($msg);
            }
            if ($old->getFlag("receive-chat") === "false") {
                unset($this->muted[$player->getRawUniqueId()]);
            }
            foreach ($player->getEffects() as $effect) {
                if ($effect->getDuration() >= 999999) {
                    $player->removeEffect($effect->getId());
                }
            }
            if ($old->getFlight() === self::FLY_SUPERVISED) {
                Utils::disableFlight($player);
	    	}
            if ($old->getGamemode() !== ($gm = $this->getServer()->getDefaultGamemode())) {
                $player->setGamemode($gm);
                if ($gm === 0 || $gm === 2) Utils::disableFlight($player);
            }
        }

        if ($new !== "") {
            if ($new->getFlag("allowed-enter") === "false") 
            {
            	if(!$player->hasPermission("worldguard.enter." . $newregion))
            	{
                	$player->sendMessage(TF::RED.'You cannot enter this area.');
                	return false;
                }
            }
            if (($gm = $new->getGamemode()) !== $player->getGamemode()) {
                $player->setGamemode($gm);
                if ($gm === 0 || $gm === 2) Utils::disableFlight($player);
            }
            if (($msg = $new->getFlag("notify-enter")) !== "") {
                $player->sendMessage($msg);
            }
            if ($new->getFlag("receive-chat") === "false") {
                $this->muted[$player->getRawUniqueId()] = $player;
            }
            if (($flight = $new->getFlight()) !== self::FLY_VANILLA) {
                switch ($flight) {
                    case self::FLY_ENABLE:
                    case self::FLY_SUPERVISED:
                        if (!$player->getAllowFlight()) {
                            $player->setAllowFlight(true);
                        }
                        break;
                    case self::FLY_DISABLE:
                        Utils::disableFlight($player);
                        break;
                }
            }
            $effects =  $new->getEffects();
            if (!empty($effects)) {
                $player->removeAllEffects();
                foreach ($effects as $effect) {
                    $player->addEffect($effect);
                }
            }
        }

        return true;
    }

    public function updateRegion(Player $player)
    {
        $region = $this->players[$id = $player->getRawUniqueId()];
        if (($newRegion = $this->getRegionNameFromPosition($player->getPosition())) !== $region) {
            $this->players[$id] = $newRegion;
            return $this->onRegionChange($player, $region, $newRegion);
        }
        return true;
    }

    public function processCreation(Player $player)
    {
        if (isset($this->creating[$id = $player->getRawUniqueId()], $this->process[$id])) {
            $name = $this->process[$id];
            $map = $this->creating[$id];
            $level = $map[0][3];
            unset($map[0][3], $map[1][3]);
            $this->regions[$name] = new Region($name, $map[0], $map[1], $level, self::FLAGS);
            unset($this->process[$id], $this->creating[$id]);

            /* add permission for accesing this region */
			$permission = new Permission("worldguard.enter." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
			$permission->addParent("worldguard.enter", true);
			PermissionManager::getInstance()->addPermission($permission);

			/* add permission for leaving this region */
			$permission = new Permission("worldguard.leave." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
			$permission->addParent("worldguard.leave", true);
			PermissionManager::getInstance()->addPermission($permission);

            /* add permission for building in this region */
            $permission = new Permission("worldguard.build." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.build", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for breaking blocks in this region */
            $permission = new Permission("worldguard.break." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.break", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for editing blocks in this region */
            $permission = new Permission("worldguard.edit." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.edit", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for eating in this region */
            $permission = new Permission("worldguard.eat." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.eat", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for dropping items in this region */
            $permission = new Permission("worldguard.drop." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.drop", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for using chests in this region */
            $permission = new Permission("worldguard.usechest." . $name, "Allows player to use chests in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usechest", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usechestender." . $name, "Allows player to use ender chests in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usechestender", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for using enchanting tables in this region */
            $permission = new Permission("worldguard.enchantingtable." . $name, "Allows player to use enchanting table in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.enchantingtable", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for using doors in this region */
            $permission = new Permission("worldguard.usedoors." . $name, "Allows player to use doors in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usedoors", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for using trapdoors in this region */
            $permission = new Permission("worldguard.usetrapdoors." . $name, "Allows player to use doors in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usetrapdoors", true);
            PermissionManager::getInstance()->addPermission($permission);

            /* add permission for using gates in this region */
            $permission = new Permission("worldguard.usetrapdoors." . $name, "Allows player to use doors in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usetrapdoors", true);
            PermissionManager::getInstance()->addPermission($permission);


            return $name;
        }
        return false;
    }

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "region":
                if (!$issuer->hasPermission("worldguard.create") || !$issuer->hasPermission("worldguard.modify") || !$issuer->hasPermission("worldguard.delete")) {
                    $issuer->sendMessage("You do not have permission to use this command.");
                    return false;
                }
                if (isset($args[0])) {
                    switch ($args[0]) {
                        case "create":
                            if (!$issuer->hasPermission("worldguard.create")) {
                                $issuer->sendMessage("You do not have permission to use this command.");
                                return false;
                            }
                            if (isset($args[1])) {
                                if (!ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED.'Region name must be alpha numeric.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    $issuer->sendMessage(TF::RED.'This region already exists. Remap it using /region remap '.$args[1].', or remove it using /region remove '.$args[1]);
                                    return false;
                                } else {
                                    unset($this->creating[$id = $issuer->getRawUniqueId()], $this->process[$id]);
                                    $this->creating[$id] = [];
                                    $this->process[$id]= $args[1];
                                    $issuer->sendMessage(TF::LIGHT_PURPLE.'Select two positions to complete creating your region ('.$args[1].').');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED.'/region create <name>');
                            }
                            break;
                        case "delete":
                            if (!$issuer->hasPermission("worldguard.delete")) {
                                $issuer->sendMessage("You do not have permission to use this command.");
                                return false;
                            }
                            if (isset($args[1])) {
                                if (!ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED.'Region name must be alpha numeric.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    unset($this->regions[$args[1]]);
                                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                                        $this->updateRegion($player);
                                    }
                                    $issuer->sendMessage(TF::YELLOW.'You have deleted the region: '.$args[1]);
                                } else {
                                    $issuer->sendMessage(TF::RED.$args[1].' region does not exist. Use /region list to get a list of all regions.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED.'/region delete <name>');
                            }
                            break;
                        case "list":
                            $msg = TF::LIGHT_PURPLE."Regions: \n".TF::LIGHT_PURPLE;
                            if (empty($this->regions)) {
                                $msg .= "You haven't created any region yet. Use /region create <name> to create your first region.";
                            } else {
                                $msg .= implode(TF::WHITE.', '.TF::LIGHT_PURPLE, array_keys($this->regions));
                            }
                            $issuer->sendMessage($msg);
                            break;
                        case "getplayer":
                            if (isset($args[1])) {
                                if (($player = $this->getServer()->getPlayerExact($args[1])) !== null) {
                                    $reg = $this->getRegionOf($player);
                                    if ($reg !== "") {
                                        $issuer->sendMessage(TF::YELLOW.$player->getName().' is in '.$reg.'.');
                                    } else {
                                        $issuer->sendMessage(TF::YELLOW.$player->getName().'is not in any region.');
                                    }
                                } else {
                                    $issuer->sendMessage(TF::RED.$args[1].' is offline.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED.'/region getplayer <player>');
                            }
                            break;
                        case "flag":
                        case "flags":
                            if (!$issuer->hasPermission("worldguard.modify")) {
                                $issuer->sendMessage("You do not have permission to use this command.");
                                return false;
                            }
                            if (isset($args[1], $args[2])) {
                                if (!$this->regionExists($args[2])) {
                                    $issuer->sendMessage(TF::RED.'The specified region does not exist. Use /region list to get a list of all regions.');
                                    return false;
                                }
                                if ($args[1] !== "get") {
                                    if (!isset($args[3])) {
                                        $issuer->sendMessage(TF::RED."You haven't specified the <flag>.");
                                        return false;
                                    } elseif (!$this->flagExists($args[3])) {
                                        $issuer->sendMessage(TF::RED."The specified flag does not exist. Available flags:\n".TF::LIGHT_PURPLE.implode(TF::WHITE.', '.TF::LIGHT_PURPLE, array_keys(self::FLAGS)));
                                        return false;
                                    }
                                }
                                switch ($args[1]) {
                                    case "get":
                                        $flags = $this->getRegion($args[2])->getFlagsString();
                                        $issuer->sendMessage(TF::LIGHT_PURPLE.$args[2]."'s flags:\n".$flags);
                                        break;
                                    case "set":
                                        if (!isset($args[4])) {
                                            $issuer->sendMessage(TF::RED.'You must specify the <value> of the flag.');
                                            return false;
                                        }
                                        $val = $args;
                                        unset($val[0], $val[1], $val[2], $val[3]);
                                        $opt = $this->getRegion($args[2])->setFlag($args[3], array_values($val));
                                        if ($opt !== null) {
                                            $issuer->sendMessage($opt);
                                        } else {
                                            $issuer->sendMessage(TF::YELLOW.'Flag has been updated successfully.');
                                        }
                                        break;
                                    case "reset":
                                        $this->getRegion($args[2])->resetFlag($args[3]);
                                        break;
                                }
                            } else {
                                $issuer->sendMessage(TF::RED."/region flags <get/set/reset> <region> <flag> <value>\n".TF::GRAY.'<value> argument is only needed if you are setting the flag.');
                            }
                            break;
                    }
                } else {
                    $issuer->sendMessage(implode("\n".TF::LIGHT_PURPLE, [
                        "WorldGuard (by Chalapa) Help Page",
                        "Download link: https://github.com/Chalapa13/WorldGuard/tree/master/compiled",
                        " ",
                        "/region create <name> - Define a new region.",
                        "/region list - List all regions.",
                        "/region flags get <region> - Get <region>'s flags.",
                        "/region flags reset <region> <flag> - Reset <region>'s <flag> to default.",
                        "/region flags set <region> <flag> <value> - Modify <value> of the <region>'s flag.",
                    ]));
                }
                break;
        }
        return true;
    }
}
