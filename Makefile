plugin_name = WorldGuard-Advanced
console_script = ~/PocketMine-MP/plugins/PocketMine-DevTools-master/src/DevTools/ConsoleScript.php

default:
	php -dphar.readonly=0 $(console_script) --make ./ --out compiled/$(plugin_name).phar

clean:
	rm compiled/$(plugin_name).phar

install: default
	cp compiled/$(plugin_name).phar ../
