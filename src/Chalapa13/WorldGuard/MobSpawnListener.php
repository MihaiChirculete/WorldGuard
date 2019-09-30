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
use pocketmine\block\Block;
use pocketmine\event\block\{BlockPlaceEvent, BlockBreakEvent, LeavesDecayEvent, BlockGrowEvent, BlockSpreadEvent, BlockBurnEvent};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent, EntityExplodeEvent, ProjectileLaunchEvent};
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerMoveEvent, PlayerInteractEvent, PlayerCommandPreprocessEvent, PlayerDropItemEvent, PlayerBedEnterEvent, PlayerChatEvent, PlayerItemHeldEvent};
use pocketmine\item\Item;
use pocketmine\item\Food;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\{Entity, Animal, Monster};
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\level\Position;
use revivalpmmp\pureentities\event\CreatureSpawnEvent;

class MobSpawnListener implements Listener {

    private $plugin;

    public function __construct(WorldGuard $plugin)
    {
        $this->plugin = $plugin;

        if($plugin->pureEntitiesPlugin !== null)
            $plugin->getServer()->getPluginManager()->registerEvent(CreatureSpawnEvent::class, $this, 3, new MethodEventExecutor("onMobSpawn"), $plugin, false);
    }

    public function onMobSpawn(CreatureSpawnEvent $event)
    {
        if(($region = $this->plugin->getRegionFromPosition($event->getPosition())) !== "")
            if($region->getFlag("allow-mob-spawning") === "false")
                $event->setCancelled();
    }
}
