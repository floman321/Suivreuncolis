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

function Suivreuncolis_install()
{

}

function Suivreuncolis_update()
{
    /*
        Code temporaire, à la mise à jour du plugin,
        je place les logicalId afin de pouvoir rechercher les commandes facilement.
        Je met également le widget sur le codeetat
    */
    foreach (self::byType('Suivreuncolis') as $eqLogic) {
        $cmds = $eqLogic->getCmd();
        foreach ($cmds as $cmd) {
            if ($cmd->getName() == 'codeetat') {
                $cmd->setLogicalId('codeetat');
                $cmd->setSubType('string');
                $cmd->setTemplate('dashboard', 'Suivreuncolis::codeetat');
                $cmd->setTemplate('mobile', 'Suivreuncolis::codeetat');
                $cmd->setOrder(1);
            } else if ($cmd->getName() == 'etat') {
                $cmd->setLogicalId('etat');
                $cmd->setOrder(2);
            } else if ($cmd->getName() == 'msgtransporteur') {
                $cmd->setLogicalId('msgtransporteur');
                $cmd->setOrder(3);
            } else if ($cmd->getName() == 'lieu') {
                $cmd->setLogicalId('lieu');
                $cmd->setOrder(4);
            } else if ($cmd->getName() == 'moncommentaire') {
                $cmd->setLogicalId('moncommentaire');
                $cmd->setOrder(5);
            } else if ($cmd->getName() == 'dateheure') {
                $cmd->setLogicalId('dateheure');
                $cmd->setOrder(6);
            }

            $cmd->setDisplay('showNameOndashboard', 0);
            $cmd->setDisplay('showNameOnmobile', 0);
            $cmd->setDisplay('showIconAndNamedashboard', 0);
            $cmd->setDisplay('showIconAndNamemobile', 0);
            $cmd->save();
        }
    }
}


function Suivreuncolis_remove()
{

}

?>
