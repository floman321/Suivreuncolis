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
    
    class Suivreuncolis extends eqLogic {
        /*     * *************************Attributs****************************** */
        
		
         public static function CodeToHTML($code) {
            
            switch ($code) {
                case 0:
                    return "<b>Introuvable</b>";
                    break;
                case 5:
                    return "<b>En attente de récupération par le transporteur</b>";
                    break;
                case 10:
                    return "<b>En Transit</b><br>Votre colis a été remis au transporteur";
                    break;
                case 20:
                    return "<b>Expiré</b>";
                    break;
                case 30:
                    return "<b>Prêt pour être livré </b><br>Votre colis est arrivé dans un point de distribution locale.<br>Votre colis est en cours de livraison.;<br>";
                    break;
                case 35:
                    return "<b>Non Livré</b><br>Votre transporteur a tenté de livrer votre colis mais il n'a pu être livré. Contactez le transporteur pour de plus amples informations.";
                    break;
                case 40:
                    return "<b>Livré</b>";
                    break;
                case 50:
                    return "<b>Alerte !</b><br>Il se peut que votre colis ait subi des conditions de transit inhabituelles (Douane, Refusé)";
                    break;
                    
            }
            
        }
 
        
        function multiexplode ($delimiters,$string) {
            
            $ready = str_replace($delimiters, $delimiters[0], $string);
            $launch = explode($delimiters[0], $ready);
            return  $launch;
        }
        
        
      	function LaPosteRecupere ($NumEnvoi) {
          
            $apikey = config::byKey('api_laposte', 'suivreuncolis','');
            
            if ($apikey == ''){
                log::add('Suivreuncolis', 'error', 'Api key La poste manquante'.$apikey );
                return array("","","","","");
            }
          
          
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL,"https://api.laposte.fr/suivi/v2/idships/$NumEnvoi?lang=fr_FR");
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json','X-Forwarded-For: 123.123.123.123','X-Okapi-Key: '.$apikey));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if( ! $server_output = curl_exec($ch)) {
                
                log::add('Suivreuncolis', 'debug', 'httperror laposte' . "https://api.laposte.fr/suivi/v2/idships/$NumEnvoi?lang=fr_FR");
                
                return array("","","","");
            }else{
                
                $data = json_decode($server_output, true);
              	
                if ($data['returnCode'] == 200 ){
                  
					 log::add('Suivreuncolis', 'debug', '    debug loop ');
                  
                      	$monitem = $data['shipment']['event'][0];
                       	$dh = str_replace('T',' ',str_replace('T00:00:00','',$monitem['date']));
                      	$msg = $monitem['label'];
                        $lieu = '';
                        $statusbrut = $monitem['code'];
                        switch ($statusbrut) {
                            case 'Pending':
                                $codetat = 0;
                                break;
                            case 'PC1':
                                $codetat = 5;
                                break;
                            case 'ET1':
                                $codetat = 10;
                                break;
                            case 'Courrier en distribution':
                                $codetat = 30;
                                break;
                            case 'AttemptFail':
                                $codetat = 35;
                                break;
                            case 'DI1':
                                $codetat = 40;
                                break;
                            case 'Exception':
                                $codetat = 50;
                                break;
                            case 'Expired':
                                $codetat = 20;
                                break;
                        }
                     
                    return array($msg,$lieu,$dh,$codetat,$msg);
                    
                }else{
                    log::add('Suivreuncolis', 'debug', 'httpok laposte code error'. $data['returnCode'] );
                  	$msg = $data['returnMessage'];
                  	$codetat = 50;
                  	$lieu = '';
                  	$dh = '';
                    return array($msg,$lieu,$dh,$codetat,$msg);
                }
                
            }
            
            
            return array('','','');
        }
      
        function AfterShipRecupere ($NumEnvoi,$Transporteur,$postalcode = '') {
            
            $apikey = config::byKey('api_aftership', 'suivreuncolis','');
            
            if ($apikey == ''){
                log::add('Suivreuncolis', 'error', 'Api key Aftership manquante'.$apikey );
                return array("","","","","");
            }
            
            $ch = curl_init();
            
			$Transporteur = strtolower(str_replace(' ','',$Transporteur));
			
            
            
            if ($postalcode != ''){
				$postalcode = "?tracking_postal_code=".$postalcode."&lang=en";
			}else{
 	             $postalcode = "?lang=en";
            }
			
			log::add('Suivreuncolis', 'debug', '  httpdebug ' . "https://api.aftership.com/v4/trackings/$Transporteur/$NumEnvoi$postalcode");
			
            curl_setopt($ch, CURLOPT_URL,"https://api.aftership.com/v4/trackings/$Transporteur/$NumEnvoi$postalcode");
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('aftership-api-key: '.$apikey,'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if( ! $server_output = curl_exec($ch)) {
                
                log::add('Suivreuncolis', 'debug', 'httperror aftership' . "https://api.aftership.com/v4/trackings/$Transporteur/$NumEnvoi");
                
                return array("","","","");
            }else{
                
                $data = json_decode($server_output, true);
              
                if ($data['meta']['code'] == 200 ){
                  
                    $nbtotal = count($data['data']['tracking']['checkpoints']) -1;
                  
                    $monitem = $data['data']['tracking']['checkpoints'][$nbtotal];
                                    
   	                log::add('Suivreuncolis', 'debug', '    debug nb occurence '.$nbtotal.' '.$monitem['checkpoint_time']);

                    $dh = str_replace('T',' ',str_replace('T00:00:00','',$monitem['checkpoint_time']));
                    $msg = $monitem['message'];
                    $lieu = $monitem['location'];
                    $statusbrut = $monitem['tag'];
               
                    switch ($statusbrut) {
                        case 'Pending':
                            $codetat = 0;
                            break;
                        case 'InfoReceived':
                            $codetat = 5;
                            break;
                        case 'InTransit':
                            $codetat = 10;
                            break;
                        case 'OutForDelivery':
                            $codetat = 30;
                            break;
                        case 'AttemptFail':
                            $codetat = 35;
                            break;
                        case 'Delivered':
                            $codetat = 40;
                            break;
                        case 'Exception':
                            $codetat = 50;
                            break;
                        case 'Expired':
                            $codetat = 20;
                            break;
                    }
                 
                    return array($msg,$lieu,$dh,$codetat,$msg);
                    
                }else{
                    log::add('Suivreuncolis', 'debug', 'httpok code error'. $data['meta']['code'] . ' key = '.$apikey );
                    return array("","","","","");
                }
                
            }
            
            
            return array('','','');
            
        }
        
        
       
		
		function AliexpressShippingRecupere ($NumEnvoi) {
			
			//log::add('Suivreuncolis', 'debug', 'ALIEXPRESS '.$NumEnvoi);
            
            $source = @file_get_contents("http://global.cainiao.com/detail.htm?mailNoList=$NumEnvoi&spm=a3708.7860688.0.d01");
			
			$source = htmlspecialchars_decode ($source);
			
            $exploded = Suivreuncolis::multiexplode(array('<textarea style="display: none;" id="waybill_list_val_box">'),$source);
            list($json) = explode('</textarea>',$exploded[1]);
			
			$data = json_decode($json, true);
			
			$etat_ = $data['data'][0]['latestTrackingInfo']['status'];
			$date_ = $data['data'][0]['latestTrackingInfo']['time'];
			$description = $data['data'][0]['latestTrackingInfo']['desc'];
            
			log::add('Suivreuncolis', 'debug', 'ALIEXPRESS '.$NumEnvoi.' '.$etat_);

			
			if ( $etat_ == 'SIGNIN_EXC') {
                return array("Echec de livraison",'',$date_,35,$description);
            }
			
			if ( $etat_ == 'SHIPPING') {
                return array("Preparation de la commande ",'',$date_,5,$description);
            }
			
			if ( $etat_ == 'ORDER_NOT_EXISTS' || $etat_ == 'NOT_LAZADA_ORDER') {
                return array("Commande Introuvable",'',$date_,0,$description);
            }
			
            if ( $etat_ == 'SIGNIN' || $etat_ == 'PICKEDUP') {
                return array("Votre colis a été livré",'',$date_,40,$description);
            }
            if ( $etat_ == 'ARRIVED_AT_DEST_COUNTRY') {
                return array("Arrivée dans le pays de destination",'',$date_,30,$description);
            }
            if ( $etat_ == 'DEPART_FROM_ORIGINAL_COUNTRY' || $etat_ == 'SHIPPING' ) {
                return array("Votre colis est en cours de livraison ",'',$date_,10,$description);
            }            
            
            return array($etat_,'',$date,10,$description);
            
        }
		
		public static function APIServices($numsuivi,$NumOperator) {
            
			
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL,"https://www.17track.net/restapi/handlertrack.ashx");
            curl_setopt($ch, CURLOPT_POST, 1);
            
            if ($NumOperator == ''){
                curl_setopt($ch, CURLOPT_POSTFIELDS,'{"data":[{"num":"'.$numsuivi.'"}]}');
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS,'{"data":[{"num":"'.$numsuivi.'","fc":"'.$NumOperator.'"}]}');
            }
                        
            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if( ! $server_output = curl_exec($ch)) {
                
                log::add('Suivreuncolis', 'debug', 'httperror');
                
                return array("","","","");
            }
            
            log::add('Suivreuncolis', 'debug', 'httpok Numero colis '.$numsuivi.' '.$server_output);
                        
            curl_close ($ch);
            
            $data = json_decode($server_output, true);
          
            
            if ($data['dat'][0]['delay'] == 0  || $data['ret'] != '1'){
                
                log::add('Suivreuncolis', 'debug', ' Ok Numero colis '.$numsuivi.' code '.$data['ret']);
                
                $codetraduit = Suivreuncolis::CodeToHTML($data['dat'][0]['track']['e']);
              
				//var_dump($data['dat'][0]['track']['z0']);
                
                return array($codetraduit , $data['dat'][0]['track']['z0']['d'] . ', '.$data['dat'][0]['track']['z0']['c'] , $data['dat'][0]['track']['z0'][a] , $data['dat'][0]['track']['e'] , $data['dat'][0]['track']['z0']['z'] );
                
            }else{
                
                log::add('Suivreuncolis', 'debug', 'httpnok saturation not ok code erreur '.$data['ret'].'- delay '.$data['dat'][0]['delay']);
                return array("-99","","","","");
                
            }
            
        }
        
        function SkyRecupere($NumEnvoi) {
            
            $server_output = @file_get_contents('http://sky56.cn/track/track/result?tracking_number='.$NumEnvoi);
            $data = json_decode($server_output, true);
            
            $lasttrack = $data['List']['Track']['z0']['Detail'][0];
            
            $date_ = $lasttrack["ondate"];
            $etat_ = $lasttrack["status"];
            $msg_  = $lasttrack["message"];
            
            
            if ( $etat_ == 'votre colis a été livré en bo?te aux lettres') {
                return array($etat_,'',$date_,40,$msg_);
            }
            if ( $etat_ == 'votre colis est en cours de livraison') {
                return array($etat_,'',$date_,30,$msg_);
            }
            if ( $etat_ == 'votre colis est arrivé sur notre agence régionale') {
                return array($etat_,'',$date_,10,$msg_);
            }
            if ( $etat_ == 'votre colis est pris en charge par colis privé. il va être expédié sur notre agence régionale') {
                return array($etat_,'',$date_,10,$msg_);
            }
            if ( $etat_ == 'votre colis a été expédié par votre webmarchand, mais n a pas encore été pris en charge par colis privé') {
                return array($etat_,'',$date_,10,$msg_);
            }
            
            
            return array($etat_,'',$date_,10,$msg_);
            
        }
        
        
        
        public static function MAJColis() {
            
            foreach (self::byType('Suivreuncolis') as $suivreUnColis) {
                
                if ($suivreUnColis->getIsEnable() == 1) {
                    
                    $nom = $suivreUnColis->getName();
                    $transnom = $suivreUnColis->getConfiguration('transporteur','');
                    $numcolis = $suivreUnColis->getConfiguration('numsuivi',0);
                    $lecommentaire = $suivreUnColis->getConfiguration('commentaire','');
                    $transporteurAftership = $suivreUnColis->getConfiguration('transaftership','');
					$cp = $suivreUnColis->getConfiguration('cp_aftership','');
					$ac = $suivreUnColis->getConfiguration('autocreate',0);
					$ad = $suivreUnColis->getConfiguration('autodekete',0);

                    $etat = '';
                  
                    if ($numcolis == '') continue;
                    
                    
                    log::add('Suivreuncolis', 'debug', 'refreshdata MAJColis essai '.$transnom.' Nom Colis : '.$nom);
                    log::add('Suivreuncolis', 'debug', '  AutoCreate = '.$ac.' AutoDelete : '.$ad);
                    
                      switch ($transnom) {
                          case "sky56":
                              list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::SkyRecupere($numcolis);
                              break;
                        case "aliexpress":
                              list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::AliexpressShippingRecupere($numcolis);
                              break;
                          case "aftership":
                              list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::AfterShipRecupere($numcolis,$transporteurAftership,$cp);
                              break;
                          case "laposte":
                              list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::LaPosteRecupere($numcolis);
                              break;
						  case "17tracks":
							log::add('Suivreuncolis', 'debug', 'httpok 17tracks');
							  list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::APIServices($numcolis,'');
							break;
							
                          default:
							
                      }
                      
                    }
					
					if ($etat == '-99') return;
					
              		$notif = config::byKey('notificationpar', 'suivreuncolis','');
              
                    foreach ($suivreUnColis->getCmd() as $cmd) {
                        $v = $cmd->getName();
                        
                        if ($v == 'codeetat'){
                            if ($cmd->execCmd() != $codeetat){
								
							   if ($notif == "jeedom_msg"){
                                   message::add('Suivreuncolis','Message -> Nouvelle etat du colis N°'.$numcolis.' '.$lecommentaire.' '.$msgtransporteur.' | '.$cmd->getValue().' => '.$etat );
							   }
                              
                               if ($notif == "cmd"){
                                   $cmd_notif = config::byKey('cmd_notif', 'suivreuncolis','');
                                   $option = array('title' => 'Alerte Nouvelle etat colis N°'.$numcolis, 'message' => 'Nouvelle etat du colis N°'.$numcolis.' '.$lecommentaire.' '.$msgtransporteur.' - '.$etat);
                                   cmd::byId($cmd_notif)->execCmd($option);
							   }
                               							   							   
                               $cmd->setCollectDate('');
                               $cmd->event($etat); 
                            }
                        }
                        
                        if ($v == 'lieu'){
                          
                          if ($cmd->execCmd() != $lieu){                            
                            $cmd->setCollectDate('');
                            $cmd->event($lieu);
                          }
                          
                        }
                        
                        if ($v == 'dateheure'){
                            
                          if ($cmd->execCmd() != $dateheure){
                            $cmd->setCollectDate('');
                            $cmd->event($dateheure);
                          }
                          
                        }
                        
                        if ($v == 'codeetat'){
                            
                          if ($cmd->execCmd() != $codeetat){
                            $cmd->setCollectDate('');
                            $cmd->event($codeetat);
                          }
                          
                        }
                        
                        if ($v == 'msgtransporteur'){
                            
                          if ($cmd->execCmd() != $msgtransporteur){
                            $cmd->setCollectDate('');
                            $cmd->event($msgtransporteur);
                          }
                          
                        }
                      
                        if ($v == 'moncommentaire'){
                            
                          if ($cmd->execCmd() != $lecommentaire){
                            $cmd->setCollectDate('');
                            $cmd->event($lecommentaire);
                          }
                          
                        }
                        
                    }
                    
                    $suivreUnColis->refreshWidget();
					
					//Si le colis est livré depuis 15 jours, alors on le supprime de la liste
					$interval =  floor( (((time() - strtotime($dateheure))/60)/60)/24);
					log::add('Suivreuncolis', 'debug', '    codeetat : '.$codeetat.' dateheure : '.$dateheure.'  -> +'.$interval.'J');
					if ($codeetat == '40' && $interval >= 15) {
						$suivreUnColis->remove();
						log::add('Suivreuncolis', 'debug', '      Colis livré et vieux de plus de 15 jours -> suppression');
					} else {						
						log::add('Suivreuncolis', 'debug', '      Colis non livré ou livré depuis moins de 15 jours -> pas de suppression');
					}
                }
             
        }
      
      
        public static function importAfterShip(){
          
          $apikey = config::byKey('api_aftership', 'suivreuncolis','');
				
				if ($apikey == ''){
					log::add('Suivreuncolis', 'error', 'Api key Aftership manquante'.$apikey );
					return '';
				}
          
            
           //log::add('Suivreuncolis', 'debug', ' importAfterShip ');
          
            $ch = curl_init();
          
            curl_setopt($ch, CURLOPT_URL,"https://api.aftership.com/v4/trackings");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('aftership-api-key: '.$apikey,'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if( ! $server_output = curl_exec($ch)) {
                
                log::add('Suivreuncolis', 'debug', 'httperror' . "https://api.aftership.com/v4/trackings ".curl_error($ch));
                
                return '';
            }else{
              
                $data = json_decode($server_output, true);
              
              	if ($data['meta']['code'] == 200 ){
                  
                  $mescolis = $data['data']['trackings'];
                  
                    foreach ($mescolis as $moncolis){
                      
                      if (eqLogic::byTypeAndSearhConfiguration('Suivreuncolis','"numsuivi":"'.$moncolis['tracking_number'].'"') == null){
						$nom = "SansNom";
                        if ($moncolis['title'] != ''){
							$nom=$moncolis['title'];
						}
                        log::add('Suivreuncolis', 'debug', ' => Importation 1 colis depuis AfterShip : '.$moncolis['tracking_number'].'-'.$nom);
                        if ($moncolis['shipment_delivery_date'] == null) {
	                        log::add('Suivreuncolis', 'debug', '   - Colis non livré import');
							$mynewcolis = null;
							$mynewcolis = new self();
							$mynewcolis->setConfiguration('numsuivi',$moncolis['tracking_number']);
							$mynewcolis->setConfiguration('transporteur','aftership');
							$mynewcolis->setConfiguration('transaftership',$moncolis['slug']);
							$mynewcolis->setConfiguration('cp_aftership',$moncolis['tracking_postal_code']);
							$mynewcolis->setConfiguration('autocreate',0);
							$mynewcolis->setConfiguration('autodelete',0);

							if ($moncolis['title'] != ''){
								$mynewcolis->setName($moncolis['title']);
							}else{
								$mynewcolis->setName($moncolis['tracking_number']);
							}

							$mynewcolis->setIsEnable(1);
							$mynewcolis->setIsVisible(1);
							$mynewcolis->setEqType_name('Suivreuncolis');

							$objetpardefaut = config::byKey('objetpardefaut', 'suivreuncolis','');
							if ( $objetpardefaut == ''){
							}else{
							  $mynewcolis->setObject_id($objetpardefaut);
							}

							$mynewcolis->save();

							$mynewcolis->toHtml('dashboard');
							$mynewcolis->refreshWidget();
						} else {
							log::add('Suivreuncolis', 'debug', '   - Colis livré pas d import : '.$moncolis['shipment_delivery_date']);
						}
                      }
                      
                    }
                }
            }
          
          Suivreuncolis::MAJColis(); 
          
          return 'ok';
        }
        
        
        /*     * ***********************Methode static*************************** */
        
        /*
         * Fonction exécutée automatiquement toutes les minutes par Jeedom
         public static function cron() {
         
         }
         */
        
        //* Fonction exécutée automatiquement toutes les heures par Jeedom
        public static function cronHourly() {
			         
          Suivreuncolis::MAJColis(); 
		  
        }
        
        
        /*
         * Fonction exécutée automatiquement tous les jours par Jeedom
         public static function cronDayly() {
         
         }
         */
        
        
        
        /*     * *********************Méthodes d'instance************************* */
        
        public function preInsert() {
             
          $objetpardefaut = config::byKey('objetpardefaut', 'suivreuncolis','');
            if ( $objetpardefaut == ''){
            }else{
              $this->setObject_id($objetpardefaut);
            }
          
        }
        
        public function postInsert() {
            
            $mynewcolis = new Suivreuncolis();
            
            $mode = null;
            $mode = new SuivreuncolisCmd();
            $mode->setName('etat');
            $mode->setEqLogic_id($this->getId());
            $mode->setSubType('string');
            $mode->setType('info');
            $mode->setIsHistorized(0);
            $mode->setIsVisible(1);
            $mode->save();
            
            
            $lieu = null;
            $lieu = new SuivreuncolisCmd();
            $lieu->setName('lieu');
            $lieu->setEqLogic_id($this->getId());
            $lieu->setSubType('string');
            $lieu->setType('info');
            $lieu->setIsHistorized(0);
            $lieu->setIsVisible(1);
            $lieu->save();
            
            $dateheure = null;
            $dateheure = new SuivreuncolisCmd();
            $dateheure->setName('dateheure');
            $dateheure->setEqLogic_id($this->getId());
            $dateheure->setSubType('string');
            $dateheure->setType('info');
            $dateheure->setIsHistorized(0);
            $dateheure->setIsVisible(1);
            $dateheure->save();
            
            
            $codeetat = null;
            $codeetat = new SuivreuncolisCmd();
            $codeetat->setName('codeetat');
            $codeetat->setEqLogic_id($this->getId());
            $codeetat->setSubType('numeric');
            $codeetat->setConfiguration('maxValue', '50');
            $codeetat->setType('info');
            $codeetat->setIsHistorized(0);
            $codeetat->setIsVisible(1);
            $codeetat->save();
            
            $msgtransporteur = null;
            $msgtransporteur = new SuivreuncolisCmd();
            $msgtransporteur->setName('msgtransporteur');
            $msgtransporteur->setEqLogic_id($this->getId());
            $msgtransporteur->setSubType('string');
            $msgtransporteur->setType('info');
            $msgtransporteur->setIsHistorized(0);
            $msgtransporteur->setIsVisible(1);
            $msgtransporteur->save();
          
            $moncommentaire = null;
            $moncommentaire = new SuivreuncolisCmd();
            $moncommentaire->setName('moncommentaire');
            $moncommentaire->setEqLogic_id($this->getId());
            $moncommentaire->setSubType('string');
            $moncommentaire->setType('info');
            $moncommentaire->setIsHistorized(0);
            $moncommentaire->setIsVisible(1);
            $moncommentaire->save();
			
			
			$refresh = null;
            $refresh = new SuivreuncolisCmd();
            $refresh->setName('Rafraichir');
            $refresh->setEqLogic_id($this->getId());
            $refresh->setSubType('other');
			$refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setIsHistorized(0);
            $refresh->setIsVisible(1);
            $refresh->save();
          
        }
        
        public function preSave() {
			
		
			
			
            
        }
        
        public function postSave() {
			
		
			$transnom = $this->getConfiguration('transporteur','');
            		$autcreation = $this->getConfiguration('autocreate','1');
            
			if ($transnom == "aftership" && $autcreation != '0'){
				log::add('Suivreuncolis', 'debug', 'AutoCreation dans aftership' );
				
				$apikey = config::byKey('api_aftership', 'suivreuncolis','');
				
				if ($apikey == ''){
					log::add('Suivreuncolis', 'error', 'Api key Aftership manquante'.$apikey );
					return;
				}
				
				$nom = $this->getName();
				$numcolis = $this->getConfiguration('numsuivi',0);
				$lecommentaire = $this->getConfiguration('commentaire','');
				$transporteurAftership = $this->getConfiguration('transaftership','');
				$cp = $this->getConfiguration('cp_aftership','');
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.aftership.com/v4/trackings");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,'{"tracking":{ "tracking_number":"'.$numcolis.'","tracking_postal_code":"'.$cp.'","slug":"'.$transporteurAftership.'","title":"'.$nom.'"}}');	
				curl_setopt($ch, CURLOPT_HTTPHEADER,array('aftership-api-key: '.$apikey,'Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							
				if( ! $server_output = curl_exec($ch)) {
					
					log::add('Suivreuncolis', 'debug', 'httperror '.$server_output);
					
				}else{
					log::add('Suivreuncolis', 'debug', 'Enregistre ok'.$server_output );
				}
				
				curl_close ($ch);
			}
			
        }
        
        public function preUpdate() {
            
        }
        
        public function postUpdate() {
            
        }
        
        public function preRemove() {
            
        }
        
        public function postRemove() {
			
			
			$transnom = $this->getConfiguration('transporteur','');
			$autdelete = $this->getConfiguration('autodelete','0');

			if ($transnom == "aftership" && $autdelete == 1){
				
				log::add('Suivreuncolis', 'debug', 'Aftership et auto delete activé -> supression' );
				
				$apikey = config::byKey('api_aftership', 'suivreuncolis','');
				
				if ($apikey == ''){
					log::add('Suivreuncolis', 'error', 'Api key Aftership manquante'.$apikey );
					return;
				}
				
				
				$nom = $this->getName();
				$numcolis = $this->getConfiguration('numsuivi',0);
				$lecommentaire = $this->getConfiguration('commentaire','');
				$transporteurAftership = $this->getConfiguration('transaftership','');
				$cp = $this->getConfiguration('cp_aftership','');
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.aftership.com/v4/trackings/$transporteurAftership/$numcolis");
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_HTTPHEADER,array('aftership-api-key: '.$apikey,'Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							
				if( ! $server_output = curl_exec($ch)) {
					
					log::add('Suivreuncolis', 'debug', 'httperror '.$server_output);
					
				}else{
					log::add('Suivreuncolis', 'debug', 'DELETE ok'.$server_output );
				}
				
				curl_close ($ch);
			}
			
            
        }
        
        
        public function toHtml($_version = 'dashboard') {
            
            if ($this->getIsEnable() != 1) {
                return '';
            }
            if (!$this->hasRight('r')) {
                return '';
            }
          
            $replace = $this->preToHtml($_version);
            if (!is_array($replace)) {
                return $replace;
            }
            
            $version = jeedom::versionAlias($_version);
            if ($this->getDisplay('hideOn' . $version) == 1) {
                return '';
            }
                         
            $html_forecast = '';
            
            $replace = array(
                             '#id#' => $this->getId(),
                             '#collectDate#' => '',
                             '#eqLink#' => $this->getLinkToConfiguration(),
                             );
          
            
            $dateheure = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'dateheure');
            $replace['#dateheure#'] = is_object($dateheure) ? $dateheure->execCmd() : '?';
          	if ($replace['#dateheure#'] != ''){
                $replace['#dateheure#'] = '<p></i> '.$replace['#dateheure#'].'</p>';
            }
            
            $etat = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'etat');
            $replace['#etat#'] = is_object($etat) ? $etat->execCmd() : '';

            
            $lieu = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'lieu');
            $replace['#lieu#'] = is_object($lieu) ? $lieu->execCmd() : '';
            
            if ($replace['#lieu#'] != ''){
                $replace['#lieu#'] = '<p></i> '.$replace['#lieu#'].'</p>';
            }
            
            $codeetat = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'codeetat');
            $code = is_object($codeetat) ? $codeetat->execCmd() : '';
            $replace['#jauge#'] = $code;
          
            $comment = $this->getConfiguration('commentaire','');
            if ($comment == ''){
                $comment = "";
            }else{
                $comment = '<p>'.$comment.'</p>';
            }
            $replace['#commentaire#'] = $comment;
			
          
			$msgtransporteur = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'msgtransporteur');
			$replace['#msgtransporteur#']  = is_object($msgtransporteur) ? $msgtransporteur->execCmd() : '';
			if ($replace['#etat#'] == $replace['#msgtransporteur#']){
				$replace['#msgtransporteur#']  = '';
			}
									
            switch ($code) {
                case '':
                   $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-expired.svg";//plugins/Suivreuncolis/3rparty/introuvable.png";
                   break;
                case '0':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-expired.svg";//"/plugins/Suivreuncolis/3rparty/introuvable.png";
                    break;
                case '5':
               		$replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-info-receive.svg";//"/plugins/Suivreuncolis/3rparty/preparing.png";
                    break;
                case '10':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-in-transit.svg";//"/plugins/Suivreuncolis/3rparty/transit.png";
                    break;
                case '20':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-expired.svg";//"/plugins/Suivreuncolis/3rparty/introuvable.png";
                    break;
                case '30':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-out-for-delivery.svg";//"/plugins/Suivreuncolis/3rparty/outfordelivery.png";
                    break;
                case '35':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-attemptfail.svg";//"/plugins/Suivreuncolis/3rparty/problem.png";
                    break;
                case '40':
                    $replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-delivered.svg";//"/plugins/Suivreuncolis/3rparty/livre.png";
                    break;
                case '50':
               		$replace['#image#'] = "plugins/Suivreuncolis/core/template/dashboard/status-expired.svg";//"/plugins/Suivreuncolis/3rparty/problem.png";
                    break;
           }
            
            
            
            $numsuivi = $this->getConfiguration('numsuivi','');
			
			$postalcode = $this->getConfiguration('cp','');
			if ($postalcode != ''){
				$postalcode = "?tracking_postal_code=".$postalcode;
			}
			
            $msgtransporteur = $this->getConfiguration('transaftership','');
            $replace['#lien#'] = 'https://track.aftership.com/'.$msgtransporteur.'/'.$numsuivi.$postalcode;
            $replace['#name_display#'] = $this->getName();
			
			$refresh = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'Rafraichir');
			if (is_object($refresh)) {
				$replace['#refresh_id#'] = $refresh->getId();
			}
			
			
            /*$parameters = $this->getDisplay('parameters');
            if (is_array($parameters)) {
                foreach ($parameters as $key => $value) {
                    $replace['#' . $key . '#'] = $value;
                }
            }*/
                      
          
            $html = template_replace($replace, getTemplate('core', $version, 'colis', 'Suivreuncolis'));
			cache::set('widgetHtmlColis' . $version . $this->getId(), $html, 0);
			return $html;
        }
        
    }
    
    class SuivreuncolisCmd extends cmd {
           
        public function execute($_options = array()) {
            
			if ($this->getType() == '') {
			return '';
			}
			$eqLogic = $this->getEqlogic();
			$eqLogic->MAJColis();
			
        }
    }
    
    ?>
