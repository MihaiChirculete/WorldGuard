# WorldGuard-Advanced
A World Management plugin for PocketMine-MP and forks.
Read the [tutorial](https://github.com/Muqsit/WorldGuard/wiki/Tutorial).

Extended capabilities added by Chalapa.

Download a compiled .phar file [here](https://github.com/MihaiChirculete/WorldGuard/tree/master/compiled).

# Flags added on top of Musqit's version
- eat (boolean): permit/deny eating inside area. known issues: if the player warps inside the area with food already being held in hand, he can eat

- allow-damage-animals (boolean): permit/deny damaging animals.
		tested with animals from [PureEntitiesX](https://github.com/RevivalPMMP/PureEntitiesX), but should work with other plugins too
		as long as the mob contains the word 'animal' in its classname

- allow-damage-monsters (boolean): permit/deny damaging monsters.
		tested with monsters from [PureEntitiesX](https://github.com/RevivalPMMP/PureEntitiesX), but should work with other plugins too
		as long as the mob contains the word 'monster' in its classname

- allow-leaves-decay (boolean): allow/prevent leaf decay