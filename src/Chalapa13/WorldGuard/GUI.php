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
        $issuer->sendForm(new MenuForm(
            "§9§lWorld Guard Menu", "Choose an option", [new Button("§6§lRegion Management", new Image("textures/items/book_writable", "path")),
            new Button("§5§lHelp")],
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
        $issuer->sendForm(new MenuForm(
            "§9§lRegion Management", "Choose an option",
            [new Button("Manage existing region"),
            new Button("Create a new region"),
            new Button("Redefine a region"),
            new Button("Delete a region")],
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
        $issuer->sendForm(new CustomForm("§9§lRegion Creation",
            [
                new Label("Let's help you create a region."),
                new Input("First you will have to enter a name for your region.", "MyRegion"),
                new Label("If you want your region to expand infinitely upwards and downards check the following option."),
                new Toggle("Expand vertically", false),
                new Label("Now hit the §a'Submit'§r and select 2 corners of your region as you will be instructed next.")
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
        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§lRegion Management",
            [
                new Dropdown("Select the region you would like to redefine", $regions),
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg redefine $rgName");
            }
        ));
    }

    public static function displayRgDelete(Player $issuer)
    {
        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§lRegion Management",
            [
                new Dropdown("Select the region you would like to delete", $regions),
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($rgName) = $response->getValues();
                $player->getServer()->dispatchCommand($player, "rg delete $rgName");
            }
        ));
    }

    public static function displayExistingRegions(Player $issuer)
    {
        $regions = array_keys($issuer->getServer()->getPluginManager()->getPlugin("WorldGuard")->getRegions());

        $issuer->sendForm(new CustomForm("§9§lRegion Management",
            [
                new Dropdown("Select the region you would like to manage", $regions),
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

        $issuer->sendForm(new CustomForm("Managing region: §9" . $rgName,
            [
                new Toggle("PvP", filter_var($rg->getFlag("pvp"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Experience drops", filter_var($rg->getFlag("exp-drops"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Invincible", filter_var($rg->getFlag("invincible"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Fall damage",filter_var($rg->getFlag("fall-dmg"), FILTER_VALIDATE_BOOLEAN)),
                // add flag for effects
                // add flag for blocked commands
                // add flag for allowed commands
                new Toggle("Use", filter_var($rg->getFlag("use"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Item drop", filter_var($rg->getFlag("item-drop"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Explosions", filter_var($rg->getFlag("explosion"), FILTER_VALIDATE_BOOLEAN)),
                new Input("Notify enter", $rg->getFlag("notify-enter")),
                new Input("Notify leave", $rg->getFlag("notify-leave")),
                new Toggle("Potions", filter_var($rg->getFlag("potions"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allowed enter", filter_var($rg->getFlag("allowed-enter"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allowed leave", filter_var($rg->getFlag("allowed-leave"), FILTER_VALIDATE_BOOLEAN)),
                new Dropdown("Gamemode", ["Survival", "Creative"]),
                new Toggle("Allow sleeping", filter_var($rg->getFlag("sleep"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow sending chat messages", filter_var($rg->getFlag("send-chat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow receiving chat messages", filter_var($rg->getFlag("receive-chat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow use of ender pearls", filter_var($rg->getFlag("enderpearl"), FILTER_VALIDATE_BOOLEAN)),
                new Dropdown("Fly mode", ["Vanilla", "Enabled", "Disabled", "Supervised"]),
                new Toggle("Allow eating", filter_var($rg->getFlag("eat"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow damaging of animals", filter_var($rg->getFlag("allow-damage-animals"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow damaging of monsters", filter_var($rg->getFlag("allow-damage-monsters"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow leaf decay", filter_var($rg->getFlag("allow-leaves-decay"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow plant growth", filter_var($rg->getFlag("allow-plant-growth"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow spreading", filter_var($rg->getFlag("allow-spreading"), FILTER_VALIDATE_BOOLEAN)),
                new Toggle("Allow block burn", filter_var($rg->getFlag("allow-block-burn"), FILTER_VALIDATE_BOOLEAN)),
                new Input("Region priority", filter_var($rg->getFlag("priority"), FILTER_VALIDATE_INT))
            ],
            function(Player $player, CustomFormResponse $response) : void{
                list($pvpFlag, $xpFlag, $invincibleFlag, $fallDmgFlag, $useFlag, $itemDropFlag, $explosionsFlag,
                    $notifyEnterFlag, $notifyLeaveFlag, $potionsFlag, $allowEnterFlag, $allowLeaveFlag,
                    $gamemodeFlag, $sleepFlag, $sendChatFlag, $receiveChatFlag, $enderPearlFlag, $flyModeFlag, $eatingFlag,
                    $damageAnimalsFlag, $damageMonstersFlag, $leafDecayFlag, $plantGrowthFlag, $spreadingFlag, $blockBurnFlag,
                    $priorityFlag) = $response->getValues();

                var_dump($pvpFlag);
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " pvp " . var_export($pvpFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " exp-drops " . var_export($xpFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " invincible " . var_export($invincibleFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " fall-dmg " . var_export($fallDmgFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " use " . var_export($useFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " item-drop " . var_export($itemDropFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " explosion " . var_export($explosionsFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " notify-enter " . $notifyEnterFlag);
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " notify-leave " . $notifyLeaveFlag);
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " potions " . var_export($potionsFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allowed-enter " . var_export($allowEnterFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allowed-leave " . var_export($allowLeaveFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " game-mode " . strtolower($gamemodeFlag));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " sleep " . var_export($sleepFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " send-chat " . var_export($sendChatFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " receive-chat " . var_export($receiveChatFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " enderpearl " . var_export($enderPearlFlag, true));
                switch ($flyModeFlag)
                {
                    case "Vanilla":
                        $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " fly-mode 0");
                        break;

                    case "Enabled":
                        $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " fly-mode 1");
                        break;

                    case "Disabled":
                        $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " fly-mode 2");
                        break;

                    case "Supervised":
                        $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " fly-mode 3");
                        break;
                }

                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " eat " . var_export($eatingFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-damage-animals " . var_export($damageAnimalsFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-damage-monsters " . var_export($damageMonstersFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-leaves-decay " . var_export($leafDecayFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-plant-growth " . var_export($plantGrowthFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-spreading " . var_export($spreadingFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " allow-block-burn " . var_export($blockBurnFlag, true));
                $player->getServer()->dispatchCommand($player, "rg flags set " . self::$currentlyEditedRg . " priority " . intval($priorityFlag));

                self::$currentlyEditedRg = "";
            }
        ));
    }

    public static function displayHelpMenu(Player $issuer)
    {
        $issuer->sendForm(new CustomForm("§9§lHelp",
            [
                new Label("If you need help setting up world guard check out the tutorial we made for you:"),
                new Label("§9§lhttps://github.com/Chalapa13/WorldGuard/wiki/Tutorial")
            ],
            function(Player $player, CustomFormResponse $response) : void{}
        ));

    }
}