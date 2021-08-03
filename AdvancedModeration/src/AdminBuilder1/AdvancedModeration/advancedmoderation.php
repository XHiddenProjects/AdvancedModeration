<?php

namespace AdminBuilder1\AdvancedModeration;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat as C;
# commands
use AdminBuilder1\AdvancedModeration\command\help;

class AdvancedModeration extends PluginBase implements Listener {
	public $mainargs = [
	"help",
	"annouce"
	];
	
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info(C::GREEN . "AdvancedModerator is enabled");
		$this->saveDefaultConfig(); // Saves config.yml if not created.
		$this->reloadConfig(); // Fix bugs sometimes by getting configs values
	}
	public function onDisabled(){
		$this->getLogger()->info(C::RED . "AdvancedModerator is disabled");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		
		 switch($cmd->getName()) {
			 # cmd
			case "advancedmod":
			case "advmod":
//if(){
			# default
			if($sender instanceof Player){
				if(!isset($args[0])){
					 if (!$sender->hasPermission("advancedmod.command")) {
                            $sender->sendMessage($this->noperm);
                            return true;
                        } else {
                            $sender->sendMessage("Please type '/advancedmod|advmod help'.");
                            return true;
                        }
				
				}
				# help cmd
				if(count($args) == 1 && $args[0] === "help"){
					if(!$sender->hasPermission("advancedmod.help")){
					$sender->sendMessage($this->noperm);
                  return true;
				}else{
					$sender->sendMessage(C::GREEN . "List of commands:" . C::WHITE . "\n- help(list helps)\n- annouce(sends a public message)");
					return true;
				}
			}
			#annouce cmd
				if(count($args) >= 1 && $args[0] === "announce"){
					if(!$sender->hasPermission("advancedmod.announce")){
						$sender->sendMessage($this->noperm);
                  return true;
					}else{
						
						foreach($this->getServer()->getOnlinePlayers() as $p){
							array_shift($args);
								$txt = implode(" ", $args);
								$p->sendMessage(C::RED . "[administrator] > " . C::YELLOW . $txt);
							
						}
					
					}
				}
			}
//}
			
				
			break;
			break;
		
	}
		return true;
	}
}
 