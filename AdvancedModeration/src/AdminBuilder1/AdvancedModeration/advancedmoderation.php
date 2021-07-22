<?php

namespace AdminBuilder1\AdvancedModeration;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\TextFormat;



class AdvancedModeration extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("AdvancedModerator is enabled");
	}
	public function onDisabled(){
		$this->getLogger()->info("AdvancedModerator is disabled");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $agrs) : bool{
		 switch($cmd->getName()) {
			 # cmd
			case "advancemod":
			case "admod":
		
					# default
				if($sender->hasPermission("advancemod.cmd")){
				if($sender instanceof Player){
						$sender->sendMessage("try using /[advancemod|admod] <help>".$agrs);
					}else{
						$sender->sendMessage("Cannot use it console");
					}
			}else {
				$sender->sendMessage("You do not have permission");
			}
			break;
			break;
			# cmd_1
			case "advancemod.help":
			case "admod.help":
					
				if($sender->hasPermission("advancemod.cmd.help")){
				if($sender instanceof Player){
						$sender->sendMessage("Here are some commands\n-help\n-annouce");
					}else{
						$sender->sendMessage("Cannot use it console");
					}
			}else {
				$sender->sendMessage("You do not have permission");
			}
			
			break;
			break;
			# cmd_2
			case "advancemod.annouce":
			case "admod.annouce":
		
			if($sender->hasPermission("advancemod.cmd.annouce")){
				if($sender instanceof Player){
						$sender->sendMessage("test");
					}else{
						$sender->sendMessage("Cannot use it console");
					}
			}else {
				$sender->sendMessage("You do not have permission");
			}
			break;
			break;
		
	}
		return true;
	}
}
 