<?php


namespace MihaiChirculete\WorldGuard;
use pocketmine\utils\TextFormat as TF;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use MihaiChirculete\WorldGuard\forms\{CustomForm, CustomFormResponse, MenuForm};
use MihaiChirculete\WorldGuard\elements\{Button, Dropdown, Image, Input, Label, Toggle};

class GUI
{

    public static $currentlyEditedRg = "";

    public static function displayMenu(CommandSender $issuer)
    {
        $plugin = Utils::getPluginFromIssuer($issuer);
        $lang = $plugin->resourceManager->getLanguagePack();

        $issuer->sendForm(new MenuForm(
            "§9§l". $lang["gui_wg_menu_title"], $lang["gui_label_choose_option"], [new Button("§6§l". $lang["gui_btn_rg_management"], new Image("textures/items/book_writable", "path")),
            new Button("§5§l". $lang["gui_btn_help"])],
            function(\WGPlayerClass $player, Button $selected) : void{

                switch ($selected->getValue())
                {
                    case 0:
                        self::displayRgManagement($player);
                        break;
                    case 1:
                        self::displayHelpMenu($player);
                        break;
                }
            }
        ));
    }

    public static function displayRgManagement(\WGPlayerClass $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $issuer->sendForm(new MenuForm(
            "§9§l" . $lang["gui_btn_rg_management"], $lang["gui_label_choose_option"],
            [new Button($lang["gui_btn_manage_existing"]),
            new Button($lang["gui_btn_create_region"]),
            new Button($lang["gui_btn_redefine_region"]),
            new Button($lang["gui_btn_delete_region"])],
            function(\WGPlayerClass $player, Button $selected) : void{

                switch ($selected->getValue())
                {
                    case 0:
                        self::displayExistingRegions($player);
                        break;
                    case 1:
                        self::displayRgCreation($player);
                        break;
                    case 2:
                        self::displayRgRedefine($player);
                        break;
                    case 3:
                        self::displayRgDelete($player);
                        break;
                }
            }
        ));
    }

    public static function displayRgCreation(\WGPlayerClass $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_creation_menu_title"],
            [
                new Label($lang["gui_creation_menu_label1"]),
                new Input($lang["gui_creation_menu_rg_name_box"], "MyRegion"),
                new Label($lang["gui_creation_menu_label2"]),
                new Toggle($lang["gui_creation_menu_toggle_expand"], false),
                new Label($lang["gui_creation_menu_label3"])
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{

                list($rgName, $extended) = $response->getValues();
                if($extended === true)
                    $player->getServer()->dispatchCommand($player, "rg create $rgName extended");
                else
                    $player->getServer()->dispatchCommand($player, "rg create $rgName");
            }
        ));
    }

    public static function displayRgRedefine(\WGPlayerClass $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $regions = array_keys(Utils::getPluginFromIssuer($issuer)->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_redefine"], $regions),
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg redefine $rgName");
            }
        ));
    }

    public static function displayRgDelete(\WGPlayerClass $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_delete"], $regions),
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg delete $rgName");
            }
        ));
    }
    public static function displayExistingRegions(\WGPlayerClass $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();
        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_manage"], $regions),
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                self::displayRgEditing($player, $rgName);
            }
        ));
    }

    public static function displayRgEditing(\WGPlayerClass $issuer, $rgName)
    {
        $rg = $issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegion($rgName);
        self::$currentlyEditedRg = $rgName;

        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $issuer->sendForm(new CustomForm($lang["gui_manage_menu_title"] . " §9" . $rgName,
            [
                new Toggle($lang["gui_flag_pluginbypass"], filter_var($rg->getFlag("pluginbypass"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_deny_message"], filter_var($rg->getFlag("deny-msg"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_blockbreak"], filter_var($rg->getFlag("block-break"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_blockplace"], filter_var($rg->getFlag("block-place"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_pvp"], filter_var($rg->getFlag("pvp"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_xp_drops"], filter_var($rg->getFlag("exp-drops"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_invincible"], filter_var($rg->getFlag("invincible"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_fall_dmg"], filter_var($rg->getFlag("fall-dmg"), FILTER_VALIDATE_BOOLEAN)),
                new Dropdown($lang["gui_flag_effect"], [$lang["gui_effect_delete"], $lang["gui_effect_speed"], $lang["gui_effect_slowness"], 
                    $lang["gui_effect_haste"], $lang["gui_effect_fatigue"], $lang["gui_effect_strength"], $lang["gui_effect_healing"], 
                    $lang["gui_effect_damage"], $lang["gui_effect_jump_boost"], $lang["gui_effect_nausea"], $lang["gui_effect_regeneration"], 
                    $lang["gui_effect_resistance"], $lang["gui_effect_fire_resistance"], $lang["gui_effect_water_breathing"], 
                    $lang["gui_effect_invisiblilty"], $lang["gui_effect_blindness"], $lang["gui_effect_night_vision"], $lang["gui_effect_hunger"], 
                    $lang["gui_effect_weakness"], $lang["gui_effect_poison"], $lang["gui_effect_wither"], $lang["gui_effect_healthboost"], 
                    $lang["gui_effect_absorption"], $lang["gui_effect_saturation"], $lang["gui_effect_leviatation"], $lang["gui_effect_fatal_poison"], 
                    $lang["gui_effect_conduit_power"]]),
                new Label($lang["gui_effect_restart_label"]),
                // add flag for blocked commands
                // add flag for allowed commands
                new Toggle($lang["gui_flag_usage"], filter_var($rg->getFlag("use"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_item_drop"], filter_var($rg->getFlag("item-drop"), FILTER_VALIDATE_BOOLEAN)),

                new Toggle($lang["gui_flag_item_death_drop"], filter_var($rg->getFlag("item-by-death"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_explosions"], filter_var($rg->getFlag("explosion"), FILTER_VALIDATE_BOOLEAN)),
                new Input($lang["gui_flag_notify_enter"], $rg->getFlag("notify-enter")),
                new Input($lang["gui_flag_notify_leave"], $rg->getFlag("notify-leave")),
                new Toggle($lang["gui_flag_potions"], filter_var($rg->getFlag("potions"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_allowed_enter"], filter_var($rg->getFlag("allowed-enter"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_allowed_leave"], filter_var($rg->getFlag("allowed-leave"), FILTER_VALIDATE_BOOLEAN)),
                new Dropdown($lang["gui_flag_gm"], [$lang["gui_gm_survival"], $lang["gui_gm_creative"], $lang["gui_gm_adventure"]]),
                new Toggle($lang["gui_flag_sleep"], filter_var($rg->getFlag("sleep"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_send_chat"], filter_var($rg->getFlag("send-chat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_rcv_chat"], filter_var($rg->getFlag("receive-chat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_enderpearl"], filter_var($rg->getFlag("enderpearl"), FILTER_VALIDATE_BOOLEAN)),
                new Dropdown($lang["gui_flag_fly_mode"], ["Vanilla", $lang["gui_enabled"], $lang["gui_disabled"], "Supervised"]),
                new Toggle($lang["gui_flag_eat"], filter_var($rg->getFlag("eat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_hunger"], filter_var($rg->getFlag("hunger"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_dmg_animals"], filter_var($rg->getFlag("allow-damage-animals"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_dmg_monsters"], filter_var($rg->getFlag("allow-damage-monsters"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_leaf_decay"], filter_var($rg->getFlag("allow-leaves-decay"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_plant_growth"], filter_var($rg->getFlag("allow-plant-growth"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_spread"], filter_var($rg->getFlag("allow-spreading"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_block_burn"], filter_var($rg->getFlag("allow-block-burn"), FILTER_VALIDATE_BOOLEAN)),
                new Input($lang["gui_flag_priority"], filter_var($rg->getFlag("priority"), FILTER_VALIDATE_INT))
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{
                list($pluginBypass, $denyMessage, $blockBreak, $blockPlace, $pvpFlag, $xpFlag, $invincibleFlag, $fallDmgFlag, $effectsFlag,
                    $useFlag, $itemDropFlag, $itemDeathDropFlag, $explosionsFlag, $notifyEnterFlag, $notifyLeaveFlag, $potionsFlag, 
                    $allowEnterFlag, $allowLeaveFlag, $gamemodeFlag, $sleepFlag, $sendChatFlag, $receiveChatFlag, $enderPearlFlag, 
                    $flyModeFlag, $eatingFlag, $HungerFlag, $damageAnimalsFlag, $damageMonstersFlag, 
                    $leafDecayFlag, $plantGrowthFlag, $spreadingFlag, $blockBurnFlag, $priorityFlag) = $response->getValues();
              
                $lang = Utils::getPluginFromIssuer($player)->resourceManager->getLanguagePack();

                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" pluginbypass " . var_export($pluginBypass, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" deny-msg " . var_export($denyMessage, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" block-break " . var_export($blockBreak, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" block-place " . var_export($blockPlace, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" pvp " . var_export($pvpFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" exp-drops " . var_export($xpFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" invincible " . var_export($invincibleFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fall-dmg " . var_export($fallDmgFlag, true));
                switch ($effectsFlag)
                {
                    case $lang["gui_effect_delete"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 0");
                        break;
                    case $lang["gui_effect_speed"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 1");
                        break;
                    case $lang["gui_effect_slowness"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 2");
                        break;
                    case $lang["gui_effect_haste"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 3");
                        break;
                    case $lang["gui_effect_fatigue"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 4");
                        break;
                    case $lang["gui_effect_strength"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 5");
                        break;
                    case $lang["gui_effect_healing"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 6");
                        break;
                    case $lang["gui_effect_damage"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 7");
                        break;
                    case $lang["gui_effect_jump_boost"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 8");
                        break;
                    case $lang["gui_effect_nausea"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 9");
                        break;
                    case $lang["gui_effect_regeneration"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 10");
                        break;
                    case $lang["gui_effect_resistance"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 11");
                        break;
                    case $lang["gui_effect_fire_resistance"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 12");
                        break;
                    case $lang["gui_effect_water_breathing"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 13");
                        break;
                    case $lang["gui_effect_invisiblilty"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 14");
                        break;
                    case $lang["gui_effect_blindness"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 15");
                        break;
                    case $lang["gui_effect_night_vision"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 16");
                        break;
                    case $lang["gui_effect_hunger"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 17");
                        break;
                    case $lang["gui_effect_weakness"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 18");
                        break;
                    case $lang["gui_effect_poison"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 19");
                        break;
                    case $lang["gui_effect_wither"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 20");
                        break;
                    case $lang["gui_effect_healthboost"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 21");
                        break;
                    case $lang["gui_effect_absorption"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 22");
                        break;
                    case $lang["gui_effect_saturation"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 23");
                        break;
                    case $lang["gui_effect_leviatation"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 24");
                        break;
                    case $lang["gui_effect_fatal_poison"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 25");
                        break;
                    case $lang["gui_effect_conduit_power"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" effects 26");
                        break;
                }   
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" use " . var_export($useFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" item-drop " . var_export($itemDropFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" item-by-death " . var_export($itemDeathDropFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" explosion " . var_export($explosionsFlag, true));
                if($notifyEnterFlag != '' || $notifyEnterFlag != ' '){$player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" notify-enter " . $notifyEnterFlag);}
                if($notifyLeaveFlag != '' || $notifyLeaveFlag != ' '){$player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" notify-leave " . $notifyLeaveFlag);}
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" potions " . var_export($potionsFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allowed-enter " . var_export($allowEnterFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allowed-leave " . var_export($allowLeaveFlag, true));
                switch ($gamemodeFlag)
                {
                    case $lang["gui_gm_survival"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" game-mode survival");
                        break;
                    case $lang["gui_gm_creative"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" game-mode creative");
                        break;
                    case $lang["gui_gm_adventure"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" game-mode adventure");
                        break;
                }
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" sleep " . var_export($sleepFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" send-chat " . var_export($sendChatFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" receive-chat " . var_export($receiveChatFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" enderpearl " . var_export($enderPearlFlag, true));
                switch ($flyModeFlag)
                {
                    case "Vanilla":
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fly-mode 0");
                        break;
                    case $lang["gui_enabled"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fly-mode 1");
                        break;
                    case $lang["gui_disabled"]:
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fly-mode 2");
                        break;
                    case "Supervised":
                        $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fly-mode 3");
                        break;
                }
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" eat " . var_export($eatingFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" hunger " . var_export($HungerFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-damage-animals " . var_export($damageAnimalsFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-damage-monsters " . var_export($damageMonstersFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-leaves-decay " . var_export($leafDecayFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-plant-growth " . var_export($plantGrowthFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-spreading " . var_export($spreadingFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allow-block-burn " . var_export($blockBurnFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" priority " . intval($priorityFlag));
                $player->sendMessage(TF::GREEN."Region ".self::$currentlyEditedRg." updated successfully!");

                self::$currentlyEditedRg = "";
            }
        ));
    }

    public static function displayHelpMenu(\WGPlayerClass $issuer)
    {
        $plugin = Utils::getPluginFromIssuer($issuer);
        $lang = $plugin->resourceManager->getLanguagePack();

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_help"],
            [
                new Label($lang["gui_help_menu_label1"]),
                new Label($lang["gui_help_menu_label2"]),
            ],
            function(\WGPlayerClass $player, CustomFormResponse $response) : void{}
        ));

    }
}
