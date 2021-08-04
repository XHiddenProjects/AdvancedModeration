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
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig(); // Saves config.yml if not created.
		$this->getResource("config.yml");
		$this->reloadConfig(); // Fix bugs sometimes by getting configs values
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info(C::GREEN . "AdvancedModerator is enabled");
	}
	public function onDisabled(){
		$this->getLogger()->info(C::RED . "AdvancedModerator is disabled");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		
		 switch($cmd->getName()) {
			 # cmd
			case "advancedmod":
			case "advmod":
//if($this->getConfig()->get("execute") === true){
			# default
			if($sender instanceof Player){
				$config = $this->getConfig();
				$username = $config->get("username");
				# encode
				if($config->get("encode-username") === true){
					$username = base64_encode($username);
				}else{
					$username = $username;
				}
				
				if(!isset($args[0])){
					 if (!$sender->hasPermission("advancedmod.command")) {
                            $sender->sendMessage(C::RED . "You do not have the permission to use command");
                            return true;
                        } else {
                            $sender->sendMessage("Please type '/advancedmod|advmod help'.");
                            return true;
                        }
				
				}
foreach($config->get("by-pass-op") as $bypass){
		# help cmd
				if(count($args) >= 1 && $args[0] === "help"){
					if(!$sender->hasPermission("advancedmod.help")){
					$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
				}else{
					if(count($args) < 2){
						$sender->sendMessage(C::RED . "You must have a page number");
						return true;
					}
					array_shift($args);
					if($args[0] === "1"){
					$sender->sendMessage(C::GREEN . "List of commands(1/1):" . C::WHITE . "\n- help <pg>\n- annouce <msg>\n- pmsg <player> <msg>\n- getIP <player>\n- gm <mode> <target>\n- gmall <mode>\n- tpto <player>");
					return true;	
					}
					
					
				}
			}
			#annouce cmd
				if(count($args) >= 1 && $args[0] === "announce" && $config->get("announce") === true){
					if(!$sender->hasPermission("advancedmod.announce")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
					}else{
						
						foreach($this->getServer()->getOnlinePlayers() as $p){
							array_shift($args);
								$txt = implode(" ", $args);
									if($config->get("encode-messages") === true){
										$p->sendMessage(C::GREEN . "[" . $username . "] ". C::YELLOW . "> " . C::WHITE . base64_encode($txt));
									}else{
									$p->sendMessage(C::GREEN . "[" . $username . "] ". C::YELLOW . "> " . C::WHITE . $txt);
								}
								
							
						}
					
					}
				}
			# private cmd
				if(count($args) >= 1 && $args[0] === "pmsg" && $config->get("pmsg") === true){
					if(!$sender->hasPermission("advancedmod.pmsg")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
					}else{
					if(count($args) < 3){
						$sender->sendMessage(C::RED . "You must have a player and a message");
						return true;
					}
						array_shift($args);
							if($this->getServer()->getPlayer($args[0])){
									$getUser = $this->getServer()->getPlayer($args[0]);
									array_shift($args);
									$txt = implode(" ", $args);
									if($config->get("encode-messages") === true){
									$getUser->sendMessage(C::GREEN . "[" . $username . "] " . C::YELLOW . " > ". C::GRAY ."[" . $getUser->getName() . "] " . C::YELLOW . "> " . base64_encode($txt));
									}else{
									$getUser->sendMessage(C::GREEN . "[" . $username . "] " . C::YELLOW . " > ". C::GRAY ."[" . $getUser->getName() . "] " . C::YELLOW . "> " . $txt);
									}
									
									return true;
								
							}else{
								$sender->sendMessage(C::RED . "Player not found!");
								return true;
							}
					}
				}
			# gamemodes
				if(count($args) >= 1 && $args[0] === "gm" && $config->get("gamemode") === true){
					if(!$sender->hasPermission("advancedmod.gm")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
					}else{
						if(count($args) < 3){
						$sender->sendMessage(C::RED . "You must have a player and a gamemode");
						return true;
					}
						array_shift($args);
						if($args[0] === "0" || $args[0] === "1" || $args[0] === "2" || $args[0] === "3"){# 3 main targets
							$query = $args[0];
							if($this->getServer()->getPlayer($args[1])){
								$user = $this->getServer()->getPlayer($args[1]);
								$user->setGamemode($query);
								if($query === "0"){
									$sender->sendMessage("You are now in Survival");
									return true;
								}
								if($query === "1"){
									$sender->sendMessage("You are now in Creative");
									return true;
								}
								if($query === "2"){
									$sender->sendMessage("You are now in Survival");
									return true;
								}
								if($query === "3"){
									$sender->sendMessage("You are now in Spectator");
									return true;
								}
								return true;
							}else{
								$sender->sendMessage(C::RED . "Player not found!");
								return true;
							}
						}else{
							$sender->sendMessage(C::RED . "invalid gamemode");
							return true;
						}
					}
				}
			#gmall
			if(count($args) >= 1 && $args[0] === "gmall" && $config->get("gamemodeall") === true){
				if(!$sender->hasPermission("advancedmod.gmall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
					}else{
						if(count($args) < 2){
						$sender->sendMessage(C::RED . "You must have a gamemode");
						return true;
						}
						foreach($this->getServer()->getOnlinePlayers() as $p){
							if($p->getName() !== $bypass){
								//do anyone but bypass
								array_shift($args);
								if($args[0] === "0" || $args[0] === "1" || $args[0] === "2" || $args[0] === "3"){
									$p->setGamemode($args[0]);
									$renameArgs = explode(" ", $p->getName());
									$sender->sendMessage("Successfully changed everyones gamemode " . C::GRAY . implode(",", $renameArgs));
									return true;
								}else{
								$sender->sendMessage(C::RED . "invalid gamemode");
								return true;
								}
							}else{
								$renameArgs = explode(" ", $p->getName());
								$sender->sendMessage("Can't update " . C::GRAY . implode(",", $renameArgs) . " do to bypass");
								return true;
							}
						}
					}
			}
			
			#tp
				if(count($args) >= 1 && $args[0] === "tpto" && $config->get("teleport") === true){
					if(!$sender->hasPermission("advancedmod.tp")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						if(count($args) < 2){
							$sender->sendMessage(C::RED . "Must include a username");
							return true;
						}
							//player
							array_shift($args);
							if($this->getServer()->getPlayer($args[0])){
								$player = $this->getServer()->getPlayer($args[0]);
								$sender->teleport($player->getPosition());
								$sender->sendMessage("Successfully teleport to " . C::GRAY . $player->getName());
								return true;
							}else{
								$sender->sendMessage(C::RED . "Player not found!");
								return true;
							}
						
					}
				}
				
				
			# GET info
			if(count($args) >= 1 && $args[0] === "getIP" && $config->get("getIP") === true){
				if(!$sender->hasPermission("advancedmod.getIP")){
					$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
				}else{
					if(count($args) < 2){
						$sender->sendMessage(C::RED . "You must have a player and a message");
						return true;
					}
					array_shift($args);
					if($this->getServer()->getPlayer($args[0])){
						$info = $this->getServer()->getPlayer($args[0]);
						$sender->sendMessage("User " . C::GRAY . $info->getName() . C::YELLOW . " IPA is: " . C::WHITE . $info->getAddress());
						return true;
					}else{
					$sender->sendMessage(C::RED . "Player not found!");
					return true;
					}
				}
			}
	
				
//end commands ^ here
		}
			
}
//}
			
				
			break;
			break;
		
	}
		return true;
	}
}
 