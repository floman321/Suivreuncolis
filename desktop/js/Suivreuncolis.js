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


$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
});

function printEqLogic(_eqLogic) {
    if (!isset(_eqLogic)) {
        var _eqLogic = {configuration: {}};
    }
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {};
    }
    if (_eqLogic.logicalId == 'list') {
        $('#colis').hide();
        $('#aftership').hide();
    } else {
        $('#colis').show();
        if($('.eqLogicAttr[data-l1key=configuration][data-l2key=transporteur]').value()=='aftership'){
            $('#aftership').show();
        }
    }
}
var transp = "";

function transporteurchange(this_select) {
    transp = this_select.value;
    if (this_select.value == "aftership") {
        $('#aftership').show();
    }
    else {
        $('#aftership').hide();
    }
}

function rechercher(api_aftership) {
    if (transp == "aftership") {
        $.ajax({
            type: 'POST',
            headers: {'aftership-api-key':api_aftership},
            url: 'https://api.aftership.com/v4/couriers/detect',
            dataType: 'json',
            data: '{"tracking":{"tracking_number": "' + document.getElementById('numcolis').value + '"}}',
            success: function (resultat, statut) {

                var cuisines = resultat.data.couriers;
                var sel = document.getElementById('ListeTransporteurs');
                //$("#ListeTransporteurs").empty();
                for (var i = 0; i < cuisines.length; i++) {
                    var opt = document.createElement('option');
                    opt.innerHTML = cuisines[i].name;
                    opt.value = cuisines[i].slug;
                    if (i == 0) {
                        opt.selected = true;
                    }
                    sel.insertBefore(opt, sel[0]);
                }
            }
        });
    }
}
