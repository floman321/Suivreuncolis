<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('Suivreuncolis');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

    <div class="row row-overflow">
        <div class="col-xs-12 eqLogicThumbnailDisplay">
            <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
            <div class="eqLogicThumbnailContainer">
                <div class="cursor eqLogicAction logoPrimary" data-action="add">
                    <i class="fas fa-plus-circle"></i>
                    <br>
                    <span>{{Ajouter}}</span>
                </div>
                <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                    <i class="fas fa-wrench"></i>
                    <br>
                    <span>{{Configuration}}</span>
                </div>
            </div>
            <legend><i class="icon divers-mailbox15"></i> Mes Suivis</legend>
            <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                    echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
                    $transnom = $eqLogic->getConfiguration('transporteur','');
                    $icon =  'plugins/Suivreuncolis/3rparty/'.$transnom.'.png';
                    if(!file_exists($icon)){
                        $icon = $plugin->getPathImgIcon();
                    }
                    echo '<img src="' . $icon . '"/>';
                    echo '<br>';
                    echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <div class="col-xs-12 eqLogic" style="display: none;">
            <div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
            </div>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
                <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
                <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
            </ul>
            <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
                <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                    <br/>
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Nom de l'équipement template}}</label>
                                <div class="col-sm-3">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                                <div class="col-sm-3">
                                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                        <option value="">{{Aucun}}</option>
                                        <?php
                                        foreach (jeeObject::all() as $object) {
                                            echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                                <div class="col-sm-9">
                                    <?php
                                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                        echo '<label class="checkbox-inline">';
                                        echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-9">
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Numéro Suivi</label>
                                <div class="col-sm-3">
                                    <input type="text" id="numcolis" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="numsuivi" placeholder="N° Suivi"/ onblur="rechercher();">

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Commentaire</label>
                                <div class="col-sm-3">
                                    <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="commentaire" placeholder="Destination, Nom du E-Commerce ..."/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Transporteur</label>
                                <div class="col-sm-3">

                                    <script type="text/javascript">

                                        var transp = "";

                                        function transporteurchange(this_select)
                                        {
                                            transp = this_select.value;

                                            if (this_select.value == "aftership")
                                            {
                                                document.getElementById('aftership').style.display = 'block';
                                            }
                                            else{
                                                document.getElementById('aftership').style.display = 'none';
                                            }
                                        }

                                        function rechercher()
                                        {
                                            if (transp == "aftership"){

                                                $.ajax({
                                                    type : 'POST',
                                                    headers: { 'aftership-api-key': '<?php echo config::byKey('api_aftership', 'suivreuncolis',''); ?>' },
                                                    url : 'https://api.aftership.com/v4/couriers/detect',
                                                    dataType : 'json',
                                                    data : '{"tracking":{"tracking_number": "'+document.getElementById('numcolis').value+'"}}',
                                                    success : function(resultat, statut){

                                                        var cuisines = resultat.data.couriers;
                                                        var sel = document.getElementById('ListeTransporteurs');
                                                        //$("#ListeTransporteurs").empty();
                                                        for(var i = 0; i < cuisines.length; i++) {
                                                            var opt = document.createElement('option');
                                                            opt.innerHTML = cuisines[i].name;
                                                            opt.value = cuisines[i].slug;
                                                            if (i==0){opt.selected = true; }
                                                            sel.insertBefore(opt,sel[0]);
                                                        }

                                                    }
                                                });

                                            }
                                        }

                                    </script>

                                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="transporteur" onchange="transporteurchange(this);">
                                        <option value='aftership'>AfterShip</option>
                                        <option value='laposte'>La Poste</option>
                                        <option value='sky56'>Sky56</option>
                                        <option value='aliexpress'>AliExpress Shipping</option>
                                    </select>

                                </div>
                            </div>

                            <div id="aftership" style="display:none;">

                                <h3>AfterShip</h3>

                                <p>Vous devez avoir une cle API pour utiliser ce service (voir <a href='https://secure.aftership.com/apps/api'>https://secure.aftership.com/apps/api</a>)
                                <p>Cette partie n'est pas obligatoire dans jeedom (utilisé pour auto création dans AfterShip)</p>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Transporteur AfterShip</label>
                                    <div class="col-sm-3">
                                        <select id="ListeTransporteurs" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="transaftership">
                                            <option value=''></option>
                                            <option value='007ex'>007EX</option>
                                            <option value='17postservice'>17 Post Service</option>
                                            <option value='2ebox'>2ebox</option>
                                            <option value='2go'>2GO</option>
                                            <option value='360lion'>360 Lion Express</option>
                                            <option value='4-72'>4-72 Entregando</option>
                                            <option value='4px'>4PX</option>
                                            <option value='800bestex'>Best Express</option>
                                            <option value='aaa-cooper'>AAA Cooper</option>
                                            <option value='abcustom'>AB Custom Group</option>
                                            <option value='abf'>ABF Freight</option>
                                            <option value='abxexpress-my'>ABX Express</option>
                                            <option value='acommerce'>aCommerce</option>
                                            <option value='acscourier'>ACS Courier</option>
                                            <option value='acsworldwide'>ACS Worldwide Express</option>
                                            <option value='adicional'>Adicional Logistics</option>
                                            <option value='adsone'>ADSOne</option>
                                            <option value='aduiepyle'>A Duie Pyle</option>
                                            <option value='aeroflash'>Mexico AeroFlash</option>
                                            <option value='aersure'>Aersure</option>
                                            <option value='air21'>AIR21</option>
                                            <option value='airpak-express'>Airpak Express</option>
                                            <option value='airspeed'>Airspeed International Corporation</option>
                                            <option value='alfatrex'>AlfaTrex</option>
                                            <option value='allied-express-ftp'>Allied Express</option>
                                            <option value='alliedexpress'>Allied Express</option>
                                            <option value='alljoy'>ALLJOY SUPPLY CHAIN CO.</option>
                                            <option value='alphafast'>alphaFAST</option>
                                            <option value='always-express'>Always Express</option>
                                            <option value='amazon'>Amazon Ground</option>
                                            <option value='amazon-fba-us'>Amazon FBA USA</option>
                                            <option value='amazon-logistics-uk'>Amazon Logistics</option>
                                            <option value='an-post'>An Post</option>
                                            <option value='antron'>Antron Express</option>
                                            <option value='ao-courier'>AO Logistics</option>
                                            <option value='apc'>APC Postal Logistics</option>
                                            <option value='apc-overnight'>APC Overnight</option>
                                            <option value='apc-overnight-connum'>APC Overnight Consignment Number</option>
                                            <option value='apg'>APG eCommerce Solutions Ltd.</option>
                                            <option value='aprisaexpress'>Aprisa Express</option>
                                            <option value='aquiline'>Aquiline</option>
                                            <option value='aramex'>Aramex</option>
                                            <option value='arrowxl'>Arrow XL</option>
                                            <option value='asendia-de'>Asendia Germany</option>
                                            <option value='asendia-hk'>Asendia HK</option>
                                            <option value='asendia-uk'>Asendia UK</option>
                                            <option value='asendia-usa'>Asendia USA</option>
                                            <option value='asm'>ASM</option>
                                            <option value='aupost-china'>AuPost China</option>
                                            <option value='australia-post'>Australia Post</option>
                                            <option value='australia-post-api'>Australia Post API</option>
                                            <option value='austrian-post'>Austrian Post (Express)</option>
                                            <option value='austrian-post-registered'>Austrian Post (Registered)</option>
                                            <option value='averitt'>Averitt Express</option>
                                            <option value='b2ceurope'>B2C Europe</option>
                                            <option value='belpost'>Belpost</option>
                                            <option value='bert-fr'>Bert Transport</option>
                                            <option value='bestwayparcel'>Best Way Parcel</option>
                                            <option value='bgpost'>Bulgarian Posts</option>
                                            <option value='bh-posta'>JP BH Pošta</option>
                                            <option value='bh-worldwide'>B&H Worldwide</option>
                                            <option value='birdsystem'>BirdSystem</option>
                                            <option value='bluecare'>Bluecare Express Ltd</option>
                                            <option value='bluedart'>Bluedart</option>
                                            <option value='bluestar'>Blue Star</option>
                                            <option value='bneed'>Bneed</option>
                                            <option value='bond'>Bond</option>
                                            <option value='bondscouriers'>Bonds Couriers</option>
                                            <option value='borderexpress'>Border Express</option>
                                            <option value='box-berry'>Boxberry</option>
                                            <option value='boxc'>BoxC</option>
                                            <option value='bpost'>Bpost</option>
                                            <option value='bpost-international'>Bpost international</option>
                                            <option value='brazil-correios'>Brazil Correios</option>
                                            <option value='bring'>Bring</option>
                                            <option value='brt-it'>BRT Bartolini</option>
                                            <option value='brt-it-parcelid'>BRT Bartolini(Parcel ID)</option>
                                            <option value='brt-it-sender-ref'>BRT Bartolini(Sender Reference)</option>
                                            <option value='budbee-webhook'>Budbee</option>
                                            <option value='buylogic'>Buylogic</option>
                                            <option value='cae-delivers'>CAE Delivers</option>
                                            <option value='cambodia-post'>Cambodia Post</option>
                                            <option value='canada-post'>Canada Post</option>
                                            <option value='canpar'>Canpar Courier</option>
                                            <option value='carriers'>Carriers</option>
                                            <option value='carry-flap'>Carry-Flap Co.</option>
                                            <option value='cbl-logistica'>CBL Logistics</option>
                                            <option value='cbl-logistica-api'>CBL Logistica</option>
                                            <option value='cdek-tr'>CDEK TR</option>
                                            <option value='celeritas'>Celeritas Transporte</option>
                                            <option value='ceska-posta'>Česká Pošta</option>
                                            <option value='ceva'>CEVA LOGISTICS</option>
                                            <option value='ceva-tracking'>CEVA Package</option>
                                            <option value='cfl-logistics'>CFL Logistics</option>
                                            <option value='champion-logistics'>Champion Logistics</option>
                                            <option value='china-ems'>China EMS (ePacket)</option>
                                            <option value='china-post'>China Post</option>
                                            <option value='chitchats'>Chit Chats</option>
                                            <option value='chrobinson'>C.H. Robinson Worldwide</option>
                                            <option value='chronopost-france'>Chronopost France</option>
                                            <option value='chronopost-portugal'>Chronopost Portugal</option>
                                            <option value='citylinkexpress'>City-Link Express</option>
                                            <option value='cj-gls'>CJ GLS</option>
                                            <option value='cj-korea-thai'>CJ Korea Express</option>
                                            <option value='cj-malaysia'>CJ Century</option>
                                            <option value='cj-malaysia-international'>CJ Century (International)</option>
                                            <option value='cj-philippines'>CJ Transnational Philippines</option>
                                            <option value='cjlogistics'>CJ Logistics International</option>
                                            <option value='cjpacket'>CJ Packet</option>
                                            <option value='cle-logistics'>CL E-Logistics Solutions Limited</option>
                                            <option value='clevy-links'>Clevy Links</option>
                                            <option value='cloudwish-asia'>Cloudwish Asia</option>
                                            <option value='cnexps'>CNE Express</option>
                                            <option value='colis-prive'>Colis Privé</option>
                                            <option value='colissimo'>Colissimo</option>
                                            <option value='collectco'>CollectCo</option>
                                            <option value='collectplus'>Collect+</option>
                                            <option value='collivery'>MDS Collivery Pty (Ltd)</option>
                                            <option value='con-way'>Con-way Freight</option>
                                            <option value='continental'>Continental</option>
                                            <option value='copa-courier'>Copa Airlines Courier</option>
                                            <option value='cope'>Cope Sensitive Freight</option>
                                            <option value='correos-chile'>Correos Chile</option>
                                            <option value='correos-de-mexico'>Correos de Mexico</option>
                                            <option value='correosexpress'>Correos Express</option>
                                            <option value='correosexpress-api'>Correos Express</option>
                                            <option value='costmeticsnow'>Cosmetics Now</option>
                                            <option value='courex'>Urbanfox</option>
                                            <option value='courier-plus'>Courier Plus</option>
                                            <option value='courierit'>Courier IT</option>
                                            <option value='courierpost'>CourierPost</option>
                                            <option value='couriers-please'>Couriers Please</option>
                                            <option value='cpacket'>cPacket</option>
                                            <option value='ctc-express'>CTC Express</option>
                                            <option value='cubyn'>Cubyn</option>
                                            <option value='cuckooexpress'>Cuckoo Express</option>
                                            <option value='cyprus-post'>Cyprus Post</option>
                                            <option value='dajin'>Shanghai Aqrum Chemical Logistics Co.Ltd</option>
                                            <option value='danmark-post'>PostNord Denmark</option>
                                            <option value='danske-fragt'>Danske Fragtmænd</option>
                                            <option value='dao365'>DAO365</option>
                                            <option value='dawnwing'>Dawn Wing</option>
                                            <option value='dayton-freight'>Dayton Freight</option>
                                            <option value='dbschenker-se'>DB Schenker</option>
                                            <option value='dbschenker-sv'>DB Schenker Sweden</option>
                                            <option value='ddexpress'>DD Express Courier</option>
                                            <option value='delcart-in'>Delcart</option>
                                            <option value='delhivery'>Delhivery</option>
                                            <option value='deliveryontime'>DELIVERYONTIME LOGISTICS PVT LTD</option>
                                            <option value='delnext'>Delnext</option>
                                            <option value='deltec-courier'>Deltec Courier</option>
                                            <option value='demandship'>DemandShip</option>
                                            <option value='descartes'>Innovel</option>
                                            <option value='detrack'>Detrack</option>
                                            <option value='deutsch-post'>Deutsche Post Mail</option>
                                            <option value='dex-i'>DEX-I</option>
                                            <option value='dhl'>DHL Express</option>
                                            <option value='dhl-active-tracing'>DHL Active Tracing</option>
                                            <option value='dhl-benelux'>DHL Benelux</option>
                                            <option value='dhl-deliverit'>DHL 2-Mann-Handling</option>
                                            <option value='dhl-es'>DHL Spain Domestic</option>
                                            <option value='dhl-germany'>Deutsche Post DHL</option>
                                            <option value='dhl-global-forwarding'>DHL Global Forwarding</option>
                                            <option value='dhl-global-mail'>DHL eCommerce US</option>
                                            <option value='dhl-global-mail-asia'>DHL eCommerce Asia</option>
                                            <option value='dhl-global-mail-asia-api'>DHL eCommerce Asia</option>
                                            <option value='dhl-hk'>DHL Hong Kong</option>
                                            <option value='dhl-nl'>DHL Netherlands</option>
                                            <option value='dhl-pieceid'>DHL Express (Piece ID)</option>
                                            <option value='dhl-poland'>DHL Poland Domestic</option>
                                            <option value='dhl-reference'>DHL</option>
                                            <option value='dhl-supply-chain-au'>DHL Supply Chain Australia</option>
                                            <option value='dhl-supplychain-id'>DHL Supply Chain Indonesia</option>
                                            <option value='dhlparcel-es'>DHL Parcel Spain</option>
                                            <option value='dhlparcel-nl'>DHL Parcel NL</option>
                                            <option value='dhlparcel-uk'>DHL Parcel UK</option>
                                            <option value='dimerco'>Dimerco Express Group</option>
                                            <option value='directfreight-au'>Direct Freight Express</option>
                                            <option value='directlog'>Directlog</option>
                                            <option value='dmm-network'>DMM Network</option>
                                            <option value='dms-matrix'>DMSMatrix</option>
                                            <option value='dnj-express'>DNJ Express</option>
                                            <option value='doora'>Doora Logistics</option>
                                            <option value='doordash-webhook'>DoorDash</option>
                                            <option value='dotzot'>Dotzot</option>
                                            <option value='dpd'>DPD</option>
                                            <option value='dpd-de'>DPD Germany</option>
                                            <option value='dpd-fr-reference'>DPD France</option>
                                            <option value='dpd-hk'>DPD HK</option>
                                            <option value='dpd-ireland'>DPD Ireland</option>
                                            <option value='dpd-poland'>DPD Poland</option>
                                            <option value='dpd-ro'>DPD Romania</option>
                                            <option value='dpd-ru'>DPD Russia</option>
                                            <option value='dpd-uk'>DPD UK</option>
                                            <option value='dpe-express'>DPE Express</option>
                                            <option value='dpe-za'>DPE South Africa</option>
                                            <option value='dpex'>DPEX</option>
                                            <option value='dsv'>DSV</option>
                                            <option value='dtdc'>DTDC India</option>
                                            <option value='dtdc-au'>DTDC Australia</option>
                                            <option value='dtdc-express'>DTDC Express Global PTE LTD</option>
                                            <option value='dx-b2b-connum'>DX</option>
                                            <option value='dylt'>Daylight Transport</option>
                                            <option value='dynamic-logistics'>Dynamic Logistics</option>
                                            <option value='easy-mail'>Easy Mail</option>
                                            <option value='ec-firstclass'>EC-Firstclass</option>
                                            <option value='ecargo-asia'>Ecargo</option>
                                            <option value='echo'>Echo</option>
                                            <option value='ecms'>ECMS International Logistics Co.</option>
                                            <option value='ecom-express'>Ecom Express</option>
                                            <option value='ecoutier'>eCoutier</option>
                                            <option value='efex'>eFEx (E-Commerce Fulfillment & Express)</option>
                                            <option value='efs'>EFS (E-commerce Fulfillment Service)</option>
                                            <option value='ekart'>Ekart</option>
                                            <option value='elta-courier'>ELTA Hellenic Post</option>
                                            <option value='emirates-post'>Emirates Post</option>
                                            <option value='empsexpress'>EMPS Express</option>
                                            <option value='endeavour-delivery'>Endeavour Delivery</option>
                                            <option value='ensenda'>Ensenda</option>
                                            <option value='envialia'>Envialia</option>
                                            <option value='ep-box'>EP-Box</option>
                                            <option value='eparcel-kr'>eParcel Korea</option>
                                            <option value='equick-cn'>Equick China</option>
                                            <option value='eshipping'>Eshipping</option>
                                            <option value='estafeta'>Estafeta</option>
                                            <option value='estes'>Estes</option>
                                            <option value='etomars'>Etomars</option>
                                            <option value='etotal'>eTotal Solution Limited</option>
                                            <option value='ets-express'>RETS express</option>
                                            <option value='eu-fleet-solutions'>EU Fleet Solutions</option>
                                            <option value='eurodis'>Eurodis</option>
                                            <option value='exapaq'>DPD France</option>
                                            <option value='expeditors'>Expeditors</option>
                                            <option value='expeditors-api'>Expeditors API</option>
                                            <option value='expresssale'>Expresssale</option>
                                            <option value='ezship'>EZship</option>
                                            <option value='far-international'>FAR international</option>
                                            <option value='fastrak-th'>Fastrak Services</option>
                                            <option value='fasttrack'>Fasttrack</option>
                                            <option value='fastway-au'>Fastway Australia</option>
                                            <option value='fastway-ireland'>Fastway Ireland</option>
                                            <option value='fastway-nz'>Fastway New Zealand</option>
                                            <option value='fastway-za'>Fastway South Africa</option>
                                            <option value='fedex'>FedEx</option>
                                            <option value='fedex-crossborder'>Fedex Cross Border</option>
                                            <option value='fedex-fims'>FedEx International MailService</option>
                                            <option value='fedex-freight'>FedEx Freight</option>
                                            <option value='fedex-uk'>FedEx UK</option>
                                            <option value='fercam'>FERCAM Logistics & Transport</option>
                                            <option value='fetchr'>Fetchr</option>
                                            <option value='fetchr-webhook'>Mena 360 (Fetchr)</option>
                                            <option value='first-flight'>First Flight Couriers</option>
                                            <option value='first-logistics'>First Logistics</option>
                                            <option value='firstmile'>FirstMile</option>
                                            <option value='fitzmark-api'>FitzMark</option>
                                            <option value='flytexpress'>Flyt Express</option>
                                            <option value='fmx'>FMX</option>
                                            <option value='fonsen'>Fonsen Logistics</option>
                                            <option value='forrun'>forrun Pvt Ltd (Arpatech Venture)</option>
                                            <option value='freterapido'>Frete Rápido</option>
                                            <option value='gati-kwe'>Gati-KWE</option>
                                            <option value='gba'>GBA Services Ltd</option>
                                            <option value='gdex'>GDEX</option>
                                            <option value='gemworldwide'>GEM Worldwide</option>
                                            <option value='general-overnight'>Go!Express and logistics</option>
                                            <option value='geodis-calberson-fr'>GEODIS - Distribution & Express</option>
                                            <option value='geodis-espace'>Geodis E-space</option>
                                            <option value='ghn'>Giao hàng nhanh</option>
                                            <option value='globaltranz'>GlobalTranz</option>
                                            <option value='globegistics'>Globegistics Inc.</option>
                                            <option value='gls'>GLS</option>
                                            <option value='gls-croatia'>GLS Croatia</option>
                                            <option value='gls-cz'>GLS Czech Republic</option>
                                            <option value='gls-da'>GLS Denmark</option>
                                            <option value='gls-italy'>GLS Italy</option>
                                            <option value='gls-netherlands'>GLS Netherlands</option>
                                            <option value='gls-slovakia'>GLS General Logistics Systems Slovakia s.r.o.</option>
                                            <option value='gls-slovenia'>GLS Slovenia</option>
                                            <option value='gls-spain'>GLS Spain</option>
                                            <option value='gofly'>GoFly</option>
                                            <option value='gojavas'>GoJavas</option>
                                            <option value='greyhound'>Greyhound</option>
                                            <option value='gsi-express'>GSI EXPRESS</option>
                                            <option value='gso'>GSO</option>
                                            <option value='hct-logistics'>HCT LOGISTICS CO.LTD.</option>
                                            <option value='hdb'>Haidaibao</option>
                                            <option value='hdb-box'>Haidaibao</option>
                                            <option value='helthjem'>Helthjem</option>
                                            <option value='hermes'>Hermesworld</option>
                                            <option value='hermes-de'>Hermes Germany</option>
                                            <option value='hermes-it'>Hermes Italy</option>
                                            <option value='heyworld'>Heyworld</option>
                                            <option value='hh-exp'>Hua Han Logistics</option>
                                            <option value='hipshipper'>Hipshipper</option>
                                            <option value='holisol'>Holisol</option>
                                            <option value='hong-kong-post'>Hong Kong Post</option>
                                            <option value='hrvatska-posta'>Hrvatska Pošta</option>
                                            <option value='hunter-express'>Hunter Express</option>
                                            <option value='huodull'>Huodull</option>
                                            <option value='hx-express'>HX Express</option>
                                            <option value='i-dika'>i-dika</option>
                                            <option value='i-parcel'>i-parcel</option>
                                            <option value='idexpress'>IDEX</option>
                                            <option value='ids-logistics'>IDS Logistics</option>
                                            <option value='imexglobalsolutions'>IMEX Global Solutions</option>
                                            <option value='imxmail'>IMX Mail</option>
                                            <option value='india-post'>India Post Domestic</option>
                                            <option value='india-post-int'>India Post International</option>
                                            <option value='inexpost'>Inexpost</option>
                                            <option value='inpost-paczkomaty'>InPost Paczkomaty</option>
                                            <option value='instant'>INSTANT (Tiong Nam Ebiz Express Sdn Bhd)</option>
                                            <option value='intel-valley'>Intel-Valley Supply chain (ShenZhen) Co. Ltd</option>
                                            <option value='intelipost'>Intelipost (TMS for LATAM)</option>
                                            <option value='interlink-express'>DPD Local</option>
                                            <option value='interlink-express-reference'>DPD Local reference</option>
                                            <option value='international-seur'>International Seur</option>
                                            <option value='intexpress'>Internet Express</option>
                                            <option value='israel-post'>Israel Post</option>
                                            <option value='israel-post-domestic'>Israel Post Domestic</option>
                                            <option value='italy-sda'>Italy SDA</option>
                                            <option value='j-net'>J-Net</option>
                                            <option value='jam-express'>Jam Express</option>
                                            <option value='janco'>Janco Ecommerce</option>
                                            <option value='janio'>Janio Asia</option>
                                            <option value='japan-post'>Japan Post</option>
                                            <option value='jayonexpress'>Jayon Express (JEX)</option>
                                            <option value='jcex'>JCEX</option>
                                            <option value='jersey-post'>Jersey Post</option>
                                            <option value='jet-ship'>Jet-Ship Worldwide</option>
                                            <option value='jinsung'>JINSUNG TRADING</option>
                                            <option value='jne'>JNE</option>
                                            <option value='jne-api'>JNE</option>
                                            <option value='jocom'>Jocom</option>
                                            <option value='jtexpress'>J&T EXPRESS MALAYSIA</option>
                                            <option value='jx'>JX</option>
                                            <option value='k1-express'>K1 Express</option>
                                            <option value='kangaroo-my'>Kangaroo Worldwide Express</option>
                                            <option value='kerry-logistics'>Kerry Express Thailand</option>
                                            <option value='kerrytj'>Kerry TJ Logistics</option>
                                            <option value='kerryttc-vn'>Kerry Express (Vietnam) Co Ltd</option>
                                            <option value='kgmhub'>KGM Hub</option>
                                            <option value='kiala'>Kiala</option>
                                            <option value='kn'>Kuehne + Nagel</option>
                                            <option value='knuk'>KNAirlink Aerospace Domestic Network</option>
                                            <option value='korea-post'>Korea Post EMS</option>
                                            <option value='kpost'>Korea Post</option>
                                            <option value='kurasi'>KURASI</option>
                                            <option value='kwt'>Shenzhen Jinghuada Logistics Co.</option>
                                            <option value='ky-express'>Kua Yue Express</option>
                                            <option value='la-poste-colissimo'>La Poste</option>
                                            <option value='lalamove'>Lalamove</option>
                                            <option value='landmark-global'>Landmark Global</option>
                                            <option value='landmark-global-reference'>Landmark Global Reference</option>
                                            <option value='lao-post'>Lao Post</option>
                                            <option value='lasership'>LaserShip</option>
                                            <option value='latvijas-pasts'>Latvijas Pasts</option>
                                            <option value='lbcexpress'>LBC Express</option>
                                            <option value='legion-express'>Legion Express</option>
                                            <option value='lexship'>LexShip</option>
                                            <option value='lht-express'>LHT Express</option>
                                            <option value='liefery'>liefery</option>
                                            <option value='lietuvos-pastas'>Lietuvos Paštas</option>
                                            <option value='line'>Line Clear Express & Logistics Sdn Bhd</option>
                                            <option value='linkbridge'>Link Bridge(BeiJing)international logistics co.</option>
                                            <option value='lion-parcel'>Lion Parcel</option>
                                            <option value='livrapide'>Livrapide</option>
                                            <option value='logicmena'>Logic Mena</option>
                                            <option value='logistyx-transgroup'>Transgroup</option>
                                            <option value='lonestar'>Lone Star Overnight</option>
                                            <option value='loomis-express'>Loomis Express</option>
                                            <option value='lotte'>Lotte Global Logistics</option>
                                            <option value='lwe-hk'>Logistic Worldwide Express</option>
                                            <option value='m-xpress'>M Xpress Sdn Bhd</option>
                                            <option value='magyar-posta'>Magyar Posta</option>
                                            <option value='mail-box-etc'>Mail Boxes Etc.</option>
                                            <option value='mailamericas'>MailAmericas</option>
                                            <option value='mailplus'>MailPlus</option>
                                            <option value='mailplus-jp'>MailPlus</option>
                                            <option value='mainfreight'>Mainfreight</option>
                                            <option value='mainway'>Mainway</option>
                                            <option value='malaysia-post'>Malaysia Post EMS / Pos Laju</option>
                                            <option value='malaysia-post-posdaftar'>Malaysia Post - Registered</option>
                                            <option value='mara-xpress'>Mara Xpress</option>
                                            <option value='matdespatch'>Matdespatch</option>
                                            <option value='matkahuolto'>Matkahuolto</option>
                                            <option value='mazet'>Groupe Mazet</option>
                                            <option value='megasave'>Megasave</option>
                                            <option value='mexico-redpack'>Mexico Redpack</option>
                                            <option value='mexico-senda-express'>Mexico Senda Express</option>
                                            <option value='mglobal'>PT MGLOBAL LOGISTICS INDONESIA</option>
                                            <option value='midland'>Midland</option>
                                            <option value='mikropakket'>Mikropakket</option>
                                            <option value='milkman'>Milkman</option>
                                            <option value='mondialrelay'>Mondial Relay</option>
                                            <option value='mrw-spain'>MRW</option>
                                            <option value='mx-cargo'>M&X cargo</option>
                                            <option value='mxe'>MXE Express</option>
                                            <option value='myhermes-uk'>myHermes UK</option>
                                            <option value='mypostonline'>Mypostonline</option>
                                            <option value='nacex'>NACEX</option>
                                            <option value='nacex-spain'>NACEX Spain</option>
                                            <option value='nanjingwoyuan'>Nanjing Woyuan</option>
                                            <option value='naqel-express'>Naqel Express</option>
                                            <option value='national-sameday'>National Sameday</option>
                                            <option value='nationwide-my'>Nationwide Express</option>
                                            <option value='new-zealand-post'>New Zealand Post</option>
                                            <option value='neway'>Neway Transport</option>
                                            <option value='newgistics'>Newgistics</option>
                                            <option value='newzealand-couriers'>NEW ZEALAND COURIERS</option>
                                            <option value='nhans-solutions'>Nhans Solutions</option>
                                            <option value='nightline'>Nightline</option>
                                            <option value='nim-express'>Nim Express</option>
                                            <option value='ninjavan'>Ninja Van</option>
                                            <option value='ninjavan-id'>Ninja Van Indonesia</option>
                                            <option value='ninjavan-my'>Ninja Van Malaysia</option>
                                            <option value='ninjavan-philippines'>Ninja Van Philippines</option>
                                            <option value='ninjavan-thai'>Ninja Van Thailand</option>
                                            <option value='nipost'>NiPost</option>
                                            <option value='norsk-global'>Norsk Global</option>
                                            <option value='nova-poshta'>Nova Poshta</option>
                                            <option value='nova-poshtaint'>Nova Poshta (International)</option>
                                            <option value='nowlog-api'>NOWLOG LOGISTICA INTELIGENTE LTD</option>
                                            <option value='ntl'>NTL logistics</option>
                                            <option value='oca-ar'>OCA Argentina</option>
                                            <option value='ocs'>OCS ANA Group</option>
                                            <option value='ocs-worldwide'>OCS WORLDWIDE</option>
                                            <option value='okayparcel'>OkayParcel</option>
                                            <option value='old-dominion'>Old Dominion Freight Line</option>
                                            <option value='omniparcel'>Omni Parcel</option>
                                            <option value='omniva'>Omniva</option>
                                            <option value='oneworldexpress'>One World Express</option>
                                            <option value='ontrac'>OnTrac</option>
                                            <option value='opek'>FedEx Poland Domestic</option>
                                            <option value='osm-worldwide'>OSM Worldwide</option>
                                            <option value='paack-webhook'>Paack</option>
                                            <option value='packlink'>Packlink</option>
                                            <option value='palexpress'>PAL Express Limited</option>
                                            <option value='palletways'>Palletways</option>
                                            <option value='pandulogistics'>Pandu Logistics</option>
                                            <option value='panther'>Panther</option>
                                            <option value='panther-order-number'>Panther Order Number</option>
                                            <option value='panther-reference'>Panther Reference</option>
                                            <option value='paper-express'>Paper Express</option>
                                            <option value='paperfly'>Paperfly Private Limited</option>
                                            <option value='paquetexpress'>Paquetexpress</option>
                                            <option value='parcel-force'>Parcel Force</option>
                                            <option value='parcel2go'>Parcel2Go</option>
                                            <option value='parcelled-in'>Parcelled.in</option>
                                            <option value='parcelpoint'>ParcelPoint Pty Ltd</option>
                                            <option value='parcelpost-sg'>Parcel Post Singapore</option>
                                            <option value='parknparcel'>Park N Parcel</option>
                                            <option value='pfcexpress'>PFC Express</option>
                                            <option value='pickup'>Pickupp</option>
                                            <option value='pickupp-mys'>PICK UPP</option>
                                            <option value='pickupp-sgp'>PICK UPP</option>
                                            <option value='pickupp-vnm'>Pickupp Vietnam</option>
                                            <option value='pil-logistics'>PIL Logistics (China) Co.</option>
                                            <option value='pilot-freight'>Pilot Freight Services</option>
                                            <option value='pioneer-logistics'>Pioneer Logistics Systems</option>
                                            <option value='pitney-bowes'>Pitney Bowes</option>
                                            <option value='pittohio'>PITT OHIO</option>
                                            <option value='pixsell'>PIXSELL LOGISTICS</option>
                                            <option value='planzer'>Planzer Group</option>
                                            <option value='poczta-polska'>Poczta Polska</option>
                                            <option value='pony-express'>Pony express</option>
                                            <option value='portugal-ctt'>Portugal CTT</option>
                                            <option value='portugal-seur'>Portugal Seur</option>
                                            <option value='pos-indonesia'>Pos Indonesia Domestic</option>
                                            <option value='pos-indonesia-int'>Pos Indonesia International</option>
                                            <option value='post-serbia'>Post Serbia</option>
                                            <option value='post-slovenia'>Post of Slovenia</option>
                                            <option value='post56'>Post56</option>
                                            <option value='posta-romana'>Poșta Română</option>
                                            <option value='poste-italiane'>Poste Italiane</option>
                                            <option value='posten-norge'>Posten Norge / Bring</option>
                                            <option value='posti'>Posti</option>
                                            <option value='postnl'>PostNL Domestic</option>
                                            <option value='postnl-3s'>PostNL International 3S</option>
                                            <option value='postnl-international'>PostNL International</option>
                                            <option value='postnord'>PostNord Logistics</option>
                                            <option value='postur-is'>Iceland Post</option>
                                            <option value='ppbyb'>PayPal Package</option>
                                            <option value='professional-couriers'>Professional Couriers</option>
                                            <option value='ptt-posta'>PTT Posta</option>
                                            <option value='purolator'>Purolator</option>
                                            <option value='purolator-international'>Purolator International</option>
                                            <option value='qualitypost'>QualityPost</option>
                                            <option value='quantium'>Quantium</option>
                                            <option value='qxpress'>Qxpress</option>
                                            <option value='raben-group'>Raben Group</option>
                                            <option value='raf'>RAF Philippines</option>
                                            <option value='raiderex'>RaidereX</option>
                                            <option value='ramgroup-za'>RAM</option>
                                            <option value='rcl'>Red Carpet Logistics</option>
                                            <option value='redur-es'>Redur Spain</option>
                                            <option value='relaiscolis'>Relais Colis</option>
                                            <option value='rincos'>Rincos</option>
                                            <option value='rl-carriers'>RL Carriers</option>
                                            <option value='roadbull'>Roadbull Logistics</option>
                                            <option value='rocketparcel'>Rocket Parcel International</option>
                                            <option value='rpd2man'>RPD2man Deliveries</option>
                                            <option value='rpx'>RPX Indonesia</option>
                                            <option value='rpxonline'>RPX Online</option>
                                            <option value='rrdonnelley'>RRD International Logistics U.S.A</option>
                                            <option value='russian-post'>Russian Post</option>
                                            <option value='ruston'>Ruston</option>
                                            <option value='rzyexpress'>RZY Express</option>
                                            <option value='safexpress'>Safexpress</option>
                                            <option value='sagawa'>Sagawa</option>
                                            <option value='saia-freight'>Saia LTL Freight</option>
                                            <option value='sailpost'>SAILPOST</option>
                                            <option value='sap-express'>SAP EXPRESS</option>
                                            <option value='sapo'>South African Post Office</option>
                                            <option value='saudi-post'>Saudi Post</option>
                                            <option value='scudex-express'>Scudex Express</option>
                                            <option value='sefl'>Southeastern Freight Lines</option>
                                            <option value='seino'>Seino</option>
                                            <option value='seko-sftp'>SEKO Worldwide</option>
                                            <option value='sekologistics'>SEKO Logistics</option>
                                            <option value='sending'>Sending Transporte Urgente y Comunicacion</option>
                                            <option value='sendit'>Sendit</option>
                                            <option value='sendle'>Sendle</option>
                                            <option value='sf-express'>S.F. Express</option>
                                            <option value='sf-express-webhook'>SF Express (Webhook)</option>
                                            <option value='sfb2c'>S.F International</option>
                                            <option value='sfcservice'>SFC Service</option>
                                            <option value='sfplus-webhook'>SF Plus</option>
                                            <option value='sgt-it'>SGT Corriere Espresso</option>
                                            <option value='shippify'>Shippify</option>
                                            <option value='shippit'>Shippit</option>
                                            <option value='shiptor'>Shiptor</option>
                                            <option value='shopfans'>ShopfansRU LLC</option>
                                            <option value='shree-maruti'>Shree Maruti Courier Services Pvt Ltd</option>
                                            <option value='shreetirupati'>SHREE TIRUPATI COURIER SERVICES PVT. LTD.</option>
                                            <option value='sic-teliway'>Teliway SIC Express</option>
                                            <option value='simplypost'>J & T Express Singapore</option>
                                            <option value='singapore-post'>Singapore Post</option>
                                            <option value='singapore-speedpost'>Singapore Speedpost</option>
                                            <option value='siodemka'>Siodemka</option>
                                            <option value='sk-posta'>Slovenská pošta</option>
                                            <option value='sky-postal'>SkyPostal</option>
                                            <option value='skybox'>SKYBOX</option>
                                            <option value='skynet'>SkyNet Malaysia</option>
                                            <option value='skynet-za'>Skynet World Wide Express South Africa</option>
                                            <option value='skynetworldwide'>SkyNet Worldwide Express</option>
                                            <option value='skynetworldwide-uae'>SkyNet Worldwide Express UAE</option>
                                            <option value='skynetworldwide-uk'>Skynet Worldwide Express UK</option>
                                            <option value='skypostal'>Asendia HK – Premium Service (LATAM)</option>
                                            <option value='smg-express'>SMG Direct</option>
                                            <option value='smooth'>Smooth Couriers</option>
                                            <option value='smsa-express'>SMSA Express</option>
                                            <option value='spain-correos-es'>Correos de España</option>
                                            <option value='spanish-seur'>Spanish Seur</option>
                                            <option value='spanish-seur-api'>Spanish Seur API</option>
                                            <option value='specialisedfreight-za'>Specialised Freight</option>
                                            <option value='speedcouriers-gr'>Speed Couriers</option>
                                            <option value='speedee'>Spee-Dee Delivery</option>
                                            <option value='speedexcourier'>Speedex Courier</option>
                                            <option value='speedy'>Speedy</option>
                                            <option value='spoton'>SPOTON Logistics Pvt Ltd</option>
                                            <option value='spring-gds'>Spring GDS</option>
                                            <option value='sprint-pack'>SPRINT PACK</option>
                                            <option value='srekorea'>SRE Korea</option>
                                            <option value='star-track'>StarTrack</option>
                                            <option value='star-track-courier'>Star Track Courier</option>
                                            <option value='star-track-express'>Star Track Express</option>
                                            <option value='sto'>STO Express</option>
                                            <option value='sutton'>Sutton Transport</option>
                                            <option value='sweden-posten'>PostNord Sweden</option>
                                            <option value='swiss-post'>Swiss Post</option>
                                            <option value='sypost'>Sunyou Post</option>
                                            <option value='szdpex'>DPEX China</option>
                                            <option value='taiwan-post'>Taiwan Post</option>
                                            <option value='taqbin-hk'>TAQBIN Hong Kong</option>
                                            <option value='taqbin-jp'>Yamato Japan</option>
                                            <option value='taqbin-my'>TAQBIN Malaysia</option>
                                            <option value='taqbin-sg'>TAQBIN Singapore</option>
                                            <option value='taxydromiki'>Geniki Taxydromiki</option>
                                            <option value='tck-express'>TCK Express</option>
                                            <option value='tcs'>TCS</option>
                                            <option value='tfm'>TFM Xpress</option>
                                            <option value='tforce-finalmile'>TForce Final Mile</option>
                                            <option value='tgx'>Kerry Express Hong Kong</option>
                                            <option value='thailand-post'>Thailand Thai Post</option>
                                            <option value='thecourierguy'>The Courier Guy</option>
                                            <option value='tiki'>Tiki</option>
                                            <option value='tipsa'>TIPSA</option>
                                            <option value='tnt'>TNT</option>
                                            <option value='tnt-au'>TNT Australia</option>
                                            <option value='tnt-click'>TNT-Click Italy</option>
                                            <option value='tnt-fr'>TNT France</option>
                                            <option value='tnt-it'>TNT Italy</option>
                                            <option value='tnt-reference'>TNT Reference</option>
                                            <option value='tnt-uk'>TNT UK</option>
                                            <option value='tnt-uk-reference'>TNT UK Reference</option>
                                            <option value='tntpost-it'>Nexive (TNT Post Italy)</option>
                                            <option value='toll-ipec'>Toll IPEC</option>
                                            <option value='toll-nz'>Toll New Zealand</option>
                                            <option value='toll-priority'>Toll Priority</option>
                                            <option value='tolos'>Tolos</option>
                                            <option value='tophatterexpress'>Tophatter Express</option>
                                            <option value='total-express'>Total Express</option>
                                            <option value='tourline'>tourline</option>
                                            <option value='tourline-reference'>Tourline Express</option>
                                            <option value='trakpak'>TrakPak</option>
                                            <option value='trans-kargo'>Trans Kargo Internasional</option>
                                            <option value='transmission-nl'>TransMission</option>
                                            <option value='trunkrs-webhook'>Trunkrs</option>
                                            <option value='tuffnells'>Tuffnells Parcels Express</option>
                                            <option value='tuffnells-reference'>Tuffnells Parcels Express- Reference</option>
                                            <option value='ubi-logistics'>UBI Smart Parcel</option>
                                            <option value='uds'>United Delivery Service</option>
                                            <option value='uk-mail'>UK Mail</option>
                                            <option value='ukrposhta'>UkrPoshta</option>
                                            <option value='up-express'>UP-express</option>
                                            <option value='ups'>UPS</option>
                                            <option value='ups-freight'>UPS Freight</option>
                                            <option value='ups-mi'>UPS Mail Innovations</option>
                                            <option value='urgent-cargus'>Urgent Cargus</option>
                                            <option value='usf-reddaway'>USF Reddaway</option>
                                            <option value='usps'>USPS</option>
                                            <option value='usps-webhook'>USPS Informed Visibility - Webhook</option>
                                            <option value='viettelpost'>ViettelPost</option>
                                            <option value='virtransport'>VIR Transport</option>
                                            <option value='viwo'>VIWO IoT</option>
                                            <option value='vnpost'>Vietnam Post</option>
                                            <option value='vnpost-ems'>Vietnam Post EMS</option>
                                            <option value='wahana'>Wahana</option>
                                            <option value='wanbexpress'>WanbExpress</option>
                                            <option value='watkins-shepard'>Watkins Shepard</option>
                                            <option value='weaship'>Weaship</option>
                                            <option value='wedo'>WeDo Logistics</option>
                                            <option value='wepost'>WePost Logistics</option>
                                            <option value='westbank-courier'>West Bank Courier</option>
                                            <option value='whistl'>Whistl</option>
                                            <option value='wise-express'>Wise Express</option>
                                            <option value='wiseloads'>Wiseloads</option>
                                            <option value='wishpost'>WishPost</option>
                                            <option value='wizmo'>Wizmo</option>
                                            <option value='wmg'>WMG Delivery</option>
                                            <option value='wndirect'>wnDirect</option>
                                            <option value='xdp-uk'>XDP Express</option>
                                            <option value='xdp-uk-reference'>XDP Express Reference</option>
                                            <option value='xend'>Xend Express</option>
                                            <option value='xl-express'>XL Express</option>
                                            <option value='xpedigo'>Xpedigo</option>
                                            <option value='xpert-delivery'>Xpert Delivery</option>
                                            <option value='xpo-logistics'>XPO logistics</option>
                                            <option value='xpost'>Xpost.ph</option>
                                            <option value='xpressbees'>XpressBees</option>
                                            <option value='xq-express'>XQ Express</option>
                                            <option value='yakit'>Yakit</option>
                                            <option value='yanwen'>Yanwen</option>
                                            <option value='ydh-express'>YDH express</option>
                                            <option value='yodel'>Yodel Domestic</option>
                                            <option value='yodel-international'>Yodel International</option>
                                            <option value='yrc'>YRC</option>
                                            <option value='yto'>YTO Express</option>
                                            <option value='yundaex'>Yunda Express</option>
                                            <option value='yunexpress'>Yun Express</option>
                                            <option value='yurtici-kargo'>Yurtici Kargo</option>
                                            <option value='zajil-express'>Zajil Express Company</option>
                                            <option value='zeleris'>Zeleris</option>
                                            <option value='zepto-express'>ZeptoExpress</option>
                                            <option value='zinc'>Zinc</option>
                                            <option value='zjs-express'>ZJS International</option>
                                            <option value='zto-express'>ZTO Express</option>
                                            <option value='zyllem'>Zyllem</option>
                                        </select>
                                    </div>
                                </div>



                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Code Postal Destination (si nécessaire)</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="cp_aftership" placeholder="Code Postal destination "/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Mot de passe (Ex NomExpediteur) (si nécessaire)</label>
                                    <div class="col-sm-3">
                                        <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="trackingkey_aftership" placeholder="mot de passe"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Auto creation dans aftership</label>
                                    <div class="col-sm-3">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autocreate" unchecked/></label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Auto suppression dans aftership</label>
                                    <div class="col-sm-3">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autodelete" unchecked/></label>
                                    </div>
                                </div>

                            </div>
                        </fieldset>
                    </form>
                </div>


                <div role="tabpanel" class="tab-pane" id="commandtab">
                    <br/><br/>
                    <a class="btn btn-success btn-sm cmdAction pull-left" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a>
                    <br/><br/>
                    <table id="table_cmd" class="table table-bordered table-condensed" style="word-break: break-all;">
                        <thead>
                        <tr>
                            <th>{{Nom}}</th>
                            <th>{{Type}}</th>
                            <th>{{Action}}</th>
                        </tr>
                        </thead>
                        <tbody>

                </div>
            </div>
        </div>
    </div>


<?php include_file('desktop', 'Suivreuncolis', 'js', 'Suivreuncolis');?>
<?php include_file('core', 'plugin.template', 'js');?>
