<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

//

class wlbx4 extends eqLogic {
    /*     * *************************Attributs****************************** */
	

    /*     * ***********************Methode static*************************** */

	//------------------------------------
	//INTERROGATION DE LA LIVEBOX VIA CURL
	//------------------------------------
	
	/*
	NOTE : Il semble que pour pouvoir être exécuter une action sur la LIVEBOX,
	il convienne au préalable d'obtenir son contextID (?)
	Les requêtes sur la LIVEBOX se font alors en deux temps :
	- Une première requête pour obtenir le contextID
	- Une deuxième requête pour exécuter la commande
	*/
	
	function LB_Request($IP,$Params,$Headers,$Jar) {
		
		log::add('wlbx4','debug','LB_Request');
		
		$cookie = "/tmp/wlbx4.cookie";
		
		//Initialisation CURL
		$ch = curl_init();
		//Paramètres
		curl_setopt($ch, CURLOPT_URL, $IP."/ws");
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($Jar) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		}
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $Params);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
		//Exécution
		$result = curl_exec($ch);
		//Traitement erreurs
		if (curl_errno($ch)) {
			$result = 'Error:' . curl_error($ch);
			log::add('wlbx4','debug','Error: '.$result);
		}		
		//Fermeture CURL
		curl_close ($ch);
		
		//RETOUR
		return $result;
		 
	}
	
	//------------
	//ETAT DU WIFI
	//------------
	
	function LB_WifiState($IP,$LB_USER,$LB_PWD) {
	
		log::add('wlbx4','debug','LB_WifiState');
	
		//I - Recherche ContextID
	
		//Préparation requête
		$paramInternet = "{\"service\":\"sah.Device.Information\",\"method\":\"createContext\",\"parameters\":{\"applicationName\":\"so_sdkut\",\"username\":\"".$LB_USER."\",\"password\":\"".$LB_PWD."\"}}";	

		$headers = array();
		$headers[] = 'Content-Type: application/x-sah-ws-4-call+json';
		$headers[] = 'Authorization: X-Sah-Login';

		//Interrogation de la LIVEBOX
		$result=$this->LB_Request($IP,$paramInternet,$headers,true);

		//Formatage et lecture du résultat
		$json_GetINFOs = json_decode($result,true);
		$contextID = $json_GetINFOs['data']['contextID'];

		//II - Recherche état du WIFI
		
		//Préparation requête
		$paramInternet = "{\"service\":\"NeMo.Intf.lan\",\"method\":\"getMIBs\",\"parameters\":{\"mibs\":\"wlanvap || wlanradio\"}}";
		
		$headers = array();
		$headers[] = 'Content-Type: application/x-sah-ws-4-call+json';
		$headers[] = 'X-Context: '.$contextID;

		//Interrogation de la LIVEBOX
		$result=$this->LB_Request($IP,$paramInternet,$headers,false);

		//Formatage et lecture du résultat
		$json_getMIBs = json_decode($result,true);
		$GetWifiState = $json_getMIBs ["status"]["wlanradio"]["wifi0_bcm"]["RadioStatus"];
		
		//RETOUR
		if ($GetWifiState=="Up"){return 1;} else {return 0;};
		
	}
	
	//------------------------
	//ALLUMER/ETEINDRE LE WIFI
	//------------------------
	
	function LB_WifiOnOff($IP,$LB_USER,$LB_PWD,$OnOff) {
	
		log::add('wlbx4','debug','LB_WifiOnOff');
	
		//Lecture Paramètres Allumer ou Eteindre
		if ($OnOff){$SetWifiOO="True";} else {$SetWifiOO="False";};
		
		//I - Recherche ContextID
		
		//Préparation requête
		$paramInternet = "{\"service\":\"sah.Device.Information\",\"method\":\"createContext\",\"parameters\":{\"applicationName\":\"so_sdkut\",\"username\":\"".$LB_USER."\",\"password\":\"".$LB_PWD."\"}}";

		$headers = array();
		$headers[] = 'Content-Type: application/x-sah-ws-4-call+json';
		$headers[] = 'Authorization: X-Sah-Login';
		
		//Interrogation de la LIVEBOX
		$result=$this->LB_Request($IP,$paramInternet,$headers,true);
		
		//Formatage et lecture du résultat
		$json_GetINFOs = json_decode($result,true);
		$contextID = $json_GetINFOs['data']['contextID'];
 
		//II - Exécution Allumer ou éteindre
 
		//Préparation requête
		$paramInternet = "{\"service\":\"NMC.Wifi\",\"method\":\"set\",\"parameters\":{\"Enable\":\"".$SetWifiOO."\", \"Status\":\"".$SetWifiOO."\"}}";

		$headers = array();
		$headers[] = 'Content-Type: application/x-sah-ws-4-call+json';
		$headers[] = 'X-Context: '.$contextID;

		//Interrogation de la LIVEBOX - 
		$result=$this->LB_Request($IP,$paramInternet,$headers,false);
		
		//RETOUR
		return $result;
	
	}
	
	//----------------------------------------
	//CRON (EXECUTION PERIODIQUE DE COMMANDES)
	//----------------------------------------
		    
    //Fonction exécutée automatiquement toutes les minutes par Jeedom
      //public static function cron($_eqLogic_id = null) {
		  
	public static function cron() {

		// La fonction n’a pas d’argument donc on recherche tous les équipements du plugin
		if ($_eqLogic_id == null) { 
			$eqLogics = self::byType('wlbx4', true);
		// La fonction a l’argument id(unique) d’un équipement(eqLogic)
		} else {
			$eqLogics = array(self::byId($_eqLogic_id));
		}		  
	
		foreach ($eqLogics as $wlbx4) {
			if ($wlbx4->getIsEnable() == 1) {//vérifie que l'équipement est acitf
				$cmd = $wlbx4->getCmd(null, 'wrefresh');//retourne la commande "refresh si elle existe
				if (!is_object($cmd)) {//Si la commande n'existe pas
				  continue; //continue la boucle
				}
				$cmd->execCmd(); // la commande existe on la lance
			}
		}
		  	  
	}	
	 	  
	  /*
     */

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

	
    public function preInsert() {
	
    }

    public function postInsert() {
    
    }

    public function preSave() {
	
    }

	//---------------------------------------------
	//POSTSAVE : CREATION AUTOMATIQUE DES COMMANDES
	//---------------------------------------------
	
    public function postSave() {
	
		log::add('wlbx4','debug','postSave');
			 
		//ALLUMER WIFI (Action)
		$wcmd_on = $this->getCmd(null, 'won');
		if (!is_object($wcmd_on)) {
			$wcmd_on = new wlbx4Cmd();
			$wcmd_on->setName('On');
		}
		$wcmd_on->setLogicalId('won');
		$wcmd_on->setEqLogic_id($this->getId());
		$wcmd_on->setType('action');
		$wcmd_on->setSubType('other');
		$wcmd_on->setTemplate('dashboard', 'alert');
		$wcmd_on->save();
	
		//ETEINDRE WIFI (Action)
		$wcmd_off = $this->getCmd(null, 'woff');
		if (!is_object($wcmd_off)) {
			$wcmd_off = new wlbx4Cmd();
			$wcmd_off->setName('Off');
		}
		$wcmd_off->setLogicalId('woff');
		$wcmd_off->setEqLogic_id($this->getId());
		$wcmd_off->setType('action');
		$wcmd_off->setSubType('other');
		$wcmd_off->save();
	
		//INVERSER WIFI (Action)
		$wcmd_switch = $this->getCmd(null, 'wswitch');
		if (!is_object($wcmd_switch)) {
			$wcmd_switch = new wlbx4Cmd();
			$wcmd_switch->setName('Switch');
		}
		$wcmd_switch->setLogicalId('wswitch');
		$wcmd_switch->setEqLogic_id($this->getId());
		$wcmd_switch->setType('action');
		$wcmd_switch->setSubType('other');
		$wcmd_switch->save();
	
		//RAFRAICHIR WIFI (Action)
		$wcmd_refresh = $this->getCmd(null, 'wrefresh');
		if (!is_object($wcmd_refresh)) {
			$wcmd_refresh = new wlbx4Cmd();
			$wcmd_refresh->setName('Refresh');
		}
		$wcmd_refresh->setLogicalId('wrefresh');
		$wcmd_refresh->setEqLogic_id($this->getId());
		$wcmd_refresh->setType('action');
		$wcmd_refresh->setSubType('other');
		$wcmd_refresh->save();
	
		//ETAT WIFI (Information)
		$wcmd_state = $this->getCmd(null, 'wstate');
		if (!is_object($wcmd_state)) {
			$wcmd_state = new wlbx4Cmd();
			$wcmd_state->setName('State');
		}
		$wcmd_state->setLogicalId('wstate');
		$wcmd_state->setEqLogic_id($this->getId());
		$wcmd_state->setType('info');
		$wcmd_state->setSubType('binary');
		$wcmd_state->save();
	 
    }

    public function preUpdate() {
     
    }

    public function postUpdate() {
	
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Permet de modifier l'affichage du widget
	 */

    public function toHtml($_version = 'dashboard') {

		log::add('wlbx4','debug','toHtml');
		
		//LECTURE OPTION - AFFICHAGE

		$getWWIDGET = $this->getConfiguration('wwidget');

		
		switch ($getWWIDGET) {

			//WIDGET SPECIFIQUE (PAR DEFAUT)
			case 'wwdefault':
		  		  
			//Chargement de la page HTML (../core/template/dashboard/*.html)	
			$replace = $this->preToHtml($_version);
			
			if (!is_array($replace)) {return $replace;}
			
			$this->emptyCacheWidget(); //vide le cache
			
			$version = jeedom::versionAlias($_version);
			if ($this->getDisplay('hideOn' . $version) == 1) {return '';}
			
			//------------ Ajouter code ici ------------
	 
			//Remplacement dans la page HTML des #balises# par les images
			$replace['#won_img#'] = 'plugins/wlbx4/img/won.png';	
			$replace['#woff_img#'] = 'plugins/wlbx4/img/woff.png';	
			$replace['#wswitch_img#'] = 'plugins/wlbx4/img/wswitch.png';
			$replace['#wrefresh_img#'] = 'plugins/wlbx4/img/wrefresh.png';
			$replace['#livebox_img#'] = 'plugins/wlbx4/img/Livebox4.png';
			
			//Remplacement dans la page HTML de la #balise# par l'adresse IP
			$getIP=$this->getConfiguration('ip');
			$replace['#LiveboxIP#'] = $getIP;
			$replace['#LiveboxHTTP#'] = 'http://'.$getIP.'/';
	
			//Conseillé pour toutes les commandes INFOS :
			foreach ($this->getCmd('info') as $cmd) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
				$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            }
	
			//Conseillé pour toutes les commandes ACTIONS :
			foreach ($this->getCmd('action') as $cmd) {
                $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
                $replace['#' . $cmd->getLogicalId() . '_id_display#'] = (is_object($cmd) && $cmd->getIsVisible()) ? '#' . $cmd->getId() . "_id_display#" : 'none';
			}
	 
			//------------ N'ajouter plus de code apres ici------------
			$html = template_replace($replace, getTemplate('core', $_version, 'wlbx4', 'wlbx4'));
			return $html;

			break;
	
			//WIDGET PERSONALISE PAR L'UTILISATEUR
			case 'wwpersonal':
	 
			return parent::toHtml($_version);

			break;
	 
		}
		
	}
	 
    /*     * **********************Getteur Setteur*************************** */
}

class wlbx4Cmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */
	
	//----------------------------------
	//FONCTION D'EXECUTION DES COMMANDES
	//----------------------------------
	
	public function execute($_options = array()) {

		log::add('wlbx4','debug','execute');
		
		//Lecture des paramètres (Complétés dans la gestion du Plugin - Voir Desktop)
		//(Ces paramètres seront passés aux fonctions d'interrogation de la LIVEBOX)
		$eqLogic = $this->getEqLogic();
		$getIP = $eqLogic->getConfiguration('ip');
		$getUSER = $eqLogic->getConfiguration('username');
		$getPWD = $eqLogic->getConfiguration('password');
		$getWTYPE = $this->getLogicalId();//Détermine quelle commande appelle la fonction
		
		//Temps de pause (Il semble préférable d'effectuer une pause après une requête pour Allumer ou
		//Eteindre le wifi, car si l'on effectue en suivant une requête pour connaitre l'état du wifi,
		//il semble que la LIVEBOX retourne l'état du wifi avant d'avoir pu exécuter la fonction ALLUMER
		//ou Eteindre)
		$SleepTime=2;
		
		log::add('wlbx4','debug',$getIP.' '.$getUSER.' '.$getPWD.' '.$getWTYPE);

		//COMMANDE D'APPEL
		switch ($getWTYPE) {

			//ALLUMER
			case 'won':
				$eqLogic->LB_WifiOnOff($getIP,$getUSER,$getPWD,True);
				sleep($SleepTime);
				$GetWifiState = $eqLogic->LB_WifiState($getIP,$getUSER,$getPWD);
				$eqLogic->checkAndUpdateCmd('wstate', $GetWifiState);
				return $GetWifiState;
			break;

			//ETEINDRE
			case 'woff':
				$eqLogic->LB_WifiOnOff($getIP,$getUSER,$getPWD,False);
				sleep($SleepTime);
				$GetWifiState = $eqLogic->LB_WifiState($getIP,$getUSER,$getPWD);
				$eqLogic->checkAndUpdateCmd('wstate', $GetWifiState);
				return $GetWifiState;
			break;

			//INVERSER
			case 'wswitch';
				$GetWifiState = $eqLogic->LB_WifiState($getIP,$getUSER,$getPWD);
				if ($GetWifiState == 1) {$InverseWifi = False;}else {$InverseWifi = True;}
				$eqLogic->LB_WifiOnOff($getIP,$getUSER,$getPWD,$InverseWifi);
				sleep($SleepTime);
				$GetWifiState = $eqLogic->LB_WifiState($getIP,$getUSER,$getPWD);				
				$GetWcau=$eqLogic->checkAndUpdateCmd('wstate', $GetWifiState);
				return $GetWifiState;
			break;

			//RAFRAICHIR
			case 'wrefresh':
				$GetWifiState = $eqLogic->LB_WifiState($getIP,$getUSER,$getPWD);
				$eqLogic->checkAndUpdateCmd('wstate', $GetWifiState);
				return $GetWifiState;
			break;

		}

	}
	
    /*     * **********************Getteur Setteur*************************** */
}

?>
