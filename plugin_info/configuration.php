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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

?>

<form class="form-horizontal">
<fieldset>
<div class="form-group">
<label class="col-lg-4 control-label">Cle API AfterShip</label>
<div class="col-lg-2">
<input class="configKey form-control" data-l1key="api_aftership" />
</div>
</div>
  
<div class="form-group">
<label class="col-lg-4 control-label">A la création d'un colis, l' objet parent est : </label>
<div class="col-lg-2">
    
  <select class="configKey form-control" data-l1key="objetpardefaut">
  	<option value="">Aucun</option>
  
  <?php
    $allObject = object::all(true);

    foreach ($allObject as $object_li) {
       echo '<option value="'.$object_li->getId().'">'. $object_li->getHumanName(true) . '</option>';
    }
  ?>
     </select>
  
</div>
</div>

    
<div class="form-group">
<label class="col-lg-4 control-label">Notifier les changements par : </label>
<div class="col-lg-2">
<select class="configKey form-control" data-l1key="notificationpar">
					<option value="">Aucun</option>
                    <option value="jeedom_msg">Message jeedom</option>
                    <option value="cmd">Commande jeedom Action  </option>
  </select>
  <br> 
  <p>ID commande Action avec type message :</p>
  <input class="configKey form-control" data-l1key="cmd_notif" placeholder="N° de commande jeedom"/>
</div>




</div>


  
</fieldset>



</form>

<h3>Importations Automatiques des colis chez AfterShip</h3>
<p>
Pour l'instant il faut mettre dans le moteur de taches manuellement (menu roue crantées en à droite)<br>
Bouton Ajouter puis<br>
Classe : Suivreuncolis<br>
Fonction : importAfterShip<br>
TimeOut : 5<br>
Cron : Une fois par Jour donc : 00 00 * * * *<br>
</p>
