<?php


namespace Chalapa13\WorldGuard;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerMoveEvent, PlayerInteractEvent, PlayerCommandPreprocessEvent, PlayerDropItemEvent, PlayerBedEnterEvent, PlayerChatEvent, PlayerItemHeldEvent};
use Chalapa13\WorldGuard\forms\{CustomForm, CustomFormResponse, MenuForm, ModalForm};
use Chalapa13\WorldGuard\elements\{Button, Dropdown, Image, Input, Label, Slider, StepSlider, Toggle};
use Chalapa13\WorldGuard\Region;

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
            function(Player $player, Button $selected) : void{

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

    public static function displayRgManagement(Player $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $issuer->sendForm(new MenuForm(
            "§9§l" . $lang["gui_btn_rg_management"], $lang["gui_label_choose_option"],
            [new Button($lang["gui_btn_manage_existing"]),
            new Button($lang["gui_btn_create_region"]),
            new Button($lang["gui_btn_redefine_region"]),
            new Button($lang["gui_btn_delete_region"])],
            function(Player $player, Button $selected) : void{

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

    public static function displayRgCreation(Player $issuer)
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
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName, $extended) = $response->getValues();
                if($extended === true)
                    $player->getServer()->dispatchCommand($player, "rg create $rgName extended");
                else
                    $player->getServer()->dispatchCommand($player, "rg create $rgName");
            }
        ));
    }

    public static function displayRgRedefine(Player $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $regions = array_keys(Utils::getPluginFromIssuer($issuer)->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_redefine"], $regions),
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg redefine $rgName");
            }
        ));
    }

    public static function displayRgDelete(Player $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_delete"], $regions),
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg delete $rgName");
            }
        ));
    }

    public static function displayExistingRegions(Player $issuer)
    {
        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();
        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_rg_management"],
            [
                new Dropdown($lang["gui_dropdown_select_manage"], $regions),
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                self::displayRgEditing($player, $rgName);
            }
        ));
    }

    public static function displayRgEditing(Player $issuer, $rgName)
    {
        $rg = $issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegion($rgName);
        self::$currentlyEditedRg = $rgName;

        $lang = Utils::getPluginFromIssuer($issuer)->resourceManager->getLanguagePack();

        $issuer->sendForm(new CustomForm($lang["gui_manage_menu_title"] . " §9" . $rgName,
            [
                new Toggle($lang["gui_flag_pluginbypass"], filter_var($rg->getFlag("pluginbypass"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_pvp"], filter_var($rg->getFlag("pvp"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_xp_drops"], filter_var($rg->getFlag("exp-drops"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_invincible"], filter_var($rg->getFlag("invincible"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_fall_dmg"],filter_var($rg->getFlag("fall-dmg"), FILTER_VALIDATE_BOOLEAN)),
                // add flag for effects
                // add flag for blocked commands
                // add flag for allowed commands
                new Toggle($lang["gui_flag_usage"], filter_var($rg->getFlag("use"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle($lang["gui_flag_item_drop"], filter_var($rg->getFlag("item-drop"), FILTER_VALIDATE_BOOLEAN)),
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
            function(Player $player, CustomFormResponse $response) : void{
                list($pluginbypass, $pvpFlag, $xpFlag, $invincibleFlag, $fallDmgFlag, $useFlag, $itemDropFlag, $explosionsFlag,
                    $notifyEnterFlag, $notifyLeaveFlag, $potionsFlag, $allowEnterFlag, $allowLeaveFlag,
                    $gamemodeFlag, $sleepFlag, $sendChatFlag, $receiveChatFlag, $enderPearlFlag, $flyModeFlag, $eatingFlag, $HungerFlag,
                    $damageAnimalsFlag, $damageMonstersFlag, $leafDecayFlag, $plantGrowthFlag, $spreadingFlag, $blockBurnFlag,
                    $priorityFlag) = $response->getValues();

                $lang = Utils::getPluginFromIssuer($player)->resourceManager->getLanguagePack();

                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" pluginbypass " . var_export($pluginbypass, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" pvp " . var_export($pvpFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" exp-drops " . var_export($xpFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" invincible " . var_export($invincibleFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" fall-dmg " . var_export($fallDmgFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" use " . var_export($useFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" item-drop " . var_export($itemDropFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" explosion " . var_export($explosionsFlag, true));
                if($notifyEnterFlag != '' || $notifyEnterFlag != ' '){$player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" notify-enter " . $notifyEnterFlag);}
                if($notifyLeaveFlag != '' || $notifyLeaveFlag != ' '){$player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" notify-leave " . $notifyLeaveFlag);}
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" potions " . var_export($potionsFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allowed-enter " . var_export($allowEnterFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" allowed-leave " . var_export($allowLeaveFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" sleep " . var_export($sleepFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" send-chat " . var_export($sendChatFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" receive-chat " . var_export($receiveChatFlag, true));
                $player->getServer()->dispatchCommand(new ConsoleCommandSender(), "rg flags set \"" . self::$currentlyEditedRg . "\" enderpearl " . var_export($enderPearlFlag, true));

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

    public static function displayHelpMenu(Player $issuer)
    {
        $plugin = Utils::getPluginFromIssuer($issuer);
        $lang = $plugin->resourceManager->getLanguagePack();

        $issuer->sendForm(new CustomForm("§9§l" . $lang["gui_btn_help"],
            [
                new Label($lang["gui_help_menu_label1"]),
                new Label($lang["gui_help_menu_label2"]),
            ],
            function(Player $player, CustomFormResponse $response) : void{}
        ));

    }
}
