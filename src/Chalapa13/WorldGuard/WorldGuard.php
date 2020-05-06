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

namespace Chalapa13\WorldGuard;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\permission\{Permission, Permissible, PermissionManager};
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\Server;
use revivalpmmp\pureentities\event\CreatureSpawnEvent;

class WorldGuard extends PluginBase {

    const FLAGS = [
        "block-place" => "false",
        "block-break" => "false",
        "pvp" => "true",
        "deny-msg" => "true",
        "flow" => "true",
        "exp-drops" => "true",
        "invincible" => "false",
        "fall-dmg" => "true",
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
        "game-mode" => "false",
        "sleep" => "true",
        "send-chat" => "true",
        "receive-chat" => "true",
        "enderpearl" => "true",
        "fly-mode" => 0,
        "eat" => "true",
        "allow-damage-animals" => "true",
        "allow-damage-monsters" => "true",
        "allow-leaves-decay" => "true",
        "allow-plant-growth" => "true",
        "allow-spreading" => "true",
        "allow-block-burn" => "true",
        "priority" => 0
    ];

    const FLAG_TYPE = [
        "block-place" => "boolean",
        "block-break" => "boolean",
        "pvp" => "boolean",
        "deny-msg" => "boolean",
        "flow" => "boolean",
        "exp-drops" => "boolean",
        "invincible" => "boolean",
        "fall-dmg" => "boolean",
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
        "game-mode" => "string",
        "sleep" => "boolean",
        "send-chat" => "boolean",
        "receive-chat" => "boolean",
        "enderpearl" => "boolean",
        "fly-mode" => "integer",
        "eat" => "boolean",
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
     * @return array
     */
    public function getRegions(): array
    {
        return $this->regions;
    }

    private $players = [];
    public $muted = [];

    public $messages = [];

    public $lang = [];
    public $config = [];

    public $pureEntitiesPlugin = null;

    public function onEnable()
    {
        if (!is_dir($path = $this->getDataFolder())) {
            mkdir($path);
        }

        /**
         * load regions if file exists and if not create a file
         */
        if (is_file($path.'regions.yml')) {
            $regions = yaml_parse_file($path.'regions.yml');
        } else {
            yaml_emit_file($path.'regions.yml', []);
        }

        /**
         * load config if file exists and if not create a file
         */
        if (is_file($path.'config.yml')) {
            $this->config = yaml_parse_file($path.'config.yml');
        } else {
            $this->config = array(
                "language" => "en"
            );

            yaml_emit_file($path.'config.yml', $this->config);
        }

        /**
         * load language file
         */
        if (is_file($path . "lang_" . $this->config["language"] . ".yml")) {
            $this->lang = yaml_parse_file($path . "lang_" . $this->config["language"] . ".yml");
        } else {
            // if the file does not exist, generate a default english one and use that file
            $this->config["language"] = "en";
            yaml_emit_file($path.'config.yml', $this->config);

            $this->lang = array(
                "author_name" => "Chalapa",
                "gui_wg_menu_title" => "World Guard Menu",
                "gui_label_choose_option" => "Choose an option",
                "gui_btn_rg_management" => "Region Management",
                "gui_btn_help" => "Help",
                "gui_btn_manage_existing" => "Manage existing region",
                "gui_btn_create_region" => "Create a new region",
                "gui_btn_redefine_region" => "Redefine a region",
                "gui_btn_delete_region" => "Delete a region",
                "gui_creation_menu_title" => "Region Creation",
                "gui_creation_menu_label1" => "Let's help you create a region.",
                "gui_creation_menu_rg_name_box" => "First you will have to enter a name for your region.",
                "gui_creation_menu_label2" => "If you want your region to expand infinitely upwards and downards check the following option.",
                "gui_creation_menu_toggle_expand" => "Expand vertically",
                "gui_creation_menu_label3" => "Now hit the §a'Submit'§r and select 2 corners of your region as you will be instructed next.",
                "gui_dropdown_select_redefine" => "Select the region you would like to redefine",
                "gui_dropdown_select_delete" => "Select the region you would like to delete",
                "gui_dropdown_select_manage" => "Select the region you would like to manage",
                "gui_manage_menu_title" => "Managing region:",
                "gui_flag_pvp" => "PvP",
                "gui_flag_xp_drops" => "Experience drops",
                "gui_flag_invincible" => "Invincible",
                "gui_flag_fall_dmg" => "Fall damage",
                "gui_flag_usage" => "Use",
                "gui_flag_item_drop" => "Item drop",
                "gui_flag_explosions" => "Explosions",
                "gui_flag_notify_enter" => "Notify enter",
                "gui_flag_notify_leave" => "Notify leave",
                "gui_flag_potions" => "Allow potions",
                "gui_flag_allowed_enter" => "Allowed enter",
                "gui_flag_allowed_leave" => "Allowed leave",
                "gui_flag_gm" => "Gamemode",
                "gui_gm_survival" => "Survival",
                "gui_gm_creative" => "Creative",
                "gui_flag_sleep" => "Allow sleeping",
                "gui_flag_send_chat" => "Allow sending chat messages",
                "gui_flag_rcv_chat" => "Allow receiving chat messages",
                "gui_flag_enderpearl" => "Allow use of ender pearls",
                "gui_flag_fly_mode" => "Fly mode",
                "gui_enabled" => "Enabled",
                "gui_disabled" => "Disabled",
                "gui_flag_eat" => "Allow eating",
                "gui_flag_dmg_animals" => "Allow damaging of animals",
                "gui_flag_dmg_monsters" => "Allow damaging of monsters",
                "gui_flag_leaf_decay" => "Allow leaf decay",
                "gui_flag_plant_growth" => "Allow plant growth",
                "gui_flag_spread" => "Allow spreading",
                "gui_flag_block_burn" => "Allow block burn",
                "gui_flag_priority" => "Region priority",
                "gui_help_menu_label1" => "If you need help setting up world guard check out the tutorial we made for you:",
                "gui_help_menu_label2" => "http://worldguard.ddns.net/tutorial"
            );

            yaml_emit_file($path.'lang_en.yml', $this->lang);
        }

                /**
         * load messages if file exists and if not write the default ones
         */
        if(is_file($path.'messages.yml'))
        {
            $this->messages = yaml_parse_file($path.'messages.yml');
        }
        else{
            $this->messages = array (
                "denied-enter" => "You cannot enter this area.",
                "denied-leave" => "You cannot leave this area.",
                "no-permission-for-command" => "You do not have permission to use this command.",
                "denied-eat" => "You cannot eat in this area.",
                "denied-ender-pearls" => "You cannot use ender pearls in this area.",
                "denied-chat" => "You cannot chat in this region.",
                "denied-item-drop" => "You cannot drop items in this region.",
                "denied-pvp" => "You cannot hurt players of this region.",
                "denied-block-break" => "You cannot break blocks in this region.",
                "denied-block-place" => "You cannot place blocks in this region.",
                "denied-hurt-animal" => "You cannot hurt animals of this region.",
                "denied-hurt-monster" => "You cannot hurt monsters of this region."
            );

            yaml_emit_file($path.'messages.yml', $this->messages);
        }
        
        if (isset($regions)) {
            foreach ($regions as $name => $data) {
                $this->regions[$name] = new Region($name, $data["pos1"], $data["pos2"], $data["level"], $data["flags"]);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            $this->sessionizePlayer($p);
        }
    }

    public function onDisable(){
        $this->saveRegions();
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
        /*foreach ($player->getEffects() as $effect) {
            if ($effect->getDuration() >= 999999) {
                $player->removeEffect($effect->getId());
            }
        }*/
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
        $global = new Position(0,0,0,$pos->getLevel());
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
        if($highestPriorityName == ""){
             if ($this->regionExists("global.".$pos->getLevel()->getName())){
                $highestPriorityName = "global.".$pos->getLevel()->getName();
            }
            return $highestPriorityName;
        }
        else{
            return $highestPriorityName;
        }
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
	                $player->sendMessage(TF::RED. $this->messages["denied-leave"]);
	                return false;
	            }
            }
            if (($msg = $old->getFlag("notify-leave")) !== "") {
                $player->sendTip(Utils::aliasParse($player, $msg));
            }
            if ($old->getFlag("receive-chat") === "false") {
                unset($this->muted[$player->getRawUniqueId()]);
            }
          /*  foreach ($player->getEffects() as $effect) {
                if ($effect->getDuration() >= 999999) {
                    $player->removeEffect($effect->getId());
                }
            }*/
            if ($old->getFlight() === self::FLY_SUPERVISED) {
                Utils::disableFlight($player);
	    	}
        }

        if ($new !== "") {
            if ($new->getFlag("allowed-enter") === "false") 
            {
            	if(!$player->hasPermission("worldguard.enter." . $newregion))
            	{
                	$player->sendMessage(TF::RED. $this->messages["denied-enter"]);
                	return false;
                }
            }
            if (($gm = $new->getGamemode()) !== $player->getGamemode()) {
                if(!$player->hasPermission("worldguard.bypass.gamemode" . $newregion)){
                    if ($gm !== "false"){
                        if ($gm == "0" | $gm == "1" | $gm == "2" | $gm == "3"){
                            $player->setGamemode($gm);
                            if ($gm === 0 || $gm === 2) Utils::disableFlight($player);
                        }
                        else if ($gm == "creative"){
                            $player->setGamemode(1);
                        }
                        else if ($gm == "survival"){
                            $player->setGamemode(0);
                            Utils::disableFlight($player);
                        }
                        else if ($gm == "adventure"){
                            $player->setGamemode(2);
                            Utils::disableFlight($player);
                        }
                        else if ($gm == "spectator"){
                            $player->setGamemode(3);
                        }
                    }
                }
            }
            if (($msg = $new->getFlag("notify-enter")) !== "") {
                $player->sendTip(Utils::aliasParse($player, $msg));
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
           /* $effects =  $new->getEffects();
            if (!empty($effects)) {
                $player->removeAllEffects();
                foreach ($effects as $effect) {
                    $player->addEffect($effect);
                }
            }*/
        }

        /*
        if($new !== "")
        {
            if(($time = $new->getFlag("freeze-time")) !== -1 )
            {
                $pk = new SetTimePacket();
                $pk->time = intval($time);
                $player->dataPacket($pk);
            }
            else
            {
                $pk = new SetTimePacket();
                $pk->time = intval($this->getServer()->getTick());
                $player->dataPacket($pk);
            }
        }
        else
        {
            $pk = new SetTimePacket();
            $pk->time = intval($this->getServer()->getTick());
            $player->dataPacket($pk);
        }
        */

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
    
    public function saveRegions(){
        $data = [];
        foreach ($this->regions as $name => $region) {
            $data[$name] = $region->toArray();
        }
        yaml_emit_file($this->getDataFolder().'regions.yml', $data);
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
			$permission = new Permission("worldguard.enter." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
			$permission->addParent("worldguard.enter", true);
			PermissionManager::getInstance()->addPermission($permission);

			$permission = new Permission("worldguard.leave." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
			$permission->addParent("worldguard.leave", true);
			PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.build." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.build", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.break." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.break", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.edit." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.edit", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.eat." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.eat", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.drop." . $name, "Allows player to enter the " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.drop", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usechest." . $name, "Allows player to use chests in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usechest", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usechestender." . $name, "Allows player to use ender chests in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usechestender", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.enchantingtable." . $name, "Allows player to use enchanting table in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.enchantingtable", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usedoors." . $name, "Allows player to use doors in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usedoors", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usetrapdoors." . $name, "Allows player to use trapdoors in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usetrapdoors", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usegates." . $name, "Allows player to use gates in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usegates", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usefurnaces." . $name, "Allows player to use furnaces in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usefurnaces", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.useanvil." . $name, "Allows player to use anvils in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.useanvil", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usecauldron." . $name, "Allows player to use cauldron in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usecauldron", true);
            PermissionManager::getInstance()->addPermission($permission);

            $permission = new Permission("worldguard.usebrewingstand." . $name, "Allows player to use brewing stands in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usebrewingstand", true);
            PermissionManager::getInstance()->addPermission($permission);
            $this->saveRegions();

            $permission = new Permission("worldguard.usebeacon." . $name, "Allows player to use beacons in " . $name . " region.", Permission::DEFAULT_OP);
            $permission->addParent("worldguard.usebeacon", true);
            PermissionManager::getInstance()->addPermission($permission);
            $this->saveRegions();
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
                    $issuer->sendMessage($this->messages["no-permission-for-command"]);
                    return false;
                }
                GUI::displayMenu($issuer);
                break;
            case "region":
                if (!$issuer->hasPermission("worldguard.create") || !$issuer->hasPermission("worldguard.modify") || !$issuer->hasPermission("worldguard.delete")) {
                    $issuer->sendMessage($this->messages["no-permission-for-command"]);
                    return false;
                }
                if (isset($args[0])) {
                    switch ($args[0]) {
                        case "setbiome":
                            if (!$issuer->hasPermission("worldguard.modify")) {
                                $issuer->sendMessage($this->messages["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1]) && isset($args[2])) {
                                if (!ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED.'Region name must be alpha numeric.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    Utils::setBiome($this, $this->getRegion($args[1]), $args[2]);
                                    $issuer->sendMessage(TF::YELLOW.'You have changed the region\'s biome.');
                                    $this->saveRegions();
                                } else {
                                    $issuer->sendMessage(TF::RED.$args[1].' region does not exist. Use /region list to get a list of all regions.');
                                }
                            } else {
                                $issuer->sendMessage(TF::RED.'/region setbiome <name> <biome_name>');
                            }
                            break;
                        case "create":
                            if (!$issuer->hasPermission("worldguard.create")) {
                                $issuer->sendMessage($this->messages["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1])) {
                                if (!ctype_alnum($args[1])) {
                                    $issuer->sendMessage(TF::RED.'Region names cannot contain special characters.');
                                    return false;
                                }
                                if ($this->regionExists($args[1])) {
                                    $issuer->sendMessage(TF::RED.'This region already exists. Redefine it using /region redefine '.$args[1].', or remove it using /region remove '.$args[1]);
                                    return false;
                                } else {
                                    if (isset($args[2])){
                                        if($args[2] == "extended"){
                                            unset($this->creating[$id = $issuer->getRawUniqueId()], $this->process[$id]);
                                            $this->creating[$id] = [];
                                            $this->process[$id]= $args[1];
                                            $this->extended[$id] = [];
                                            $issuer->sendMessage(TF::YELLOW.'Right-Click two positions to complete creating the extended region ('.$args[1].').');
                                        }
                                        else{
                                            $issuer->sendMessage(TF::RED."Flag '".$args[2]."' not recognized.");
                                            return false;
                                        }
                                    }
                                    else{
                                        if ($args[1] == "global"){
                                            if ($this->regionExists($args[1].".".$issuer->getLevel()->getName())) {
                                                $issuer->sendMessage(TF::RED."A global region for this world already exists!");
                                                return false;
                                            }
                                            else{
                                                unset($this->creating[$id = $issuer->getRawUniqueId()], $this->process[$id]);
                                                $this->process[$id]= ("global.".$issuer->getLevel()->getName());
                                                $this->creating[$id][] = [0, 0, 0, $issuer->getLevel()->getName()];
                                                $this->creating[$id][] = [0, 0, 0, $issuer->getLevel()->getName()];
                                                $this->processCreation($issuer);
                                                $issuer->sendMessage(TF::GREEN."Global region for world ".$issuer->getLevel()->getName()." created.");
                                            }
                                        }
                                        else{
                                            unset($this->creating[$id = $issuer->getRawUniqueId()], $this->process[$id]);
                                            $this->creating[$id] = [];
                                            $this->process[$id]= $args[1];
                                            $issuer->sendMessage(TF::YELLOW.'Right-Click two positions to complete creating the region ('.$args[1].').');
                                        }
                                    }
                                }
                            } else {
                                $issuer->sendMessage(TF::RED.'/region create <name>');
                            }
                            break;
                        case "delete":
                            if (!$issuer->hasPermission("worldguard.delete")) {
                                $issuer->sendMessage($this->messages["no-permission-for-command"]);
                                return false;
                            }
                            if (isset($args[1])) {
                                if ($this->regionExists($args[1])) {
                                    unset($this->regions[$args[1]]);
                                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                                        $this->updateRegion($player);
                                    }
                                    $issuer->sendMessage(TF::YELLOW.'You have deleted the region: '.$args[1]);
                                    $this->saveRegions();
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
                                $msg .= "You haven't created any regions yet. Use /region create <name> to create your first region.";
                            } else {
                                $msg .= implode(TF::WHITE.', '.TF::LIGHT_PURPLE, array_keys($this->regions));
                            }
                            $issuer->sendMessage($msg);
                            break;
                        case "info":
                            $reg = $this->getRegionOf($issuer);
                            if ($reg !== "") {
                                $issuer->chat("/rg flags get ".$reg);
                                return true;
                            }
                            else {
                                $issuer->sendMessage(TF::RED."You are not currently standing in any regions.");
                                return false;
                            }
                            break;
                        case "redefine":
                            if (!isset($args[1])) {
                                $issuer->sendMessage(TF::RED.'/region redefine <region>');
                                return false;
                            }
                            else{
                                if (!$this->regionExists($args[1])) {
                                    $issuer->sendMessage(TF::RED.$args[1].' region does not exist. Use /region list to get a list of all regions.');
                                    return false;
                                }
                                else {
                                       unset($this->creating[$id = $issuer->getRawUniqueId()], $this->process[$id]);
                                       $this->creating[$id] = [];
                                       $this->process[$id]= $args[1];
                                       $issuer->sendMessage(TF::LIGHT_PURPLE.'Right-Click two positions to redefine your region ('.$args[1].').');
                                }
                            }
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
                                $issuer->sendMessage($this->messages["no-permission-for-command"]);
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
                                        $args[4] = str_replace("allow", "true", $args[4]);
                                        $args[4] = str_replace("deny", "false", $args[4]);
                                        $val = $args;
                                        unset($val[0], $val[1], $val[2], $val[3]);
                                        $opt = $this->getRegion($args[2])->setFlag($args[3], array_values($val));
                                        if ($opt !== null) {
                                            $issuer->sendMessage($opt);
                                        } else {
                                            $issuer->sendMessage(TF::YELLOW.'Flag has been updated successfully.');
                                            $this->saveRegions();
                                        }
                                        break;
                                    case "reset":
                                        $this->getRegion($args[2])->resetFlag($args[3]);
                                        $issuer->sendMessage(TF::YELLOW."Flag ".$args[3]." has been reset to it's default value.");
                                        break;
                                }
                            } else {
                                $issuer->sendMessage(TF::RED."/region flags <get/set/reset> <region> <flag> <value>\n".TF::GRAY.'<value> argument is only needed if you are setting the flag.');
                            }
                            break;
                    }
                } else {
                    $issuer->sendMessage(implode("\n".TF::LIGHT_PURPLE, [
                        "§9§lWorldGuard §r§7(by Chalapa) §9Help Page",
                        " ",
                        "§e/worldguard §7- §eOpen up the User Interface",
                        "§a/region create <region name> §7- §aCreate a new region.",
                        "§3/region list §7- §3List all regions.",
                        "§6/region info <region name> §7- §6Get information about your current region.",
                        "§c/region delete <region name> §7- §cPermanently delete a region.",
                        "§d/region flags <set/get/reset> <region name> §7- §dSet, Get, or Reset <region name>'s flags.",
                        " ",
                        "§9For additional help and documentation, visit WorldGuard's GitHub page:",
                        "§9https://github.com/Chalapa13/WorldGuard/",
                    ]));
                }
                break;
        }
        return true;
    }
}
