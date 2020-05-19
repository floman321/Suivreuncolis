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
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
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

                if ($statusbrut == '') $codetat = 0;
                if ($statusbrut == 'PC1' || $statusbrut == 'PC2' || $statusbrut == 'DR1' ) $codetat = 5;
                if ($statusbrut == 'ET1' || $statusbrut == 'ET2' || $statusbrut == 'ET3' || $statusbrut == 'ET4' || $statusbrut == 'DO2' || $statusbrut == 'DO1' ) $codetat = 10;
                if ($statusbrut == 'DO3' || $statusbrut == 'PB1' || $statusbrut == 'PB2') $codetat = 50;
                if ($statusbrut == 'ND1' || $statusbrut == 'RE1' ) $codetat = 35;
                if ($statusbrut == 'MD2' || $statusbrut == 'EP1' ) $codetat = 30;
                if ($statusbrut == 'DI1' || $statusbrut == 'DI1' ) $codetat = 40;

                log::add('Suivreuncolis', 'debug', '    debug status '.$statusbrut);

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

    function AfterShipRecupere () {

        $apikey = config::byKey('api_aftership', 'suivreuncolis','');

        if ($apikey == ''){
            log::add('Suivreuncolis', 'error', 'Api key Aftership manquante'.$apikey );
            return array("","","","","");
        }

        log::add('Suivreuncolis', 'debug', ' RECUPERE AfterShip ');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.aftership.com/v4/trackings?lang=en");
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

                log::add('Suivreuncolis', 'debug', 'recupere 200 '.count($mescolis)." colis aftership");

                foreach ($mescolis as $monitem){

                    log::add('Suivreuncolis', 'debug', 'recupere colis essai ...'.$monitem['tag']);

                    $EquipementJeedom = eqLogic::byTypeAndSearhConfiguration('Suivreuncolis','"numsuivi":"'.$monitem['tracking_number'].'"');
                    if (is_array($EquipementJeedom) && count($EquipementJeedom) != 0) {

                        $nbtotal = count($monitem['checkpoints']) -1;
                        //$monitem = $monitem['checkpoints'][$nbtotal];

                        log::add('Suivreuncolis', 'debug', '  RECUPere  debug nb occurence '.$nbtotal.' '.$monitem['last_updated_at']);

                        $dh = str_replace('T',' ',str_replace(array('T00:00:00','+00:00'),'',$monitem['last_updated_at']));
                        $msg = $monitem['checkpoints'][$nbtotal]['message'];
                        $lieu = $monitem['checkpoints'][$nbtotal]['location'];
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
                            case 'AvailableForPickup':
                                $codetat = 40;
                                break;
                            case 'Exception':
                                $codetat = 50;
                                break;
                            case 'Expired':
                                $codetat = 20;
                                break;
                        }

                        log::add('Suivreuncolis', 'debug', '-----------recupere code etat '.$statusbrut.'/'.$codetat.'/'.$monitem['id']." colis aftership");

                        Suivreuncolis::majcmdEquipement($EquipementJeedom[0],$msg,$lieu,$dh,$codetat,$msg);

                    }

                }
            }
        }
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

        return array($etat_,'',$date_,10,$description);

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


    public static function buildMessage($nom, $numcolis, $lecommentaire, $msgtransporteur, $etat, $lieu, $dateheure){
        $format_notif = config::byKey('format_notif', 'suivreuncolis','');
        if(!$format_notif){
            return "Changement d'état du colis $nom N°$numcolis $lecommentaire $msgtransporteur - $etat";
        }

        $format_notif = str_replace("#etat#", $etat, $format_notif);
        $format_notif = str_replace("#msgtransporteur#", $msgtransporteur, $format_notif);
        if(isset($dateheure) && $dateheure){
            $format_notif = str_replace("#dateheure#", date(config::byKey('format_date', 'Suivreuncolis', 'd/m/Y H:i'), strtotime($dateheure)), $format_notif);
        }else{
            $format_notif = str_replace("#dateheure#", '', $format_notif);
        }
        $format_notif = str_replace("#nom#", $nom, $format_notif);
        $format_notif = str_replace("#lieu#", $lieu, $format_notif);
        $format_notif = str_replace("#numcolis#", $numcolis, $format_notif);
        $format_notif = str_replace("#commentaire#", $lecommentaire, $format_notif);
        return $format_notif;
    }


    public static function majcmdEquipement($suivreUnColis,$etat,$lieu,$dateheure,$codeetat,$msgtransporteur){

        $nom = $suivreUnColis->getName();
        $lecommentaire = $suivreUnColis->getConfiguration('commentaire','');
        $numcolis = $suivreUnColis->getConfiguration('numsuivi','');

        $notif = config::byKey('notificationpar', 'suivreuncolis','');
        log::add('Suivreuncolis', 'debug', 'majcmdEquipement '.$nom);

        //Simplification de la mise à jour des commandes
        $suivreUnColis->checkAndUpdateCmd('lieu', $lieu);
        $suivreUnColis->checkAndUpdateCmd('dateheure', $dateheure);
        $suivreUnColis->checkAndUpdateCmd('etat', $etat);
        $suivreUnColis->checkAndUpdateCmd('msgtransporteur', $msgtransporteur);
        //cas particulier pour le codeetat
        $cmd = $suivreUnColis->getCmd('info', 'codeetat');
        //si le logicalId n'a pas été mis à jour, sauvegarde de l'eqLogic afin de mettre à jour les logicalId.
        if(!is_object($cmd)){
            $suivreUnColis->save();
            $cmd = $suivreUnColis->getCmd('info', 'codeetat');
        }


        if ($cmd->execCmd() != $codeetat){

            log::add('Suivreuncolis', 'debug', 'notif '.$notif);
            if ($notif == "jeedom_msg"){
                message::add('Suivreuncolis','Changement d\'état du colis '.$nom.' '.$lecommentaire.' '.$msgtransporteur.' | '.$cmd->getValue().' => '.$etat );
            }

            if ($notif == "cmd"){
                $cmd_notif = config::byKey('cmd_notif', 'suivreuncolis','');
                $message = self::buildMessage($nom, $numcolis, $lecommentaire, $msgtransporteur, $etat, $lieu, $dateheure);
                $option = array('title' => 'Changement d\'état du colis '.$nom, 'message' => $message);

                log::add('Suivreuncolis', 'debug', 'notif '.$notif . ' ' .$cmd_notif . ' ' . $message);
                //code pour palier à l'ancienne méthode de l'id direct
                if(is_string($cmd_notif)){
                    foreach (explode('&&', $cmd_notif) as $c){
                        if($c != ''){
                            cmd::byString($c)->execCmd($option);
                        }
                    }
                }else{
                    cmd::byId($cmd_notif)->execCmd($option);
                }
            }
        }

        $suivreUnColis->checkAndUpdateCmd('codeetat', $codeetat);
        $suivreUnColis->refreshWidget();
    }


    public static function MAJColis() {
        Suivreuncolis::AfterShipRecupere();
        foreach (self::byType('Suivreuncolis') as $suivreUnColis) {

            if($suivreUnColis->getLogicalId() == 'list'){
                continue;
            }
            if ($suivreUnColis->getIsEnable() == 1) {

                $nom = $suivreUnColis->getName();
                $transnom = $suivreUnColis->getConfiguration('transporteur','');
                $numcolis = $suivreUnColis->getConfiguration('numsuivi',0);
                log::add('Suivreuncolis', 'debug', 'refreshdata MAJColis essai ' . $transnom . ' Nom Colis : ' . $nom);
                $ac = $suivreUnColis->getConfiguration('autocreate',0);
                $ad = $suivreUnColis->getConfiguration('autodelete',0);
                $codeetatCmd =  $suivreUnColis->getCmd(null, 'codeetat');
                if(is_object($codeetatCmd)){
                    $codeetat = $codeetatCmd->execCmd();
                }
                $dateheureCmd =  $suivreUnColis->getCmd(null, 'dateheure');
                if(is_object($dateheureCmd)){
                    $dateheure = $dateheureCmd->execCmd();
                }


                if ($numcolis == '') continue;


                log::add('Suivreuncolis', 'debug', 'refreshdata MAJColis essai '.$transnom.' Nom Colis : '.$nom);
                log::add('Suivreuncolis', 'debug', '  AutoCreate = '.$ac.' AutoDelete : '.$ad);

                //Si le colis est livré depuis 15 jours, alors on le supprime de la liste
                $interval =  floor( (((time() - strtotime($dateheure))/60)/60)/24);
                log::add('Suivreuncolis', 'debug', '    codeetat : '.$codeetat.' dateheure : '.$dateheure.'  -> +'.$interval.'J');
                if ($codeetat == '40' && $interval >= 15) {
                    $suivreUnColis->remove();
                    continue;
                    log::add('Suivreuncolis', 'debug', 'Colis livré et vieux de plus de 15 jours -> suppression');
                } else {
                    log::add('Suivreuncolis', 'debug', 'Colis non livré ou livré depuis moins de 15 jours -> pas de suppression');
                }

                switch ($transnom) {
                    case "sky56":
                        list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::SkyRecupere($numcolis);
                        Suivreuncolis::majcmdEquipement($suivreUnColis,$etat,$lieu,$dateheure,$codeetat,$msgtransporteur);
                        break;
                    case "aliexpress":
                        list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::AliexpressShippingRecupere($numcolis);
                        Suivreuncolis::majcmdEquipement($suivreUnColis,$etat,$lieu,$dateheure,$codeetat,$msgtransporteur);
                        break;
                    case "laposte":
                        list($etat,$lieu,$dateheure,$codeetat,$msgtransporteur) = Suivreuncolis::LaPosteRecupere($numcolis);
                        Suivreuncolis::majcmdEquipement($suivreUnColis,$etat,$lieu,$dateheure,$codeetat,$msgtransporteur);
                        break;
                }
            }
        }

    }




    public static function importAfterShip(){

        $apikey = config::byKey('api_aftership', 'suivreuncolis','');

        if ($apikey == ''){
            log::add('Suivreuncolis', 'debug', 'Api key Aftership manquante'.$apikey );
            return '';
        }


        log::add('Suivreuncolis', 'debug', ' importAfterShip ');

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
                log::add('Suivreuncolis', 'debug', 'http 200 import');

                foreach ($mescolis as $moncolis){

                    log::add('Suivreuncolis', 'debug', 'http colis ');

                    if (eqLogic::byTypeAndSearhConfiguration('Suivreuncolis','"numsuivi":"'.$moncolis['tracking_number'].'"') == null){
                        $nom = "SansNom";
                        if ($moncolis['title'] != ''){
                            $nom=$moncolis['title'];
                        }
                        log::add('Suivreuncolis', 'debug', ' => Importation 1 colis depuis AfterShip : '.$moncolis['tracking_number'].'-'.$nom);
                        if ($moncolis['shipment_delivery_date'] == null && $moncolis['tag'] != 'Delivered') {
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
                            log::add('Suivreuncolis', 'debug', '   - Colis déja livré pas d import : '.$moncolis['shipment_delivery_date']);
                        }
                    }

                }
            }
        }

        Suivreuncolis::MAJColis();

        return 'ok';
    }

    public static function createListEqLogic() {
        $eqLogic = eqLogic::byLogicalId('list', 'Suivreuncolis');
        if (!is_object($eqLogic)) {
            $eqLogic = new Suivreuncolis();
            $eqLogic->setName('Mes colis');
            $eqLogic->setLogicalId('list');
            $eqLogic->setEqType_name('Suivreuncolis');
            $eqLogic->setIsVisible(1);
            $eqLogic->setIsEnable(1);
            $eqLogic->save();
        }
    }


    public static function refreshList() {
        log::add('Suivreuncolis', 'debug', '  refreshList');
        $eqLogic = eqLogic::byLogicalId('list', 'Suivreuncolis');
        if (is_object($eqLogic)) {

            log::add('Suivreuncolis', 'debug', '  refreshListOK');
            $eqLogic->refreshWidget();
        }
    }

    /*     * ***********************Methode static*************************** */

    /*
    // * Fonction exécutée automatiquement toutes les minutes par Jeedom
     public static function cron() {
        Suivreuncolis::MAJColis();

     }*/


    //* Fonction exécutée automatiquement toutes les heures par Jeedom
    public static function cronHourly() {
        Suivreuncolis::MAJColis();
    }


    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
     */
    public static function cronDaily() {
        Suivreuncolis::importAfterShip();
    }



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        $objetpardefaut = config::byKey('objetpardefaut', 'suivreuncolis','');
        if ( $objetpardefaut == ''){
        }else{
            $this->setObject_id($objetpardefaut);
        }
        $this->setIsEnable(1);
    }

    public function postInsert() {
        log::add('Suivreuncolis', 'debug', '   - Postinsert : '.$this->getId(). ' - '. $this->getName().' - '.$this->getLogicalId());
        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Code état');
        $cmd->setLogicalId('codeetat');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setConfiguration('maxValue', '50');
        $cmd->setType('info');
        $cmd->setTemplate('dashboard', 'Suivreuncolis::codeetat');
        $cmd->setTemplate('mobile', 'Suivreuncolis::codeetat');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();


        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Etat');
        $cmd->setLogicalId('etat');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setType('info');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();


        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Etat original');
        $cmd->setLogicalId('msgtransporteur');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setType('info');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();


        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Lieu');
        $cmd->setLogicalId('lieu');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setType('info');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();


        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Commentaire');
        $cmd->setLogicalId('moncommentaire');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setType('info');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();

        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Horodatage');
        $cmd->setLogicalId('dateheure');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('string');
        $cmd->setType('info');
        $cmd->setDisplay('showNameOndashboard', 0);
        $cmd->setDisplay('showNameOnmobile', 0);
        $cmd->setDisplay('showIconAndNamedashboard', 0);
        $cmd->setDisplay('showIconAndNamemobile', 0);
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();



        $cmd = new SuivreuncolisCmd();
        $cmd->setName('Rafraichir');
        $cmd->setLogicalId('refresh');
        $cmd->setEqLogic_id($this->getId());
        $cmd->setSubType('other');
        $cmd->setType('action');
        $cmd->setIsHistorized(0);
        $cmd->setIsVisible(1);
        $cmd->save();

        $this->refreshList();

    }

    public function preSave() {





    }

    public function postSave() {

        if($this->getLogicalId() == 'list'){
            return;
        }

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

    public function postUpdate() {
        if($this->getLogicalId() == 'list'){
            return;
        }
        /*
        Après chaque mise à jour, si la commande est vide, elle sera masquee sur le dashboard
        */
        foreach ($this->getCmd() as $cmd) {
            if($cmd->getLogicalId()!='codeetat' && $cmd->getLogicalId()!='refresh'){
                if($cmd->execCmd() != ''){
                    $cmd->setIsVisible(1);
                }else{
                    $cmd->setIsVisible(0);
                }
                $cmd->save();
            }
        }
        //Si l'état et le msg transporteur sont identiques, on masque le msgtransporteur
        $cmdEtat =  $this->getCmd(null, 'etat');
        $etat = null;
        if(is_object($cmdEtat)){
            $etat = $cmdEtat->execCmd();
        }
        $cmdMsgTransporteur =  $this->getCmd(null, 'msgtransporteur');
        if(is_object($cmdMsgTransporteur)){
            if($etat == $cmdMsgTransporteur->execCmd()){
                $cmdMsgTransporteur->setIsVisible(0);
                $cmdMsgTransporteur->save();
            }
        }
        $this->refreshList();
    }


    public function postRemove() {

        $this->refreshWidget();
        if($this->getLogicalId() == 'list'){

            return;
        }
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
        $this->refreshList();

    }


    public static function templateWidget(){
        $return = array('info' => array('string' => array()));
        $return['info']['string']['codeetat'] = array(
            'template' => 'tmplmultistate',
            'replace' => array('#_time_widget_#' => 0),
            'test' => array(
                array('operation' => '#value#==""', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat('') . '" height=38px width=38px />'),
                array('operation' => '#value#==0', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(0) . '" height=38px width=38px />'),
                array('operation' => '#value#==5', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(5) . '" height=38px width=38px />'),
                array('operation' => '#value#==10', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(10) . '" height=38px width=38px />'),
                array('operation' => '#value#==20', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(20) . '" height=38px width=38px />'),
                array('operation' => '#value#==30', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(30) . '" height=38px width=38px />'),
                array('operation' => '#value#==35', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(35) . '" height=38px width=38px />'),
                array('operation' => '#value#==40', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(40) . '" height=38px width=38px />'),
                array('operation' => '#value#==50', 'state_light' => '<img src="' . Suivreuncolis::getIconEtat(50) . '" height=38px width=38px />')
            )
        );
        return $return;
    }

    private function getIconEtat($etat){
        switch($etat){
            case 5:
                return 'plugins/Suivreuncolis/3rparty/status-info-receive.svg';
            case 10:
                return 'plugins/Suivreuncolis/3rparty/status-in-transit.svg';
            case 30:
                return 'plugins/Suivreuncolis/3rparty/status-out-for-delivery.svg';
            case 35:
                return 'plugins/Suivreuncolis/3rparty/status-attemptfail.svg';
            case 40:
                return 'plugins/Suivreuncolis/3rparty/status-delivered.svg';
            default:
                return 'plugins/Suivreuncolis/3rparty/status-expired.svg';
        }
    }

    public function buildList() {
        if ($this->getLogicalId() == 'list') {
            return;
        }
        $return = array(
            'id' => $this->getLogicalId(),
            'name' => $this->getName(),
        );
        $cmds = $this->getCmd('info');
        foreach ($cmds as $cmd) {
            $return[$cmd->getLogicalId()] = $cmd->execCmd();
            if ($cmd->getLogicalId() == 'dateheure') {
                $timestamp = $return[$cmd->getLogicalId()];
                $return['horodatage'] = "le " . date("d/m/Y à H:i", strtotime($timestamp));
            }
        }
        return $return;
    }

    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version, array(), true);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        $replace['#text_color#'] = $this->getConfiguration('text_color');
        $replace['#version#'] = $_version;
        $replace['#logicalId#'] = $this->getLogicalId();
        $refresh = $this->getCmd(null, 'refresh');
        if (is_object($refresh)) {
            $replace['#refresh_id#'] = $refresh->getId();
        }
        if ($this->getLogicalId() == 'list') {
            $replace['#colis#'] = '<div class="cards">';
            $data = array();
            $eqLogics = self::byType('Suivreuncolis', true);
            $eqLogicList = self::byLogicalId('list', 'Suivreuncolis');
            $visibles = array();
            if(is_object($eqLogicList)){
                $cmds = $eqLogicList->getCmd(null, null, null);
                foreach ($cmds as $cmd) {
                    $visibles[$cmd->getLogicalId()] = $cmd->getIsVisible();
                }
            }else{
                $visibles['etat'] = true;
                $visibles['codeetat'] = true;
            }
            foreach ($eqLogics as $eqLogic) {
                if ($eqLogic->getLogicalId() == 'list') {
                    continue;
                }
                $data[$eqLogic->getId()] = $eqLogic->buildList();
                $replace['#colis#'] .= '<div class="card">';
                $replace['#colis#'] .= '<div class="title">';
                if($visibles['codeetat']){
                    $replace['#colis#'] .= '<img src="'.$this->getIconEtat($data[$eqLogic->getId()]['codeetat']).'" height=20px width=20px />';
                }
                if($version == 'dashboard'){
                    $replace['#colis#'] .= '<a href="'.$eqLogic->getLinkToConfiguration().'" class="objectName">'.$eqLogic->getName().'</a>';
                }else{
                    $replace['#colis#'] .= '<span class="objectName">'.$eqLogic->getName().'</span>';
                }
                $replace['#colis#'] .= '<span onclick="deleteColis('.$eqLogic->getId().',\''.$eqLogic->getName().'\')" class="fas fa-trash icon_red cursor eqLogicAction " data-action="remove"></span>';
                $replace['#colis#'] .= '</div>';
                foreach ($visibles as $key => $value) {
                    if($value && ($key != 'codeetat' && $key != 'refresh') && $data[$eqLogic->getId()][$key] != ''){
                        if(($key == 'etat' && $visibles['msgtransporteur']) && $data[$eqLogic->getId()]['etat'] == $data[$eqLogic->getId()]['msgtransporteur']){
                            continue;
                        }
                        if($key == 'dateheure'){
                            $replace['#colis#'] .= '<div class="status" title="'.$data[$eqLogic->getId()][$key].'">'.date(config::byKey('format_date', 'Suivreuncolis', 'd/m/Y H:i'), strtotime($data[$eqLogic->getId()][$key])).'</div>';
                        }else{
                            $replace['#colis#'] .= '<div class="status" title="'.$data[$eqLogic->getId()][$key].'">'.$data[$eqLogic->getId()][$key].'</div>';
                        }
                    }
                }
                $replace['#colis#'] .= '</div>';
            }
            $replace['#colis#'] .= '</div>';
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'Suivreuncolis_list', 'Suivreuncolis')));
        } else {
            $cmd_html = '';
            $br_before = 0;
            foreach ($this->getCmd(null, null, true) as $cmd) {
                if (isset($replace['#refresh_id#']) && $cmd->getId() == $replace['#refresh_id#']) {
                    continue;
                }
                if ($cmd->execCmd() == '') {
                    continue;
                }
                if ($br_before == 0 && $cmd->getDisplay('forceReturnLineBefore', 0) == 1) {
                    $cmd_html .= '<br/>';
                }
                if ($cmd->getLogicalId() == 'dateheure') {
                    $replaceCmd = array(
                        '#id#' => $cmd->getId(),
                        '#name#' => $cmd->getName(),
                        '#name_display#' => ($cmd->getDisplay('icon') != '') ? $cmd->getDisplay('icon') : $cmd->getName(),
                        '#history#' => '',
                        '#hide_history#' => 'hidden',
                        '#logicalId#' => $cmd->getLogicalId(),
                        '#uid#' => 'cmd' . $cmd->getId() . eqLogic::UIDDELIMITER . mt_rand() . eqLogic::UIDDELIMITER,
                        '#version#' => $_version,
                        '#eqLogic_id#' => $cmd->getEqLogic_id(),
                        '#hide_name#' => ''
                    );
                    if ($cmd->getDisplay('showNameOn' . $_version, 1) == 0) {
                        $replaceCmd['#hide_name#'] = 'hidden';
                    }
                    if ($cmd->getDisplay('showIconAndName' . $_version, 0) == 1) {
                        $replaceCmd['#name_display#'] = $cmd->getDisplay('icon') . ' ' . $cmd->getName();
                    }
                    $template = $cmd->getWidgetTemplateCode($_version);
                    $replaceCmd['#state#'] = ($replaceCmd['#logicalId#'] == 'dateheure' ? date(config::byKey('format_date', 'Suivreuncolis', 'd/m/Y H:i'), strtotime($cmd->execCmd())) : $cmd->execCmd());
                    $replaceCmd['#state#'] = str_replace(array("\'", "'","\n"), array("'", "\'",'<br/>'), $replaceCmd['#state#']);
                    $replaceCmd['#valueName#'] = $cmd->getName();
                    $cmd_html .= translate::exec(template_replace($replaceCmd, $template), 'core/template/widgets.html');
                }else{
                    $cmd_html .= $cmd->toHtml($_version);
                }
                $br_before = 0;
                if ($cmd->getDisplay('forceReturnLineAfter', 0) == 1) {
                    $cmd_html .= '<br/>';
                    $br_before = 1;
                }
            }
            $replace['#cmd#'] = $cmd_html;
            return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'eqLogic')));
        }
    }
}

class SuivreuncolisCmd extends cmd {

    public function execute($_options = array()) {
        if ($this->getLogicalId() == 'refresh') {
            Suivreuncolis::MAJColis();
        }
    }
}
