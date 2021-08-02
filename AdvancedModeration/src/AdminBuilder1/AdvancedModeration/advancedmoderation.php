<?php

namespace AdminBuilder1\AdvancedModeration;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat;
# commands
use AdminBuilder1\AdvancedModeration\command\help;

class AdvancedModeration extends PluginBase implements Listener {
	public $mainargs = [
	"help",
	"annouce"
	];
	
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("AdvancedModerator is enabled");
	}
	public function onDisabled(){
		$this->getLogger()->info("AdvancedModerator is disabled");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		
		 switch($cmd->getName()) {
			 # cmd
			case "advancedmod":
			case "advmod":
					# default
			if($sender instanceof Player){
				if(!isset($args[0])){
					 if (!$sender->hasPermission("advancedmod.cmd")) {
                            $sender->sendMessage($this->noperm);
                            return true;
                        } else {
                            $sender->sendMessage("Please type '/advancedmod|advmod help'.");
                            return true;
                        }
				
				}
			}
				
			break;
			break;
		
	}
		return true;
	}
}
 