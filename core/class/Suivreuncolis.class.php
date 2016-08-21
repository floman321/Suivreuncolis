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
                    return "Introuvable";
                    break;
                case 10:
                    return "En Transit";
                    break;
                case 20:
                    return "Expiré";
                    break;
                case 30:
                    return "Prêt pour être livré <br>Votre colis est arrivé dans un point de distribution locale.<br>Votre colis est en cours de livraison.;<br>";
                    break;
                case 35:
                    return "Non Livré<br>Votre transporteur a tenté de livrer votre colis mais il n'a pu être livré. Contactez le transporteur pour de plus amples informations.";
                    break;
                case 40:
                    return "Livré";
                    break;
                case 50:
                    return "Alerte<br>Il se peut que votre colis ait subi des conditions de transit inhabituelles (Douane, Refusé)";
                    break;
                    
            }
            
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
            
            log::add('Suivreuncolis', 'debug', 'httpok '.$server_output);
            
            curl_close ($ch);
            
            $data = json_decode($server_output, true);
            
            
            if ($data['dat'][0]['track']['delay'] == 0){
                
                log::add('Suivreuncolis', 'debug', 'http delay ok ');
                
                $codetraduit = Suivreuncolis::CodeToHTML($data['dat'][0]['track']['e']);
                
                return array($codetraduit , $data['dat'][0]['track']['z0']['c'] , $data['dat'][0]['track']['z0'][a] , $data['dat'][0]['track']['e'] , $data['dat'][0]['track']['z0']['z'] );
                
            }else{
                
                log::add('Suivreuncolis', 'debug', 'httpok delay not ok');
                return array("","","","","");
                
            }
            
        }
        
        
        
        function multiexplode ($delimiters,$string) {
            
            $ready = str_replace($delimiters, $delimiters[0], $string);
            $launch = explode($delimiters[0], $ready);
            return  $launch;
        }
        
        
        function ColisPriveeRecupere ($NumEnvoi) {
            
            $source = file_get_contents('https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis='.$NumEnvoi);
            $exploded = Suivreuncolis::multiexplode(array('<td class="tdText">'),$source);
            
            list($date_) = explode('</td>',$exploded[1]);
            list($etat_) = explode('</td>',$exploded[2]);
            
            
            if ( $etat_ == 'Votre colis a été livré') {
                return array($etat_,'',$date_,40,'');
            }
            if ( $etat_ == 'Votre colis est en cours de livraison') {
                return array($etat_,'',$date_,30,'');
            }
            if ( $etat_ == 'Votre colis est pris en charge par Colis Privé. Il va être expédié sur notre agence régionale') {
                return array($etat_,'',$date_,10,'');
            }
            if ( $etat_ == 'Votre colis a été expédié par votre webmarchand, mais n a pas encore été pris en charge par Colis Privé') {
                return array($etat_,'',$date_,10,'');
            }
            
            
            return array('','','');
            
        }
        
        
        
        public static function MAJColis() {
            
            
            foreach (self::byType('Suivreuncolis') as $weather) {
                
                if ($weather->getIsEnable() == 1) {
                    
                    log::add('Suivreuncolis', 'info', 'loop '.$weather->getConfiguration('numsuivi',0));
                    
                    $transnom = $weather->getConfiguration('transporteur','');
                    $numcolis = $weather->getConfiguration('numsuivi',0);
                    $etat = '';
                    
                    if ($transnom == 'colisprivee'){
                        list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::ColisPriveeRecupere($numcolis);
                    }else{
                        list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::APIServices($numcolis,$transnom);
                    }
                    
                    if ($etat == '') {
                        continue;
                    }
                    
                    foreach ($weather->getCmd() as $cmd) {
                        $v = $cmd->getName();
                        
                        if ($v == 'etat'){
                            $cmd->setCollectDate('');
                            $cmd->event($etat);
                        }
                        
                        if ($v == 'lieu'){
                            
                            $cmd->setCollectDate('');
                            $cmd->event($lieu);
                        }
                        
                        if ($v == 'dateheure'){
                            
                            $cmd->setCollectDate('');
                            $cmd->event($dateheure);
                        }
                        
                        if ($v == 'codeetat'){
                            
                            $cmd->setCollectDate('');
                            $cmd->event($codeetat);
                        }
                        
                        if ($v == 'msgtransporteur'){
                            
                            $cmd->setCollectDate('');
                            $cmd->event($msgtransporteur);
                        }
                        
                    }
                    
                    $weather->toHtml('dashboard');
                    $weather->refreshWidget();
                }
                
            }
            
        }
        
        
        /*     * ***********************Methode static*************************** */
        
        /*
         * Fonction exécutée automatiquement toutes les minutes par Jeedom
         public static function cron() {
         
         }
         */
        
        
        
        //* Fonction exécutée automatiquement toutes les heures par Jeedom
        public static function cronHourly() {
            
            
            $hour = date('H');
            
            if ($hour % 2 == 0) {
                
                log::add('Suivreuncolis', 'debug', 'refreshdata');
                
                //Suivreuncolis::MAJColis();
            }
            
            Suivreuncolis::MAJColis();
            
        }
        
        
        /*
         * Fonction exécutée automatiquement tous les jours par Jeedom
         public static function cronDayly() {
         
         }
         */
        
        
        
        /*     * *********************Méthodes d'instance************************* */
        
        public function preInsert() {
            
        }
        
        public function postInsert() {
            
            
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
            
        }
        
        public function preSave() {
            
        }
        
        public function postSave() {
            
            //Suivreuncolis::MAJColis();
            
        }
        
        public function preUpdate() {
            
        }
        
        public function postUpdate() {
            
        }
        
        public function preRemove() {
            
        }
        
        public function postRemove() {
            
        }
        
        
        public function toHtml($_version = 'dashboard') {
            
         
            if ($this->getIsEnable() != 1) {
                return '';
            }
            if (!$this->hasRight('r')) {
                return '';
            }
            
            $version = jeedom::versionAlias($_version);
            if ($this->getDisplay('hideOn' . $version) == 1) {
                return '';
            }
            
            
            $mc = cache::byKey('ColisWidget' . $_version . $this->getId() );
            if ($mc->getValue() != '' && $mc->getOptions('#dateheure#','-') != '-') {              
              return preg_replace("/" . preg_quote(self::UIDDELIMITER) . "(.*?)" . preg_quote(self::UIDDELIMITER) . "/", self::UIDDELIMITER . mt_rand() . self::UIDDELIMITER, $mc->getValue());
            }
             
            $html_forecast = '';
            
            $replace = array(
                             '#id#' => $this->getId(),
                             '#collectDate#' => '',
                             '#background_color#' => $this->getBackgroundColor($_version),
                             '#eqLink#' => $this->getLinkToConfiguration(),
                             );
            
            
            
            
            
            $temperature = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'dateheure');
            $replace['#dateheure#'] = is_object($temperature) ? $temperature->execCmd() : '?';
            
            $humidity = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'etat');
            $replace['#etat#'] = is_object($humidity) ? $humidity->execCmd() : '';
            
            $pressure = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'lieu');
            $replace['#lieu#'] = is_object($pressure) ? $pressure->execCmd() : '';
            
            
            $codeetat = SuivreuncolisCmd::byEqLogicIdCmdName($this->getId(),'codeetat');
            $code = is_object($codeetat) ? $codeetat->execCmd() : '';
            $replace['#jauge#'] = $code;
            
            
            $replace['#name_display#'] = $this->getName();
            
            $parameters = $this->getDisplay('parameters');
            if (is_array($parameters)) {
                foreach ($parameters as $key => $value) {
                    $replace['#' . $key . '#'] = $value;
                }
            }
            
            $html = template_replace($replace, getTemplate('core', $_version, 'colis', 'Suivreuncolis'));
            cache::set('ColisWidget' . $_version . $this->getId(), $html, 0);
            return $html;
         
        }
        
        
        
         
        
        /*     * **********************Getteur Setteur*************************** */
    }
    
    class SuivreuncolisCmd extends cmd {
        /*     * *************************Attributs****************************** */
        
        
        /*     * ***********************Methode static*************************** */
        
        
        /*     * *********************Methode d'instance************************* */
        
        /*
         * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
         public function dontRemoveCmd() {
         return true;
         }
         */
        
        public function execute($_options = array()) {
            
            
            
            
        }
        
        
        
        
        
        
        /*     * **********************Getteur Setteur*************************** */
    }
    
    ?>