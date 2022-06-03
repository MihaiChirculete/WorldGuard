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

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command,CommandSender};
use pocketmine\console\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\player\Player;
use pocketmine\player\GameMode;
use pocketmine\world\Position;
use pocketmine\permission\{DefaultPermissions,Permission,PermissionManager,PermissionParser};
use MihaiChirculete\WorldGuard\ResourceUtils\{ResourceManager,ResourceUpdater};

class WorldGuard extends PluginBase
{
    const FLAGS = [
        "pluginbypass" => "false",
        "deny-msg" => "true",
        "block-place" => "false",
        "block-break" => "false",
        "pvp" => "true",
        "console-cmd-on-enter" => "none",
        "console-cmd-on-leave" => "none",
        "flow" => "true",
        "exp-drops" => "true",
        "invincible" => "false",
        "fall-dmg" => "true",
        "effects" => [],
        "blocked-cmds" => [],
        "allowed-cmds" => [],
        "use" => "false",
        "item-drop" => "true",
        "item-by-death" => "true",
        "explosion" => "false",
        "notify-enter" => "",
        "notify-leave" => "",
        "potions" => "true",
        "allowed-enter" => "true",
        "allowed-leave" => "true",
        "game-mode" => "false",
        "sleep" => "true",
        "send-chat" => "true",
        "receive-chat" => "true",
        "enderpearl" => "true",
        "bow" => "true",
        "fly-mode" => 0,
        "eat" => "true",
        "hunger" => "true",
        "allow-damage-animals" => "true",
        "allow-damage-monsters" => "true",
        "allow-leaves-decay" => "true",
        "allow-plant-growth" => "true",
        "allow-spreading" => "true",
        "allow-block-burn" => "true",
        "priority" => 0
    ];

    const FLAG_TYPE = [
        "pluginbypass" => "boolean",
        "deny-msg" => "boolean",
        "block-place" => "boolean",
        "block-break" => "boolean",
        "pvp" => "boolean",
        "console-cmd-on-enter" => "string",
        "console-cmd-on-leave" => "string",
        "flow" => "boolean",
        "exp-drops" => "boolean",
        "invincible" => "boolean",
        "fall-dmg" => "boolean",
        "effects" => "array",
        "blocked-cmds" => "array",
        "allowed-cmds" => "array",
        "use" => "boolean",
        "item-drop" => "boolean",
        "item-by-death" => "boolean",
        "explosion" => "boolean",
        "notify-enter" => "string",
        "notify-leave" => "string",
        "potions" => "boolean",
        "allowed-enter" => "boolean",
        "allowed-leave" => "boolean",
        "game-mode" => "string",
        "sleep" => "boolean",
        "send-chat" => "boolean",
        "receive-chat" => "boolean",
        "enderpearl" => "boolean",
        "bow" => "boolean",
        "fly-mode" => "integer",
        "eat" => "boolean",
        "hunger" => "boolean",
        "allow-damage-animals" => "boolean",
        "allow-damage-monsters" => "boolean",
        "allow-leaves-decay" => "boolean",
        "allow-plant-growth" => "boolean",
        "allow-spreading" => "boolean",
        "allow-block-burn" => "boolean",
        "priority" => "integer"
    ];

    const FLY_VANILLA = 0;

    const FLY_ENABLE = 1;

    const FLY_DISABLE = 2;

    const FLY_SUPERVISED = 3;

    public $creating = [];

    private $process = [];

    private $regions = [];

    /**
     *
     * @return array
     */
    public function getRegions(): array
    {
        return $this->regions;
    }

    private $players = [];

    public $muted = [];

    public $resourceManager = null;

    public $resourceUpdater = null;

    public function onEnable(): void
    {
        $this->resourceManager = ResourceManager::getInstance($this, $this->getServer());
        $this->resourceManager->loadResources();
        $this->resourceUpdater = ResourceUpdater::getInstance($this->resourceManager);
        $this->resourceUpdater->updateResourcesIfRequired(true);

        $regions = $this->resourceManager->getRegions();
        if (isset($regions)) {
            foreach ($regions as $name => $data) {
                $this->regions[$name] = new Region($name, $data["pos1"], $data["pos2"], $data["level"], $data["flags"]);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        foreach ($this->getServer()->getOnlinePlayers() as $p) {
            $this->sessionizePlayer($p);
        }
    }

    public function onDisable(): void
    {
        $this->resourceManager->saveRegions($this->regions);
    }

    public function getRegion(string $region)
    {
        return $this->regions[$region] ?? "";
    }

    public function getRegionByPlayer(Player $player)
    {
        if ($player instanceof Player) {
            $reg = $this->getRegionOf($player);
            return $reg !== "" ? $this->getRegion($reg) : "";
        }
    }

    public function getRegionOf(Player $player): string
    {
        if ($player instanceof Player) {
            return $this->players[$player->getUniqueId()->getBytes()] ?? "";
        }
    }

    public function regionExists(string $name): bool
    {
        return isset($this->regions[$name]);
    }

    public function flagExists(string $flag): bool
    {
        return isset(self::FLAGS[$flag]);
    }

    public function sessionizePlayer(Player $player)
    {
        $this->getLogger()->info("Updating session info for: " . $player->getName());
        $this->players[$player->getUniqueId()->getBytes()] = "";
        $this->updateRegion($player);
    }

    public function getRegionFromPosition(Position $pos)
    {
        $name = $this->getRegionNameFromPosition($pos);
        return $name !== "" ? $this->getRegion($name) : "";
    }

    public function getRegionNameFromPosition(Position $pos): string {
        $currentRegion = "";
        $highestPriority = - 1;
        // $global = new Position(0,0,0,$pos->getWorld()->getDisplayName());
        foreach ($this->regions as $name => $region) {
            if ($region->getLevelName() === $pos->getWorld()->getDisplayName()) {
                $reg1 = $region->getPos1();
                $reg2 = $region->getPos2();
                $x = array_flip(range($reg1[0], $reg2[0]));
                if (isset($x[$pos->x])) {
                    $y = array_flip(range($reg1[1], $reg2[1]));
                    if (isset($y[$pos->y])) {
                        $z = array_flip(range($reg1[2], $reg2[2]));
                        if (isset($z[$pos->z])) {
                            if ($highestPriority < intval($region->getFlag("priority"))) {
                                $highestPriority = intval($region->getFlag("priority"));
                                $currentRegion = $name;
                            }
                        }
                    }

                }
            }
        }
        if ($currentRegion == "") {
            if ($this->regionExists("global." . $pos->getWorld()->getDisplayName())) {
                $currentRegion = "global." . $pos->getWorld()->getDisplayName();
            }
        }
        return $currentRegion;
    }

    public function onPlayerLogoutRegion(Player $player)
    {
        // if player is loggedIn in WG Region and Logout
        $wgReg = $this->getRegion($player);
        if ($player instanceof Player && $wgReg !== "") {
            $player->getEffects()->clear();
            if ($this->resourceManager->getConfig()["debugging"] === true) {
                $this->getLogger()->info("Instance of player is in WorldGuard Region! Effects from Region should be deleted");
            }
        }
    }

    public function onRegionChange(Player $player, string $oldregion, string $newregion)
    {
        $new = $this->getRegion($newregion);
        $old = $this->getRegion($oldregion);

        if ($player instanceof Player) {
            if ($this->resourceManager->getConfig()["debugging"] === true) {
                if (gettype($new) === "string") {
                    $this->getLogger()->info("New Region is empty");
                } else {
                    $this->getLogger()->info("New Region: " . $new->getName());
                }
            }
            if ($this->resourceManager->getConfig()["debugging"] === true) {
                if (gettype($old) === "string") {
                    $this->getLogger()->info("Old Region is empty");
                } else {
                    $this->getLogger()->info("Old Region: " . $old->getName());
                }
            }
            if ($old !== "") {
                if ($old->getFlag("console-cmd-on-leave") !== "none") {
                    $cmd = str_replace("%player%", $player->getName(), $old->getFlag("console-cmd-on-leave"));
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $cmd);
                }
                if ($old->getFlag("allowed-leave") === "false") {
                    if (! $player->hasPermission("worldguard.leave." . $oldregion)) {
                        $player->sendMessage(TF::RED . $this->resourceManager->getMessages()["denied-leave"]);
                        return false;
                    }
                }
                if (($msg = $old->getFlag("notify-leave")) !== "") {
                    $player->sendTip(Utils::aliasParse($player, $msg));
                }
                if ($old->getFlag("receive-chat") === "false") {
                    unset($this->muted[$player->getUniqueId()->getBytes()]);
                }

                if ($old->getFlight() === self::FLY_SUPERVISED) {
                    if ($player->getGamemode() != "creative") {
                        Utils::disableFlight($player);
                    }
                }
            }

            if ($new !== "") {
                if ($new->getFlag("console-cmd-on-enter") !== "none") {
                    $cmd = str_replace("%player%", $player->getName(), $new->getFlag("console-cmd-on-enter"));
                    $player->getServer()->dispatchCommand(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $cmd);
                }

                if ($new->getFlag("allowed-enter") === "false") {
                    if (! $player->hasPermission("worldguard.enter." . $newregion)) {
                        $player->sendMessage(TF::RED . $this->resourceManager->getMessages()["denied-enter"]);
                        return false;
                    }
                }
                if (($gm = $new->getGamemode()) !== $player->getGamemode()) {
                    if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        if (!$player->hasPermission("worldguard.bypass.gamemode." . $newregion)) {
                            if ($gm !== "false") {
                                if ($gm == "creative") {
                                    $player->setGamemode(GameMode::CREATIVE());
                                } else if ($gm == "survival") {
                                    $player->setGamemode(GameMode::SURVIVAL());
                                    Utils::disableFlight($player);
                                } else if ($gm == "adventure") {
                                    $player->setGamemode(GameMode::ADVENTURE());
                                    Utils::disableFlight($player);
                                } else if ($gm == "spectator") {
                                    $player->setGamemode(GameMode::SPECTATOR());
                                }
                            }
                        }
                    }
                }
                if (($msg = $new->getFlag("notify-enter")) !== "") {
                    $player->sendTip(Utils::aliasParse($player, $msg));
                }
                if ($new->getFlag("receive-chat") === "false") {
                    $this->muted[$player->getUniqueId()->getBytes()] = $player;
                }
                if (! $player->hasPermission("worldguard.bypass.fly." . $newregion)) {
                    if (($flight = $new->getFlight()) !== self::FLY_VANILLA) {
                        if ($player->getGamemode(GameMode::CREATIVE())) {
                            switch ($flight) {
                                case self::FLY_ENABLE:
                                case self::FLY_SUPERVISED:
                                    if (! $player->getAllowFlight()) {
                                        $player->setAllowFlight(true);
                                    }
                                    break;
                                case self::FLY_DISABLE:
                                    Utils::disableFlight($player);
                                    break;
                            }
                        }
                    }
                }

                // EFFECTS

                if($old instanceof Region) {
                    $rgEffects = $old->getFlag("effects");
                    foreach ($player->getEffects() as $effect) {
                        if (array_key_exists($effect->getId(), $rgEffects)) {
                            if ($this->resourceManager->getConfig()["debugging"] === true) {
                                echo "effect: " . var_export($effect, true) . "effectflag: " . var_export($rgEffects, true);
                            }
                            $player->getEffects()->remove($effect->getType());
                        }
                    }
                }

                if ($new != null && ! empty($new)) {
                    $newRegionEffects = $new->getEffects();
                } else {
                    $newRegionEffects = null;
                }
                if ($old != null && ! empty($old)) {
                    $oldRegionEffects = $old->getEffects();
                } else {
                    $oldRegionEffects = null;
                }
                
                // Iterate all old effects and remove them
                if (! empty($oldRegionEffects) && $oldRegionEffects != null) {
                    if ($this->resourceManager->getConfig()["debugging"] === true) {
                        $this->getLogger()->info("Removing region-given effects, and re-adding any effects the player had.");
                    }
                    foreach ($oldRegionEffects as $effect) {
                        $player->getEffects()->remove($effect->getType());
                    }
                }

                // Iterate all new effects and add them
                if (! empty($newRegionEffects) && $newRegionEffects != null) {
                    if ($this->resourceManager->getConfig()["debugging"] === true) {
                        $this->getLogger()->info("Saving the player's current effects that the region overwrites, and giving the new effects from the region.");
                    }
                    foreach ($newRegionEffects as $effect) {
                        $player->getEffects()->add($effect);
                    }
                }
            }

            /*
             * if($new !== "")
             * {
             * if(($time = $new->getFlag("freeze-time")) !== -1 )
             * {
             * $pk = new SetTimePacket();
             * $pk->time = intval($time);
             * $player->dataPacket($pk);
             * }
             * else
             * {
             * $pk = new SetTimePacket();
             * $pk->time = intval($this->getServer()->getTick());
             * $player->dataPacket($pk);
             * }
             * }
             * else
             * {
             * $pk = new SetTimePacket();
             * $pk->time = intval($this->getServer()->getTick());
             * $player->dataPacket($pk);
             * }
             */
        }
        return true;
    }

    public function updateRegion(Player $player)
    {
        $region = isset($this->players[$player->getUniqueId()->getBytes()]) ? $this->players[$player->getUniqueId()->getBytes()] : null;
        if (($newRegion = $this->getRegionNameFromPosition($player->getPosition())) !== $region) {
            $this->players[$player->getUniqueId()->getBytes()] = $newRegion;
            return $this->onRegionChange($player, $region, $newRegion);
        }
        return true;
    }

    public function processCreation(Player $player)
    {
        if (isset($this->creating[$id = $player->getUniqueId()->getBytes()], $this->process[$id])) {
            $name = $this->process[$id];
            $map = $this->creating[$id];
            $level = $map[0][3];
            unset($map[0][3], $map[1][3]);
            $this->regions[$name] = new Region($name, $map[0], $map[1], $level, self::FLAGS);
            unset($this->process[$id], $this->creating[$id]);

            $permission = new Permission("worldguard.enter." . $name, "Allows player to enter the ".$name." region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.enter", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.leave." . $name, "Allows player to leave the " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.leave", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.place." . $name, "Allows player to build blocks in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.place", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.block-place." . $name, "Allows player to build blocks in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.block-place", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.break." . $name, "Allows player to break blocks in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.break", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.block-break." . $name, "Allows player to break blocks in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.block-break", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.edit." . $name, "Allows player to edit blocks in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.edit", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.eat." . $name, "Allows player to eat in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.eat", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.drop." . $name, "Allows player to drop items in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.drop", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usechest." . $name, "Allows player to use chests in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usechest", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usechestender." . $name, "Allows player to use ender chests in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usechestender", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usetrappedchest." . $name, "Allows player to use trapped chests in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usetrappedchest", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.enchantingtable." . $name, "Allows player to use enchanting table in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.enchantingtable", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usedoors." . $name, "Allows player to use doors in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usedoors", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usetrapdoors." . $name, "Allows player to use trapdoors in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usetrapdoors", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usegates." . $name, "Allows player to use gates in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usegates", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usefurnaces." . $name, "Allows player to use furnaces in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usefurnaces", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.useanvil." . $name, "Allows player to use anvils in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.useanvil", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usecauldron." . $name, "Allows player to use cauldron in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usecauldron", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usebrewingstand." . $name, "Allows player to use brewing stands in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usebrewingstand", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usebeacon." . $name, "Allows player to use beacons in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usebeacon", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usepressureplate." . $name, "Allows player to use pressureplates in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usepressureplate", true);
            PermissionManager::getInstance()->addPermission($permission);
             
            $permission = new Permission("worldguard.usebutton." . $name, "Allows player to use buttons in " . $name . " region.", [PermissionParser::DEFAULT_OP]);
            //$permission->addParent("worldguard.usebutton", true);
            PermissionManager::getInstance()->addPermission($permission);
            $this->resourceManager->saveRegions($this->regions);
            return $name;
        }
        return false;
    }

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args): bool
    {
        switch (strtolower($cmd->getName())) {
            case "worldguard":
                  if(!$issuer->hasPermission("worldguard.ui"))
                  {
                  $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                  return false;
                  }
                  GUI::displayMenu($issuer);

                break;
            case "region":
                if (! $issuer->hasPermission("worldguard.create") || ! $issuer->hasPermission("worldguard.modify") || ! $issuer->hasPermission("worldguard.delete")) {
                    $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                    return false;
                }
                if (isset($args[0])) {
                    switch ($args[0]) {
                        case "setbiome":
                            if (! $issuer->hasPermission("worldguard.modify")) {
                                $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1]) && isset($args[2])) {
                                if (! ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED . 'Region name must be alpha numeric.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    Utils::setBiome($this, $this->getRegion($args[1]), $args[2]);
                                    $issuer->sendMessage(TF::YELLOW . 'You have changed the region\'s biome.');
                                    $this->resourceManager->saveRegions($this->regions);
                                } else {
                                    $issuer->sendMessage(TF::RED . $args[1] . ' region does not exist. Use /region list to get a list of all regions.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED . '/region setbiome <name> <biome_name>');
                            }
                            break;
                        case "create":
                            if (! $issuer->hasPermission("worldguard.create")) {
                                $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1])) {
                                if (! ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED . 'Region names cannot contain special characters.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    $issuer->sendMessage(TF::RED . 'This region already exists. Redefine it using /region redefine ' . $args[1] . ', or remove it using /region remove ' . $args[1]);
                                    return false;
                                } else {
                                    if (isset($args[2])) {
                                        if ($args[2] == "extended") {
                                            unset($this->creating[$id = $issuer->getUniqueId()->getBytes()], $this->process[$id]);
                                            $this->creating[$id] = [];
                                            $this->process[$id] = $args[1];
                                            $this->extended[$id] = [];
                                            $issuer->sendMessage(TF::YELLOW . 'Right-Click two positions to complete creating the extended region (' . $args[1] . ').');
                                        } else {
                                            $issuer->sendMessage(TF::RED . "Flag '" . $args[2] . "' not recognized.");
                                            return false;
                                        }
                                    } else {
                                        if ($args[1] == "global") {
                                            if ($this->regionExists($args[1] . "." . $issuer->getWorld()->getDisplayName())) {
                                                $issuer->sendMessage(TF::RED . "A global region for this world already exists!");
                                                return false;
                                            } else {
                                                unset($this->creating[$id = $issuer->getUniqueId()->getBytes()], $this->process[$id]);
                                                $this->process[$id] = ("global." . $issuer->getWorld()->getDisplayName());
                                                $this->creating[$id][] = [
                                                    0,
                                                    0,
                                                    0,
                                                    $issuer->getWorld()->getDisplayName()
                                                ];
                                                $this->creating[$id][] = [
                                                    0,
                                                    0,
                                                    0,
                                                    $issuer->getWorld()->getDisplayName()
                                                ];
                                                $this->processCreation($issuer);
                                                $issuer->sendMessage(TF::GREEN . "Global region for world " . $issuer->getWorld()->getDisplayName() . " created.");
                                            }
                                        } else {
                                            unset($this->creating[$id = $issuer->getUniqueId()->getBytes()], $this->process[$id]);
                                            $this->creating[$id] = [];
                                            $this->process[$id] = $args[1];
                                            $issuer->sendMessage(TF::YELLOW . 'Right-Click two positions to complete creating the region (' . $args[1] . ').');
                                        }
                                    }
                                }
                            } else {
                                $issuer->sendMessage(TF::RED . '/region create <name>');
                            }
                            break;
                        case "delete":
                            if (! $issuer->hasPermission("worldguard.delete")) {
                                $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1])) {
                                if ($this->regionExists($args[1])) {
                                    unset($this->regions[$args[1]]);
                                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                                        $this->updateRegion($player);
                                    }
                                    $issuer->sendMessage(TF::YELLOW . 'You have deleted the region: ' . $args[1]);
                                    $this->resourceManager->saveRegions($this->regions);
                                } else {
                                    $issuer->sendMessage(TF::RED . $args[1] . ' region does not exist. Use /region list to get a list of all regions.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED . '/region delete <name>');
                            }
                            break;
                        case "list":
                            $msg = TF::LIGHT_PURPLE . "Regions: \n" . TF::LIGHT_PURPLE;
                            if (empty($this->regions)) {
                                $msg .= "You haven't created any regions yet. Use /region create <name> to create your first region.";
                            } else {
                                $msg .= implode(TF::WHITE . ', ' . TF::LIGHT_PURPLE, array_keys($this->regions));
                            }
                            $issuer->sendMessage($msg);
                            break;
                        case "info":
                            $reg = $this->getRegionOf($issuer);
                            if ($reg !== "") {
                                $issuer->chat("/rg flags get " . $reg);
                                return true;
                            } else {
                                $issuer->sendMessage(TF::RED . "You are not currently standing in any regions.");
                                return false;
                            }
                            break;
                        case "redefine":
                            if (! isset($args[1])) {
                                $issuer->sendMessage(TF::RED . '/region redefine <region>');
                                return false;
                            } else {
                                if (! $this->regionExists($args[1])) {
                                    $issuer->sendMessage(TF::RED . $args[1] . ' region does not exist. Use /region list to get a list of all regions.');
                                    return false;
                                } else {
                                    unset($this->creating[$id = $issuer->getUniqueId()->getBytes()], $this->process[$id]);
                                    $this->creating[$id] = [];
                                    $this->process[$id] = $args[1];
                                    $issuer->sendMessage(TF::LIGHT_PURPLE . 'Right-Click two positions to redefine your region (' . $args[1] . ').');
                                }
                            }
                            break;
                        case "getplayer":
                            if (isset($args[1])) {
                                if (($player = $this->getServer()->getPlayerExact($args[1])) !== null) {
                                    $reg = $this->getRegionOf($player);
                                    if ($reg !== "") {
                                        $issuer->sendMessage(TF::YELLOW . $player->getName() . ' is in ' . $reg . '.');
                                    } else {
                                        $issuer->sendMessage(TF::YELLOW . $player->getName() . 'is not in any region.');
                                    }
                                } else {
                                    $issuer->sendMessage(TF::RED . $args[1] . ' is offline.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED . '/region getplayer <player>');
                            }
                            break;
                        case "flag":
                        case "flags":
                            if (! $issuer->hasPermission("worldguard.modify")) {
                                $issuer->sendMessage($this->resourceManager->getMessages()["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1], $args[2])) {
                                if (! $this->regionExists($args[2])) {
                                    $issuer->sendMessage(TF::RED . 'The specified region does not exist. Use /region list to get a list of all regions.');
                                    return false;
                                }
                                if ($args[1] !== "get") {
                                    if (! isset($args[3])) {
                                        $issuer->sendMessage(TF::RED . "You haven't specified the <flag>.");
                                        return false;
                                    } elseif (! $this->flagExists($args[3])) {
                                        $issuer->sendMessage(TF::RED . "The specified flag does not exist. Available flags:\n" . TF::LIGHT_PURPLE . implode(TF::WHITE . ', ' . TF::LIGHT_PURPLE, array_keys(self::FLAGS)));
                                        return false;
                                    }
                                }
                                switch ($args[1]) {
                                    case "get":
                                        $flags = $this->getRegion($args[2])->getFlagsString();
                                        $issuer->sendMessage(TF::LIGHT_PURPLE . $args[2] . "'s flags:\n" . $flags);
                                        break;
                                    case "set":
                                        if (! isset($args[4])) {
                                            $issuer->sendMessage(TF::RED . 'You must specify the <value> of the flag.');
                                            return false;
                                        }
                                        $args[4] = str_replace("allow", "true", $args[4]);
                                        $args[4] = str_replace("deny", "false", $args[4]);
                                        $val = $args;
                                        unset($val[0], $val[1], $val[2], $val[3]);
                                        $opt = $this->getRegion($args[2])->setFlag($args[3], array_values($val));
                                        if ($opt !== null) {
                                            $issuer->sendMessage($opt);
                                        } else {
                                            $issuer->sendMessage(TF::YELLOW . 'Flag has been updated successfully.');
                                            $this->resourceManager->saveRegions($this->regions);
                                        }
                                        break;
                                    case "reset":
                                        $this->getRegion($args[2])->resetFlag($args[3]);
                                        $issuer->sendMessage(TF::YELLOW . "Flag " . $args[3] . " has been reset to it's default value.");
                                        $this->resourceManager->saveRegions($this->regions);
                                        break;
                                }
                            } else {
                                $issuer->sendMessage(TF::RED . "/region flags <get/set/reset> <region> <flag> <value>\n" . TF::GRAY . '<value> argument is only needed if you are setting the flag.');
                            }
                            break;
                    }
                } else {
                    $issuer->sendMessage(implode("\n" . TF::LIGHT_PURPLE, [
                        "§9§lWorldGuard ("
                    ]) . $this->getServer()
                        ->getVersion() . implode("\n" . TF::LIGHT_PURPLE, [
                        ") §r§9Help Page §7(by Chalapa)",
                        " ",
                        "§e/worldguard §7- §eOpen up the User Interface",
                        "§a/region create <region name> §7- §aCreate a new region.",
                        "§3/region list §7- §3List all regions.",
                        "§6/region info <region name> §7- §6Get information about your current region.",
                        "§c/region delete <region name> §7- §cPermanently delete a region.",
                        "§d/region flags <set/get/reset> <region name> §7- §dSet, Get, or Reset <region name>'s flags.",
                        " ",
                        "§9For additional help and documentation, visit WorldGuard's GitHub page:",
                        "§9https://github.com/MihaiChirculete/WorldGuard/"
                    ]));
                }
                break;
        }
        return true;
    }

    public function getKnockback()
    {
        return $this->resourceManager->getConfig()["knockback"];
    }
}
