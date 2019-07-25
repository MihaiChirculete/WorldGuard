# WorldGuard-Advanced [![Poggit-CI](https://poggit.pmmp.io/ci.badge/Chalapa13/WorldGuard/WorldGuard)](https://poggit.pmmp.io/ci/Chalapa13/WorldGuard/WorldGuard)

This plugin is a port of the popular bukkit plugin called ["WorldGuard"](https://dev.bukkit.org/projects/worldguard). It was originally developed by [Muqsit](https://github.com/Muqsit), but more flags and extended capabilities were added by me.

# Features
- Easily define regions in your world.
- Add custom flags on defined regions that allow/restrict certain actions and events.
- Delete/Modify already defined regions.

# Utility
- Defined regions can be used to protect specific areas of your world from griefing, pvp, damaging mobs, building, and some other events.  
For the full list of flags check the tutorial below.

# Tutorial
Read the [tutorial](https://github.com/Muqsit/WorldGuard/wiki/Tutorial).  

For the added flags check [here](https://github.com/Chalapa13/WorldGuard).  
Be sure to check the above link when updating the plugin to see changes and view available flags. (can also be found in-game by doing /rg flags get region_name)

# Dev builds
For more frequent updates (risk of bugs) you can download the plugin from [here](https://poggit.pmmp.io/ci/Chalapa13/WorldGuard/WorldGuard).

Download a compiled .phar file [here](https://github.com/Chalapa13/WorldGuard/tree/master/compiled).

# Flags added on top of Muqsit's version
- eat (boolean): permit/deny eating inside area. known issues: if the player warps inside the area with food already being held in hand, he can eat

- allow-damage-animals (boolean): permit/deny damaging animals.
		tested with animals from [PureEntitiesX](https://github.com/RevivalPMMP/PureEntitiesX), but should work with other plugins too
		as long as the mob contains the word 'animal' in its classname

- allow-damage-monsters (boolean): permit/deny damaging monsters.
		tested with monsters from [PureEntitiesX](https://github.com/RevivalPMMP/PureEntitiesX), but should work with other plugins too
		as long as the mob contains the word 'monster' in its classname

- allow-leaves-decay (boolean): allow/prevent leaf decay

- allow-plant-growth (boolean): allow/prevent growth of plants such as grass, flowers, vines, seeds

- allow-spreading (boolean): allow/prevent block spreading such as mycelium, grass, etc.
