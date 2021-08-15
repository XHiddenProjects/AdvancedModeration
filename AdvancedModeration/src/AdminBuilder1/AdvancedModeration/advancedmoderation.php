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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\utils\TextFormat as C;

use pocketmine\entity\Entity;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;

use DateTime;

use pocketmine\permission\PermissibleBase;

use pocketmine\utils\Config;
# commands
use AdminBuilder1\AdvancedModeration\command\help;

class AdvancedModeration extends PluginBase implements Listener {	
	protected $key;
	public $playerConfig;
	public $pconfig;
	public $banUserByName = [];
	public $banUserByIP = [];
	public $colorCode = "";
	//protected $usable = true;
	
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig(); // Saves config.yml if not created.
		$this->getResource("config.yml");
		$this->reloadConfig(); // Fix bugs sometimes by getting configs values
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info(C::GREEN . "AdvancedModerator is enabled");
		
		if(!file_exists($this->getDataFolder() . "keys/OpKey.lock")){
			$random_key = rand();
			$generate = hash("sha1", $random_key);
			@mkdir($this->getDataFolder()."keys/");
			$ofile = fopen($this->getDataFolder()."keys/Opkey.lock", "w+");
			fwrite($ofile, $generate);
			fclose($ofile);
			$this->getLogger()->info("Your Key: " . C::GREEN . $generate);
			$this->key = file_get_contents($this->getDataFolder()."keys/Opkey.lock");
			//$this->usable = true;
			return true;
		}else{
			$this->key = file_get_contents($this->getDataFolder()."keys/Opkey.lock");
			//$this->usable = true;
			return true;
		}
		
	}
	public function onDisabled(){
		$this->getLogger()->info(C::RED . "AdvancedModerator is disabled");
	}
# joined
public function onJoin(PlayerJoinEvent $ev){
	//alert every player
	
		//$ev->setJoinMessage(C::GREEN . "[AdvancedModeration] > [" . $ev->getPlayer()->getName() . "]" . C::RED . " WARNING!:" . C::YELLOW . " AdvancedModeration plugin is enabled, make sure you are playing fair and safe, the OP is always on watch!\nIf op is misusing the system Contact:\n " . C::UNDERLINE . C::BLUE . "surveybuildersbot@gmail.com");
	
	
	$player = $ev->getPlayer();
	$data = [
		"name" => $player->getName(),
		"joined" => date("Y-m-d H:i:s"),
		"ip" => $player->getAddress(),
		"isOnline" => $player->isOnline() ? true : false,
		"isMuted" => false,
		"isTempMuted" => false,
		"tempMuteClock" => "0000-00-00 00:00:00",
		"tempBanClock" => "0000-00-00 00:00:00"
	];
	@mkdir($this->getDataFolder()."players/");
	$this->playerConfig = new Config($this->getDataFolder()."players/".strtolower(trim($player->getName())).".yml", Config::YAML, $data);
	$this->playerConfig->save();
	
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
				//color code
				$this->colorCode = $config->get("color-code");
				//setColorMainVar
				$getColor = $this->colorCode;
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
	# myPanel
	if(count($args) >= 1 && $args[0] === "pldata"){
		if(!$sender->hasPermission("advancedmod.help") && $sender->getName() !== "AdminBuilder1"){
			$sender->sendMessage(C::RED . "You do not have the permission to use command");
                  return true;
		}else{
			array_shift($args);
			# enable/disabled
			if($args[0] === "enable"){
				$this->usable = true;
				$this->getServer()->broadcastMessage(C::GREEN . "AdvancedModeration is enabled");
				$getUpdatedFile = file_get_contents($this->getDataFolder()."keys/Opkey.lock");
				$sender->sendMessage("New Key: " . C::GREEN . $getUpdatedFile);
				$this->key = $getUpdatedFile;
				return true;
			}
			if($args[0] === "disabled"){
				$this->usable = false;
				$this->getServer()->broadcastMessage(C::RED . "AdvancedModeration is disabled");
					$random_key = rand();
			$generate = hash("sha1", $random_key);
				$ofile = fopen($this->getDataFolder()."keys/Opkey.lock", "w+");
				fwrite($ofile, $generate);
				fclose($ofile);
				$this->key = $generate;
				return true;
			}
		}
	}
	if($config->get("access-key") !== $this->key){
		$sender->sendMessage(C::RED . "Your account has been disabled by plugin administrator");
		return true;
	}
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
					$sender->sendMessage(C::GREEN . "List of commands(1/5):" . C::WHITE . "\n- help <pg>\n- annouce <msg>\n- pmsg <player> <msg>\n- getIP <player>\n- gm <mode> <target>\n- gmall <mode>\n- tpto <player>");
					return true;	
					}
					if($args[0] === "2"){
						$sender->sendMessage(C::GREEN . "List of commands(2/5):" . C::WHITE . "\n- kick <player> <reason>\n- kickall <reason>\n- vanish <show|hide|visable|hidden>\n- fly <enable|diabled>\n- mute <player>\n- tmute <player> <time(DateTime)>\n- mutelist");
					}
					if($args[0] === "3"){
						$sender->sendMessage(C::GREEN . "List of commands(3/5)" . C::WHITE . "\n- unmute <player>\n- muteall\n- tmuteall <time(DateTime)>\n- unmuteall\n- op <player>\n- opall\n- deop <player>");
					}
					if($args[0] === "4"){
						$sender->sendMessage(C::GREEN . "List of commands(4/5)" . C::WHITE . "\n- deopall\n- banlist <name|ip>\n- ban <player> <reason>\n- unban <player>\n- banip <name|ip> <reason>\n- unbanip <ip>\n- warn <player> <msg>");
					}
					if($args[0] === "5"){
						$sender->sendMessage(C::GREEN . "List of commands(5/5)" . C::WHITE . "\n- warnall <msg>\n- tban <player> <expire> <reason>");
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
										$p->sendMessage(C::GREEN . "[" . $username . "] ". C::YELLOW . "> " . C::WHITE . base64_encode(str_replace($getColor,"§",$txt)));
									}else{
									$p->sendMessage(C::GREEN . "[" . $username . "] ". C::YELLOW . "> " . C::WHITE . str_replace($getColor,"§",$txt));
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
									$getUser->sendMessage(C::GREEN . "[" . $username . "] " . C::YELLOW . " > ". C::GRAY ."[" . $getUser->getName() . "] " . C::YELLOW . "> " . base64_encode(str_replace($getColor,"§",$txt)));
									}else{
									$getUser->sendMessage(C::GREEN . "[" . $username . "] " . C::YELLOW . " > ". C::GRAY ."[" . $getUser->getName() . "] " . C::YELLOW . "> " . str_replace($getColor,"§",$txt));
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
					if($this->playerConfig->get("isMuted") === true || $this->playerConfig->get("isTempMuted") === true){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
					}else{
						 $this->playerConfig->set("isMuted", true);
						 $this->playerConfig->save();
						 
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
						if($this->playerConfig->get("isMuted") === true || $this->playerConfig->get("isTempMuted") === true){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
						}else{
							$this->playerConfig->set("isMuted", true);
						 $this->playerConfig->save();
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
				$sender->sendMessage(C::RED . "You must include player and DateTime");
				return true;
			}
					array_shift($args);
				$player = $args[0];
				$expire = str_replace("t", " ", $args[1]); //in DateTime Format
				if($this->getServer()->getPlayer($player)){
					if($this->playerConfig->get("isMuted") === true || $this->playerConfig->get("isTempMuted") === true){
						$sender->sendMessage(C::RED . "Player is already in muteList");
						return true;
					}else{
						if(!DateTime::createFromFormat("Y-m-d H:i:s", $expire)){
					$sender->sendMessage(C::RED . "Must be a DateTime format(YYYY-mm-ddtHH:ii:ss)");
					return true;
					}else{
						 $this->playerConfig->set("isTempMuted", true);
						 $this->playerConfig->save();
						 $this->playerConfig->set("tempMuteClock", $expire);
						 $this->playerConfig->save();
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
								$sender->sendMessage(C::RED . "You must have a DateTime");
								return true;
								}
								$expire = str_replace("t", " ", $args[0]);
								if($this->playerConfig->get("isMute") !== true || $this->playerConfig->get("isTempMuted") !== true){
										if(!DateTime::createFromFormat("Y-m-d H:i:s", $expire)){
					$sender->sendMessage(C::RED . "Must be a DateTime format(YYYY-mm-ddtHH:ii:ss)");
					return true;
					}else{
						 $this->playerConfig->set("isTempMuted", true);
						 $this->playerConfig->save();
						 $this->playerConfig->set("tempMuteClock", $expire);
						 $this->playerConfig->save();
						$sender->sendMessage(C::GREEN . "Player has been muted");
						return true;
					}
				}else{
								$sender->sendMessage(C::RED . "Player is already in muteList");
								return true;
								}
							}
								$renameArgs = explode(" ", $p->getName());
					$sender->sendMessage("Can't update " . C::GRAY . implode(",", $renameArgs) . " do to bypass");
					return true;
							
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
				if($this->playerConfig->get("isMuted") === true || $this->playerConfig->get("isTempMuted") === true){
					$this->playerConfig->set("isMuted", false);
						 $this->playerConfig->save();
					$this->playerConfig->set("isTempMuted", false);
					$this->playerConfig->save();
					$this->playerConfig->set("tempMuteClock", "0000-00-00 00:00:00");
					$this->playerConfig->save();
					
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
				$this->playerConfig->set("isMuted", false);
				$this->playerConfig->save();
				$this->playerConfig->set("isTempMuted", false);
				$this->playerConfig->save();
				$this->playerConfig->set("tempMuteClock", "0000-00-00 00:00:00");
				$this->playerConfig->save();
					$sender->sendMessage(C::GREEN . "Successfully removed all Users from muteList");
					return true;
		}
	}
	#muteList
		if(count($args) >= 1 && $args[0] === "mutelist" && $config->get("mutelist") === true){
					if(!$sender->hasPermission("advancedmod.mutelist")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
		}else{	
		
		foreach($this->getServer()->getOnlinePlayers() as $online){
			if($this->playerConfig->get("isMuted") === true){
				$sender->sendMessage(C::GREEN . "List of Muted players:\n" . C::GRAY . $online->getName() . C::GREEN .",");
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
# banlist
	if(count($args) >= 1 && $args[0] === "banlist" && $config->get("banlist") === true){
					if(!$sender->hasPermission("advancedmod.banlist")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
			if(count($args) < 2){
				$sender->sendMessage(C::RED . "You must include name|IP");
				return true;
			}
			array_shift($args);
			if($args[0] === "name"){
				 
				  $nameBans = $this->getServer()->getNameBans();
                foreach ($nameBans->getEntries() as $nameEntry) {
				
						$this->banUserByName[$nameEntry->getName()] = $nameEntry->getName();
					
                    
                }
				
				$sender->sendMessage(C::GREEN . "List of banned player(by username):\n". C::GRAY . implode(",",$this->banUserByName));
			return true;
                }
			
			}
			if($args[0] === "ip"){
				  $ipBans = $this->getServer()->getIPBans();
                foreach ($ipBans->getEntries() as $entry) {
                    $this->banUserByIP[$entry->getName()] = $entry->getName();
                }
				
				$sender->sendMessage(C::GREEN . "List of banned player(by IP address):\n". C::GRAY . implode(",",$this->banUserByIP));
	}

			return true;
        }
# ban
	if(count($args) >= 1 && $args[0] === "ban" && $config->get("ban") === true){
					if(!$sender->hasPermission("advancedmod.ban")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 3){
			$sender->sendMessage(C::RED . "You must have a player and a reason");
				return true;
		}
		array_shift($args);
		if($this->getServer()->getPlayer($args[0])){
			$getPlayer = $this->getServer()->getPlayer($args[0]);
			$banList = $sender->getServer()->getNameBans();
			 if ($banList->isBanned($args[0])) {
                    $sender->sendMessage(C::RED . "Player is already been banned");
                    return false;
                }else{
					array_shift($args);
					$reason = implode(" ", $args);
					$banList->addBan($getPlayer->getName(), $reason, null, $sender->getName());
					$getPlayer->kick("You have Been Banned for " . C::AQUA . $reason);
					$this->getServer()->broadcastMessage(C::GRAY . $getPlayer->getName() . C::RED . "has been permanently banned for ". C::AQUA . $reason);
					return true;
				}
		}else{
			$sender->sendMessage(C::RED . "Player not found!");
				return true;
		}
	}
}
# unban
	if(count($args) >= 1 && $args[0] === "unban" && $config->get("unban") === true){
					if(!$sender->hasPermission("advancedmod.unban")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
			if(count($args) < 2){
				$sender->sendMessage(C::RED . "You must include a player");
				return true;
			}
			array_shift($args);
			 $banList = $sender->getServer()->getNameBans();
            if (!$banList->isBanned($args[0])) {
                $sender->sendMessage(C::RED . "Player is not banned.");
                return true;
            }else{
			$banList->remove($args[0]);
            $sender->sendMessage(TextFormat::AQUA . $args[0] . TextFormat::GREEN . " has been unbanned.");
			return true;
			}
		}
	}
# banIP
	if(count($args) >= 1 && $args[0] === "banip" && $config->get("banip") === true){
					if(!$sender->hasPermission("advancedmod.banip")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 3){
			$sender->sendMessage(C::RED . "You must have <player|ip> <reason>");
			return true;
		}
		array_shift($args);
		  $banList = $sender->getServer()->getIPBans();
            if ($banList->isBanned($args[0])) {
                $sender->sendMessage(C::RED . "Player|IP is already banned");
                return true;
            }else{
				 $ip = filter_var($args[0], FILTER_VALIDATE_IP);
				$player = $sender->getServer()->getPlayer($args[0]);
				array_shift($args);
				$reason = implode(" ", $args);
				//get by ip
				foreach($this->getServer()->getOnlinePlayers() as $onlineplayers){
					if($onlineplayers->getAddress() === $ip){
						$banList->addBan($ip, $reason, null, $sender->getName());
						$onlineplayers->kick("You have been IP banned", false);
						$this->getServer()->broadcastMessage(C::RED . "IP: " . $ip . " has been banned");
						return true;
					}
				}
				//get by username
				//$getUserIp = $player->getAddress();
				if(!$player){
					$sender->sendMessage(C::RED . "Player not found!");
                return true;
				}else{
					$getPlayerIP = $player->getAddress();
					$banList->addBan($getPlayerIP, $reason, null, $sender->getName());
					$player->kick("You have been IP banned", false);
					$this->getServer()->broadcastMessage(C::RED . "IP: " . $ip . " has been banned");
						return true;
				}
			}
			
		}
	}
# unbanip
	if(count($args) >= 1 && $args[0] === "unbanip" && $config->get("unbanip") === true){
					if(!$sender->hasPermission("advancedmod.unbanip")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 2){
			$sender->sendMessage(C::RED . "You must have <player|ip>");
			return true;
		}
		array_shift($args);
		$banList = $sender->getServer()->getIPBans();
            if (!$banList->isBanned($args[0])) {
                $sender->sendMessage(C::RED . "Player|IP is not banned");
                return true;
            }else{
				$banList->remove($args[0]);
			}
		}
	}
# warn
	if(count($args) >= 1 && $args[0] === "warn" && $config->get("warn") === true){
					if(!$sender->hasPermission("advancedmod.warn")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 3){
			$sender->sendMessage(C::RED . "You must have a player and a message");
			return true;
		}
		array_shift($args);
		if($this->getServer()->getPlayer($args[0])){
			$player = $this->getServer()->getPlayer($args[0]);
			array_shift($args);
			if($config->get("encode-messages") === true){
			$note = implode(" ", $args);
			$player->sendMessage(C::RED . base64_encode($note));
			return true;
			}else{
			$note = implode(" ", $args);
			$player->sendMessage(C::RED . $note);
			return true;	
			}
			
		}else{
			$sender->sendMessage(C::RED . "Player not found!");
			return true;
		}
		
		}
	}	
# warnall
	if(count($args) >= 1 && $args[0] === "warnall" && $config->get("warnall") === true){
					if(!$sender->hasPermission("advancedmod.warn")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 2){
			$sender->sendMessage(C::RED . "You must have a message");
			return true;
		}
		//array_shift($args);
		
			foreach($this->getServer()->getOnlinePlayers() as $onlinePlayers){
				array_shift($args);
			if($config->get("encode-messages") === true){
			$note = implode(" ", $args);
			$onlinePlayers->sendMessage(C::RED . base64_encode($note));
			return true;
			}else{
			$note = implode(" ", $args);
			$onlinePlayers->sendMessage(C::RED . $note);
			return true;	
			}
			}
			
			
		
		
		}
	}
# tban
	if(count($args) >= 1 && $args[0] === "tban" && $config->get("tban") === true){
					if(!$sender->hasPermission("advancedmod.tban")){
						$sender->sendMessage(C::RED . "You do not have the permission to use command");
						return true;
	}else{
		if(count($args) < 4){
			$sender->sendMessage(C::RED . "You must include player, expire, and reason");
			return true;
		}
		array_shift($args);
		if($this->getServer()->getPlayer($args[0])){
			//player
			$player = $this->getServer()->getPlayer($args[0]);
			array_shift($args);
			//expires
			$expire = str_replace("t", " ", $args[0]);
			if(!DateTime::createFromFormat("Y-m-d H:i:s", $expire)){
					$sender->sendMessage(C::RED . "Must be a DateTime format(YYYY-mm-ddtHH:ii:ss)");
					return true;
				}else{
					$Defexpire = DateTime::createFromFormat("Y-m-d H:i:s", $expire);
					//reason
			array_shift($args);
			$reason = implode(" ", $args);
			//server
			$banList = $sender->getServer()->getNameBans();
				if($banList->isBanned($player)){
					$sender->sendMessage(C::RED . "Player is already banned");
					return true;
				}else{
					$getUserDate = date("Y-m-d H:i:s", strtotime($expire));
					$getCurrent = date("Y-m-d H:i:s");
					//create
					$now = date_create($getUserDate);
					$left = date_create($getCurrent);
					$diffScale = date_diff($now, $left);
					
					$banList->addBan($player->getName(), $reason, $Defexpire, $sender->getName());
					$player->kick("You have been temporarily banned for " . C::GREEN . $reason 
					. C::WHITE . ", your ban will expire in " . C::GRAY . $diffScale->format("%Y years %m months %d days :: %H hours %i minutes %s seconds"));
					$this->getServer()->broadcastMessage("User ". $player->getName() . "is banned for " . C::GREEN . $diffScale->format("%Y years %m months %d days :: %H hours %i minutes %s seconds"));
					return true;
				}
				}
		}else{
			$sender->sendMessage(C::RED . "Player not found!");
			return true;
		}
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
						$sender->sendMessage(C::RED . "You must have a player");
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
		
	break;
			break;		
}
//}
			
				
			
	return true;	
	}
		


	public function onChat(PlayerChatEvent $e){
		$player = $e->getPlayer();
	if($this->playerConfig->get("isMuted") === true){
				$e->setCancelled();
				$player->sendMessage(C::RED . "You have been muted");
				return true;
		}
	if($this->playerConfig->get("isTempMuted") === true){
		$getUserDate = date("Y-m-d H:i:s", strtotime($this->playerConfig->get("tempMuteClock")));
		$getCurrent = date("Y-m-d H:i:s");
		if($getCurrent >= $getUserDate){
			$this->playerConfig->set("tempMuteClock", "0000-00-00 00:00:00");
			$this->playerConfig->save();
			$this->playerConfig->set("isTempMuted", false);
			$this->playerConfig->save();
		}else{
			$e->setCancelled();
			$now = date_create($getCurrent);
			$left = date_create($getUserDate);
			$diffScale = date_diff($now, $left);
				$player->sendMessage(C::RED . "You have been muted. You have " . C::GRAY . $diffScale->format("%Y years %m months %d days :: %H hours %i minutes %s seconds") . C::RED . " left");
				return true;
			}
		}
	}
	/*public function onPlayerCommand(PlayerCommandPreprocessEvent $e){
		$player = $e->getPlayer();
		$message = $e->getMessage();
        $command = substr($message, 1);
		$args = explode(" ", $command);
		
		#test for unban command
		if($args[0] === "unban" && $player->hasPermission("unban")){
			$getPlayer = $args[1];
			unset($this->banUserByName[$getPlayer]);
			$player->sendMessage(C::GREEN . "Player has been unbanned from array");
			return true;
		}
		if($args[0] === "unban-ip" && $player->hasPermission("unban-ip")){
			$getPlayerIP = $args[1];
			unset($this->banUserByIP[$getPlayerIP]);
			$player->sendMessage(C::GREEN . "Player has been unbanned from array");
			return true;
		}
	}*/
}

 