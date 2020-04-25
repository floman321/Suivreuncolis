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
            <label class="col-sm-4 control-label">Cle API AfterShip</label>
            <div class="col-sm-4">
                <input class="configKey form-control" data-l1key="api_aftership"/>
            </div>
        </div>

        <form class="form-horizontal">
            <fieldset>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Cle API LaPoste : (<a
                                href="https://developer.laposte.fr/products/suivi/latest">Lien vers formuluraire</a>)
                    </label>
                    <div class="col-sm-4">
                        <input class="configKey form-control" data-l1key="api_laposte"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label">A la création d'un colis, l' objet parent est : </label>
                    <div class="col-sm-4">

                        <select class="configKey form-control" data-l1key="objetpardefaut">
                            <option value="">Aucun</option>

                            <?php
                            $allObject = jeeObject::all(true);

                            foreach ($allObject as $object_li) {
                                echo '<option value="' . $object_li->getId() . '">' . $object_li->getHumanName(true) . '</option>';
                            }
                            ?>
                        </select>

                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-4 control-label">Notifier les changements par : </label>
                    <div class="col-sm-4">
                        <select class="configKey form-control" id="notifType" data-l1key="notificationpar">
                            <option value="">Aucun</option>
                            <option value="jeedom_msg">Message jeedom</option>
                            <option value="cmd">Commande jeedom Action</option>
                        </select>
                    </div>

                </div>
                <div id="notifConfigCmd" style="display : none;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{Action avec type message}}</label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input class="form-control configKey input-sm" data-l1key="cmd_notif"/>
                                <span class="input-group-btn">
                                  <a class="btn btn-default btn-sm listCmdInfo btn-warning" data-input="cmd_notif"><i class="fa fa-list-alt"></i></a>
                              </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="notifConfig" style="display : none;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{Format du message}} <sup><i class="fas fa-question-circle" title="{{Vous pouvez utiliser les tags #etat#, #msgtransporteur#, #dateheure#, #nom#, #lieu#, #commentaire# et #numcolis#}}"></i></sup></label>
                        <div class="col-sm-4">
                            <input class="form-control configKey input-sm" data-l1key="format_notif"/>

                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
        <script>

            $('#notifType').on('change', function () {
                if ($(this).value() == 'cmd') {
                    $('#notifConfigCmd').show();
                } else {
                    $('#notifConfigCmd').hide();
                }
                if ($(this).value() != '') {
                    $('#notifConfig').show();
                } else {
                    $('#notifConfig').hide();
                }
            });
            $("#notifConfigCmd").delegate(".listCmdInfo", 'click', function () {
                var el = $('.configKey[data-l1key=' + $(this).attr('data-input') + ']');
                jeedom.cmd.getSelectModal({cmd: {type: 'action', subtype: 'message'}}, function (result) {
                    el.atCaret('insert', result.human);
                });
            });
        </script>
