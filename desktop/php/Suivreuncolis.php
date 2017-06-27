<?php
    if (!isConnect('admin')) {
        throw new Exception('{{401 - Accès non autorisé}}');
    }
    sendVarToJS('eqType', 'Suivreuncolis');
    $eqLogics = eqLogic::byType('Suivreuncolis');
    ?>

<div class="row row-overflow">
<div class="col-lg-2 col-md-3 col-sm-4">
<div class="bs-sidebar">
<ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
<a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un Suivi}}</a>
<li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
<?php
    foreach ($eqLogics as $eqLogic) {
        echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
    }
    ?>
</ul>
</div>
</div>
<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
<legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
<div class="eqLogicThumbnailContainer">
<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
<center>
<i class="fa fa-plus-circle" style="font-size : 5em;color:#00979c;"></i>
</center>
<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#00979c"><center>Ajouter</center></span>
</div>

<div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 140px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
<center>
<i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
</center>
<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
</div>
</div>
</div>
<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
<legend><i class="icon divers-mailbox15"></i> Mes Suivis</legend>

<div class="eqLogicThumbnailContainer">
<?php
    foreach ($eqLogics as $eqLogic) {
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
        echo "<center>";
        echo '<img src="plugins/Suivreuncolis/doc/images/Suivreuncolis_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
    }
    ?>
</div>
</div>
<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
<a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
<a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
<ul class="nav nav-tabs" role="tablist">
<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
</ul>
<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
<div role="tabpanel" class="tab-pane active" id="eqlogictab">
<br/>

<form class="form-horizontal">
<fieldset>
<div class="form-group">
<label class="col-sm-3 control-label">{{Nom du colis}}</label>
<div class="col-sm-3">
<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du colis}}"/>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label" >{{Objet parent}}</label>
<div class="col-sm-3">
<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
<option value="">{{Aucun}}</option>
<?php
    foreach (object::all() as $object) {
        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
    }
    ?>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label" >{{Activer}}</label>
<div class="col-sm-9">
<input type="checkbox" class="eqLogicAttr" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
<input type="checkbox" class="eqLogicAttr" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
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
	<option value='sky56'>Sky56</option>
	<option value='aliexpress'>AliExpress Shipping</option>
	<option value='17tracks'>17 Tracks</option>
</select>

</div>
</div>

<div id="aftership" style="display:none;">

<h3>AfterShip</h3>

<p>Vous devez avoir une cle API pour utiliser ce service (voir <a href='https://secure.aftership.com/apps/api'>https://secure.aftership.com/apps/api</a>)
                                                                                                                  
                                                          <div class="form-group">
                                                          <label class="col-sm-3 control-label">Transporteur AfterShip</label>
                                                          <div class="col-sm-3">
                                                          <select id="ListeTransporteurs" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="transaftership">
                                                          <option value='26390'>4-72 Entregando</option>
                                                          <option value='17postservice'>17 Post Service</option>
                                                          <option value='2go'>2GO</option>
                                                          <option value='360lion'>360 Lion Express</option>
                                                          <option value='4px'>4PX</option>
                                                          <option value='800bestex'>Best Express</option>
                                                          <option value='abf'>ABF Freight</option>
                                                          <option value='abxexpress-my'>ABX Express</option>
                                                          <option value='acscourier'>ACS Courier</option>
                                                          <option value='adicional'>Adicional Logistics</option>
                                                          <option value='adsone'>ADSOne</option>
                                                          <option value='aeroflash'>Mexico AeroFlash</option>
                                                          <option value='air21'>AIR21</option>
                                                          <option value='airpak-express'>Airpak Express</option>
                                                          <option value='airspeed'>Airspeed International Corporation</option>
                                                          <option value='ajexpress'>a j express</option>
                                                          <option value='alphafast'>alphaFAST</option>
                                                          <option value='an-post'>An Post</option>
                                                          <option value='apc'>APC Postal Logistics</option>
                                                          <option value='aramex'>Aramex</option>
                                                          <option value='arrowxl'>Arrow XL</option>
                                                          <option value='asendia-de'>Asendia Germany</option>
                                                          <option value='asendia-uk'>Asendia UK</option>
                                                          <option value='asendia-usa'>Asendia USA</option>
                                                          <option value='asm'>ASM</option>
                                                          <option value='aupost-china'>AuPost China</option>
                                                          <option value='australia-post'>Australia Post</option>
                                                          <option value='austrian-post'>Austrian Post (Express)</option>
                                                          <option value='austrian-post-registered'>Austrian Post (Registered)</option>
                                                          <option value='axl'>AXL Express & Logistics</option>
                                                          <option value='b2ceurope'>B2C Europe</option>
                                                          <option value='belpost'>Belpost</option>
                                                          <option value='bert-fr'>Bert Transport</option>
                                                          <option value='bgpost'>Bulgarian Posts</option>
                                                          <option value='bh-posta'>JP BH PoÅ¡ta</option>
                                                          <option value='bluedart'>Bluedart</option>
                                                          <option value='bondscouriers'>Bonds Couriers</option>
                                                          <option value='boxc'>BOXC</option>
                                                          <option value='bpost'>Belgium Post</option>
                                                          <option value='bpost-international'>bpost international</option>
                                                          <option value='brazil-correios'>Brazil Correios</option>
                                                          <option value='brt-it'>BRT Bartolini</option>
                                                          <option value='brt-it-parcelid'>BRT Bartolini(Parcel ID)</option>
                                                          <option value='buylogic'>Buylogic</option>
                                                          <option value='cambodia-post'>Cambodia Post</option>
                                                          <option value='canada-post'>Canada Post</option>
                                                          <option value='canpar'>Canpar Courier</option>
                                                          <option value='cbl-logistica'>CBL Logistics</option>
                                                          <option value='ceska-posta'>ÄŒeskÃ¡ PoÅ¡ta</option>
                                                          <option value='china-ems'>China EMS</option>
                                                          <option value='china-post'>China Post</option>
                                                          <option value='chronopost-france'>Chronopost France</option>
                                                          <option value='chronopost-portugal'>Chronopost Portugal</option>
                                                          <option value='citylinkexpress'>City-Link Express</option>
                                                          <option value='cj-gls'>CJ GLS</option>
                                                          <option value='cnexps'>CNE Express</option>
                                                          <option value='colis-prive'>Colis PrivÃ©</option>
                                                          <option value='colissimo'>Colissimo</option>
                                                          <option value='collectplus'>Collect+</option>
                                                          <option value='con-way'>Con-way Freight</option>
                                                          <option value='correo-argentino'>Correo Argentino</option>
                                                          <option value='correos-chile'>Correos Chile</option>
                                                          <option value='correos-de-mexico'>Correos de Mexico</option>
                                                          <option value='correosexpress'>Correos Express</option>
                                                          <option value='costmeticsnow'>Cosmetics Now</option>
                                                          <option value='courex'>Courex</option>
                                                          <option value='courierit'>Courier IT</option>
                                                          <option value='courier-plus'>Courier Plus</option>
                                                          <option value='courierpost'>CourierPost</option>
                                                          <option value='couriers-please'>Couriers Please</option>
                                                          <option value='cpacket'>cPacket</option>
                                                          <option value='cuckooexpress'>Cuckoo Express</option>
                                                          <option value='cyprus-post'>Cyprus Post</option>
                                                          <option value='danmark-post'>Post Danmark</option>
                                                          <option value='dawnwing'>Dawn Wing</option>
                                                          <option value='dbschenker-se'>DB Schenker Sweden</option>
                                                          <option value='delcart-in'>Delcart</option>
                                                          <option value='delhivery'>Delhivery</option>
                                                          <option value='deltec-courier'>Deltec Courier</option>
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
                                                          <option value='dhl-global-mail'>DHL eCommerce</option>
                                                          <option value='dhl-global-mail-asia'>DHL Global Mail Asia</option>
                                                          <option value='dhl-hk'>DHL Hong Kong</option>
                                                          <option value='dhl-nl'>DHL Netherlands</option>
                                                          <option value='dhlparcel-nl'>DHL Parcel NL</option>
                                                          <option value='dhl-pieceid'>DHL Express (Piece ID)</option>
                                                          <option value='dhl-poland'>DHL Poland Domestic</option>
                                                          <option value='directfreight-au'>Direct Freight Express</option>
                                                          <option value='directlink'>Direct Link</option>
                                                          <option value='directlog'>Directlog</option>
                                                          <option value='dmm-network'>DMM Network</option>
                                                          <option value='dotzot'>Dotzot</option>
                                                          <option value='dpd'>DPD</option>
                                                          <option value='dpd-de'>DPD Germany</option>
                                                          <option value='dpd-ireland'>DPD Ireland</option>
                                                          <option value='dpd-poland'>DPD Poland</option>
                                                          <option value='dpd-uk'>DPD UK</option>
                                                          <option value='dpe-express'>DPE Express</option>
                                                          <option value='dpex'>DPEX</option>
                                                          <option value='dpe-za'>DPE South Africa</option>
                                                          <option value='dsv'>DSV</option>
                                                          <option value='dtdc'>DTDC India</option>
                                                          <option value='dtdc-au'>DTDC Australia</option>
                                                          <option value='dynamic-logistics'>Dynamic Logistics</option>
                                                          <option value='easy-mail'>Easy Mail</option>
                                                          <option value='ecargo-asia'>Ecargo</option>
                                                          <option value='ec-firstclass'>EC-Firstclass</option>
                                                          <option value='echo'>Echo</option>
                                                          <option value='ecom-express'>Ecom Express</option>
                                                          <option value='ekart'>Ekart</option>
                                                          <option value='elta-courier'>ELTA Hellenic Post</option>
                                                          <option value='emirates-post'>Emirates Post</option>
                                                          <option value='empsexpress'>EMPS Express</option>
                                                          <option value='ensenda'>Ensenda</option>
                                                          <option value='envialia'>Envialia</option>
                                                          <option value='eparcel-kr'>eParcel Korea</option>
                                                          <option value='equick-cn'>Equick China</option>
                                                          <option value='estafeta'>Estafeta</option>
                                                          <option value='estes'>Estes</option>
                                                          <option value='exapaq'>Exapaq</option>
                                                          <option value='expeditors'>Expeditors</option>
                                                          <option value='fastrak-th'>Fastrak Services</option>
                                                          <option value='fastway-au'>Fastway Australia</option>
                                                          <option value='fastway-ireland'>Fastway Ireland</option>
                                                          <option value='fastway-nz'>Fastway New Zealand</option>
                                                          <option value='fastway-za'>Fastway South Africa</option>
                                                          <option value='fedex'>FedEx</option>
                                                          <option value='fedex-freight'>FedEx Freight</option>
                                                          <option value='fedex-uk'>FedEx UK</option>
                                                          <option value='fercam'>FERCAM Logistics & Transport</option>
                                                          <option value='first-flight'>First Flight Couriers</option>
                                                          <option value='first-logistics'>First Logistics</option>
                                                          <option value='flytexpress'>Flyt Express</option>
                                                          <option value='gati-kwe'>Gati-KWE</option>
                                                          <option value='gdex'>GDEX</option>
                                                          <option value='geodis-calberson-fr'>GEODIS - Distribution & Express</option>
                                                          <option value='geodis-espace'>Geodis E-space</option>
                                                          <option value='ghn'>Giao hÃ ng nhanh</option>
                                                          <option value='globegistics'>Globegistics Inc.</option>
                                                          <option value='gls'>GLS</option>
                                                          <option value='gls-italy'>GLS Italy</option>
                                                          <option value='gls-netherlands'>GLS Netherlands</option>
                                                          <option value='gofly'>GoFly</option>
                                                          <option value='gojavas'>GoJavas</option>
                                                          <option value='greyhound'>Greyhound</option>
                                                          <option value='hermes'>Hermesworld</option>
                                                          <option value='hermes-de'>Hermes Germany</option>
                                                          <option value='hh-exp'>Hua Han Logistics</option>
                                                          <option value='homedirect-logistics'>Homedirect Logistics</option>
                                                          <option value='hong-kong-post'>Hong Kong Post</option>
                                                          <option value='hrvatska-posta'>Hrvatska PoÅ¡ta</option>
                                                          <option value='hunter-express'>Hunter Express</option>
                                                          <option value='idexpress'>IDEX</option>
                                                          <option value='imexglobalsolutions'>IMEX Global Solutions</option>
                                                          <option value='imxmail'>IMX Mail</option>
                                                          <option value='india-post'>India Post Domestic</option>
                                                          <option value='india-post-int'>India Post International</option>
                                                          <option value='inpost-paczkomaty'>InPost Paczkomaty</option>
                                                          <option value='interlink-express'>Interlink Express</option>
                                                          <option value='interlink-express-reference'>Interlink Express Reference</option>
                                                          <option value='international-seur'>International Seur</option>
                                                          <option value='i-parcel'>i-parcel</option>
                                                          <option value='israel-post'>Israel Post</option>
                                                          <option value='israel-post-domestic'>Israel Post Domestic</option>
                                                          <option value='italy-sda'>Italy SDA</option>
                                                          <option value='itis'>ITIS International</option>
                                                          <option value='jam-express'>Jam Express</option>
                                                          <option value='japan-post'>Japan Post</option>
                                                          <option value='jayonexpress'>Jayon Express (JEX)</option>
                                                          <option value='jcex'>JCEX</option>
                                                          <option value='jersey-post'>Jersey Post</option>
                                                          <option value='jet-ship'>Jet-Ship Worldwide</option>
                                                          <option value='jne'>JNE</option>
                                                          <option value='jocom'>Jocom</option>
                                                          <option value='kangaroo-my'>Kangaroo Worldwide Express</option>
                                                          <option value='kerry-logistics'>Kerry Express Thailand</option>
                                                          <option value='kerryttc-vn'>Kerry TTC Express</option>
                                                          <option value='kgmhub'>KGM Hub</option>
                                                          <option value='kn'>Kuehne + Nagel</option>
                                                          <option value='korea-post'>Korea Post</option>
                                                          <option value='landmark-global'>Landmark Global</option>
                                                          <option value='lao-post'>Lao Post</option>
                                                          <option value='la-poste-colissimo'>La Poste</option>
                                                          <option value='lasership'>LaserShip</option>
                                                          <option value='lbcexpress'>LBC Express</option>
                                                          <option value='lietuvos-pastas'>Lietuvos PaÅ¡tas</option>
                                                          <option value='lion-parcel'>Lion Parcel</option>
                                                          <option value='lwe-hk'>Logistic Worldwide Express</option>
                                                          <option value='magyar-posta'>Magyar Posta</option>
                                                          <option value='malaysia-post'>Malaysia Post EMS / Pos Laju</option>
                                                          <option value='malaysia-post-posdaftar'>Malaysia Post - Registered</option>
                                                          <option value='matdespatch'>Matdespatch</option>
                                                          <option value='matkahuolto'>Matkahuolto</option>
                                                          <option value='maxcellents'>Maxcellents Pte Ltd</option>
                                                          <option value='mexico-redpack'>Mexico Redpack</option>
                                                          <option value='mexico-senda-express'>Mexico Senda Express</option>
                                                          <option value='mondialrelay'>Mondial Relay</option>
                                                          <option value='mrw-spain'>MRW</option>
                                                          <option value='myhermes-uk'>myHermes UK</option>
                                                          <option value='mypostonline'>Mypostonline</option>
                                                          <option value='nacex-spain'>NACEX Spain</option>
                                                          <option value='nanjingwoyuan'>Nanjing Woyuan</option>
                                                          <option value='nationwide-my'>Nationwide Express</option>
                                                          <option value='newgistics'>Newgistics</option>
                                                          <option value='new-zealand-post'>New Zealand Post</option>
                                                          <option value='nhans-solutions'>Nhans Solutions</option>
                                                          <option value='nightline'>Nightline</option>
                                                          <option value='ninjavan'>Ninja Van</option>
                                                          <option value='ninjavan-id'>Ninja Van Indonesia</option>
                                                          <option value='ninjavan-my'>Ninja Van Malaysia</option>
                                                          <option value='nipost'>NiPost</option>
                                                          <option value='norsk-global'>Norsk Global</option>
                                                          <option value='nova-poshta'>Nova Poshta</option>
                                                          <option value='nuvoex'>NuvoEx</option>
                                                          <option value='oca-ar'>OCA Argentina</option>
                                                          <option value='old-dominion'>Old Dominion Freight Line</option>
                                                          <option value='omniparcel'>Omni Parcel</option>
                                                          <option value='oneworldexpress'>One World Express</option>
                                                          <option value='ontrac'>OnTrac</option>
                                                          <option value='opek'>FedEx Poland Domestic</option>
                                                          <option value='packlink'>Packlink</option>
                                                          <option value='pandulogistics'>Pandu Logistics</option>
                                                          <option value='panther'>Panther</option>
                                                          <option value='parcel-express'>Parcel Express</option>
                                                          <option value='parcel-force'>Parcel Force</option>
                                                          <option value='parcelled-in'>Parcelled.in</option>
                                                          <option value='parcelpost-sg'>Parcel Post Singapore</option>
                                                          <option value='pfcexpress'>PFC Express</option>
                                                          <option value='poczta-polska'>Poczta Polska</option>
                                                          <option value='portugal-ctt'>Portugal CTT</option>
                                                          <option value='portugal-seur'>Portugal Seur</option>
                                                          <option value='pos-indonesia'>Pos Indonesia Domestic</option>
                                                          <option value='pos-indonesia-int'>Pos Indonesia Int'l</option>
                                                          <option value='post56'>Post56</option>
                                                          <option value='posta-romana'>PoÈ™ta RomÃ¢nÄƒ</option>
                                                          <option value='poste-italiane'>Poste Italiane</option>
                                                          <option value='poste-italiane-paccocelere'>Poste Italiane Paccocelere</option>
                                                          <option value='posten-norge'>Posten Norge / Bring</option>
                                                          <option value='posti'>Posti</option>
                                                          <option value='postnl'>PostNL Domestic</option>
                                                          <option value='postnl-3s'>PostNL International 3S</option>
                                                          <option value='postnl-international'>PostNL International</option>
                                                          <option value='postnord'>PostNord Logistics</option>
                                                          <option value='post-serbia'>Post Serbia</option>
                                                          <option value='postur-is'>Iceland Post</option>
                                                          <option value='ppbyb'>PayPal Package</option>
                                                          <option value='professional-couriers'>Professional Couriers</option>
                                                          <option value='ptt-posta'>PTT Posta</option>
                                                          <option value='purolator'>Purolator</option>
                                                          <option value='quantium'>Quantium</option>
                                                          <option value='qxpress'>Qxpress</option>
                                                          <option value='raben-group'>Raben Group</option>
                                                          <option value='raf'>RAF Philippines</option>
                                                          <option value='raiderex'>RaidereX</option>
                                                          <option value='ramgroup-za'>RAM</option>
                                                          <option value='red-express'>Red Express</option>
                                                          <option value='red-express-wb'>Red Express Waybill</option>
                                                          <option value='redur-es'>Redur Spain</option>
                                                          <option value='rl-carriers'>RL Carriers</option>
                                                          <option value='roadbull'>Roadbull Logistics</option>
                                                          <option value='rocketparcel'>Rocket Parcel International</option>
                                                          <option value='royal-mail'>Royal Mail</option>
                                                          <option value='rpx'>RPX Indonesia</option>
                                                          <option value='rpxonline'>RPX Online</option>
                                                          <option value='rrdonnelley'>RR Donnelley</option>
                                                          <option value='russian-post'>Russian Post</option>
                                                          <option value='rzyexpress'>RZY Express</option>
                                                          <option value='safexpress'>Safexpress</option>
                                                          <option value='sagawa'>Sagawa</option>
                                                          <option value='sapo'>South African Post Office</option>
                                                          <option value='saudi-post'>Saudi Post</option>
                                                          <option value='scudex-express'>Scudex Express</option>
                                                          <option value='sekologistics'>SEKO Logistics</option>
                                                          <option value='sendle'>Sendle</option>
                                                          <option value='sfb2c'>S.F International</option>
                                                          <option value='sfcservice'>SFC Service</option>
                                                          <option value='sf-express'>S.F. Express</option>
                                                          <option value='sgt-it'>SGT Corriere Espresso</option>
                                                          <option value='sic-teliway'>Teliway SIC Express</option>
                                                          <option value='simplypost'>SimplyPost</option>
                                                          <option value='singapore-post'>Singapore Post</option>
                                                          <option value='singapore-speedpost'>Singapore Speedpost</option>
                                                          <option value='siodemka'>Siodemka</option>
                                                          <option value='skynet'>SkyNet Malaysia</option>
                                                          <option value='skynetworldwide'>SkyNet Worldwide Express</option>
                                                          <option value='skynetworldwide-uk'>Skynet Worldwide Express UK</option>
                                                          <option value='skypostal'>Asendia HK (LATAM)</option>
                                                          <option value='smsa-express'>SMSA Express</option>
                                                          <option value='spain-correos-es'>Correos de EspaÃ±a</option>
                                                          <option value='spanish-seur'>Spanish Seur</option>
                                                          <option value='specialisedfreight-za'>Specialised Freight</option>
                                                          <option value='speedcouriers-gr'>Speed Couriers</option>
                                                          <option value='speedexcourier'>Speedex Courier</option>
                                                          <option value='spreadel'>Spreadel</option>
                                                          <option value='srekorea'>SRE Korea</option>
                                                          <option value='star-track'>StarTrack</option>
                                                          <option value='star-track-express'>Star Track Express</option>
                                                          <option value='sto'>STO Express</option>
                                                          <option value='sweden-posten'>Sweden Posten</option>
                                                          <option value='swiss-post'>Swiss Post</option>
                                                          <option value='szdpex'>DPEX China</option>
                                                          <option value='taiwan-post'>Taiwan Post</option>
                                                          <option value='taqbin-hk'>TAQBIN Hong Kong</option>
                                                          <option value='taqbin-jp'>Yamato Japan</option>
                                                          <option value='taqbin-my'>TAQBIN Malaysia</option>
                                                          <option value='taqbin-sg'>TAQBIN Singapore</option>
                                                          <option value='taxydromiki'>Geniki Taxydromiki</option>
                                                          <option value='tgx'>TGX</option>
                                                          <option value='thailand-post'>Thailand Thai Post</option>
                                                          <option value='thecourierguy'>The Courier Guy</option>
                                                          <option value='tiki'>Tiki</option>
                                                          <option value='tnt'>TNT</option>
                                                          <option value='tnt-au'>TNT Australia</option>
                                                          <option value='tnt-click'>TNT-Click Italy</option>
                                                          <option value='tnt-fr'>TNT France</option>
                                                          <option value='tnt-it'>TNT Italy</option>
                                                          <option value='tntpost-it'>Nexive (TNT Post Italy)</option>
                                                          <option value='tnt-reference'>TNT Reference</option>
                                                          <option value='tnt-uk'>TNT UK</option>
                                                          <option value='tnt-uk-reference'>TNT UK Reference</option>
                                                          <option value='toll-ipec'>Toll IPEC</option>
                                                          <option value='toll-priority'>Toll Priority</option>
                                                          <option value='trakpak'>TrakPak</option>
                                                          <option value='transmission-nl'>TransMission</option>
                                                          <option value='tuffnells'>Tuffnells Parcels Express</option>
                                                          <option value='ubi-logistics'>UBI Logistics Australia</option>
                                                          <option value='uk-mail'>UK Mail</option>
                                                          <option value='ukrposhta'>UkrPoshta</option>
                                                          <option value='ups'>UPS</option>
                                                          <option value='ups-freight'>UPS Freight</option>
                                                          <option value='ups-mi'>UPS Mail Innovations</option>
                                                          <option value='usps'>USPS</option>
                                                          <option value='viettelpost'>ViettelPost</option>
                                                          <option value='vnpost'>Vietnam Post</option>
                                                          <option value='vnpost-ems'>Vietnam Post EMS</option>
                                                          <option value='vtfe'>VicTas Freight Express</option>
                                                          <option value='wahana'>Wahana</option>
                                                          <option value='wedo'>WeDo Logistics</option>
                                                          <option value='wishpost'>WishPost</option>
                                                          <option value='xdp-uk'>XDP Express</option>
                                                          <option value='xdp-uk-reference'>XDP Express Reference</option>
                                                          <option value='xend'>Xend Express</option>
                                                          <option value='xl-express'>XL Express</option>
                                                          <option value='xpressbees'>XpressBees</option>
                                                          <option value='xq-express'>XQ Express</option>
                                                          <option value='yakit'>Yakit</option>
                                                          <option value='yanwen'>Yanwen</option>
                                                          <option value='yodel'>Yodel Domestic</option>
                                                          <option value='yodel-international'>Yodel International</option>
                                                          <option value='yrc'>YRC</option>
                                                          <option value='yto'>YTO Express</option>
                                                          <option value='yundaex'>Yunda Express</option>
                                                          <option value='yunexpress'>Yun Express</option>
                                                          <option value='zalora-7-eleven'>Zalora 7-Eleven</option>
                                                          <option value='zjs-express'>ZJS International</option>
                                                          <option value='zyllem'>Zyllem</option>
                                                          </select>
                                                          </div>
                                                          </div>
                                                          
                                                          
                                                          
                                                          <div class="form-group">
                                                          <label class="col-sm-3 control-label">Code Postal Destination AfterShip (si nécessaire)</label>
                                                          <div class="col-sm-3">
                                                          <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="cp_aftership" placeholder="Code Postal destination "/>
                                                          </div>
                                                          </div>

														  <div class="form-group">
														  <label class="col-sm-3 control-label">Auto creation dans aftership</label>
														  <div class="col-sm-3">
														  <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="autocreate" unchecked/>
														  </div>
														  </div>

														  <div class="form-group">
														  <label class="col-sm-3 control-label">Auto suppression dans aftership</label>
														  <div class="col-sm-3">
														  <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-l1key="configuration" data-l2key="autodelete" unchecked/>
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
