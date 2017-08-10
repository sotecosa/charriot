<?php

/* Copyright (C) 2015      Henry Serón            <hseron@shakingweb.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   \file       htdocs/production/process.php
 *   \brief      Production module
 *   \ingroup    production
 */
require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/html.formprocess.class.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/process.class.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/component.class.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/material.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

$help_url = 'EN:Module_Production|ES:Producción';

$langs->load('production');
//$langs->load('accountingex@accountingex');
//$langs->load('products');
//$langs->load('projects');




$form = new FormProcess($db);
$process = new ProductionProcess($db);
$component = new ProductionComponent($db);
$material = new ProductionMaterial($db);

$action = GETPOST('action', 'alpha', 3);
$id = GETPOST('id', 'int', 3);
$idline = GETPOST('lineid');
$confirm = GETPOST('confirm');


/**
 * Actions
 */
if ($action == 'deleteline') {

    $form = new Form($db);
    $text = $langs->trans('ConfirmDeleteComponent');
    $formquestion = array(
        array('type' => 'hidden', 'name' => 'id', 'label' => 'id', 'value' => $id),
        array('type' => 'hidden', 'name' => 'deletecomponent', 'label' => 'idline', 'value' => $idline)
    );

    echo $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans('ConfirmDeleteComponents'), $text, 'confirm_delete_component', $formquestion, "yes", 1);
}

//print(GETPOST('deletecomponent')) ;

if ($action == 'confirm_delete_component' && $confirm == 'yes') {
    $deletecomponent = GETPOST('deletecomponent');



    $result = $component->deleteline($id, $deletecomponent);


    if ($result == 0) {
        $result = $component->delete1($idine);
        if ($result == 0) {
            setEventMessage('DeleteComponent');
        } else {
            setEventMessage('ComponentError', 'errors');
        }
    } else {
        setEventMessage('ComponentError1', 'errors');
    }
}







// saving product
if ($action == 'add' || $action == 'edit') {
    $process->process_type = GETPOST('process_type');
    $process->status = GETPOST('status');
    $process->fk_product = GETPOST('fk_product');
    $process->planned_amount = GETPOST('planned_amount');
    $process->fk_entrepot = GETPOST('fk_entrepot');
    $process->date = GETPOST('dateyear') . '-' . GETPOST('datemonth') . '-' . GETPOST('dateday');
    $process->date_end = GETPOST('date_end') ? GETPOST('date_endyear') . '-' . GETPOST('date_endmonth') . '-' . GETPOST('date_endday') : '';
    $process->fk_user = GETPOST('fk_user');
    $process->fk_profit_center = GETPOST('fk_profit_center');
    $process->fk_project = GETPOST('fk_project');
    $process->ref = GETPOST('ref');

    $errors = 0;
    if (empty($process->process_type)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('ProcessType')), 'errors');
        $errors++;
    }

    if (empty($process->fk_product)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Material')), 'errors');
        $errors++;
    }

    if (empty($process->planned_amount)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('PlannedAmount')), 'errors');
        $errors++;
    }

    if ($errors == 0) {
        if ($action == 'add') {
            $result = $process->create();
//            $creatv = $process->create_valsal($id);
//            if ($creatv < 0) {
//                setEventMessage($process->error, 'Error vale salida');
//            } else {
//                setEventMessage($langs->trans("Vale de Salida ingresado"));
//                print '';
//            }
//            $valeig = $process->create_valing($id);
//            if ($valeig < 0) {
//                setEventMessage($process->error, 'Error vale ingreso');
//            } else {
//                setEventMessage($langs->trans("Vale de Entrada ingresado"));
//                print '';
//            }
        } elseif ($action == 'edit') {
            $process->real_component_cost = GETPOST('real_component_cost');
            $process->aditional_cost = GETPOST('aditional_cost');
            $process->real_product_cost = GETPOST('real_product_cost');
            $process->total_deviation = GETPOST('total_deviation');
            $process->deviation_percent = GETPOST('deviation_percent');
            $process->completed_amount = GETPOST('completed_amount');
            $process->rejected_amount = GETPOST('rejected_amount');
            $process->real_date_end = GETPOST('real_date_endyear') . '-' . GETPOST('real_date_endmonth') . '-' . GETPOST('real_date_endday');

            $result = $process->update($id);
            
        }

        if ($result >= 0) {
            setEventMessage($langs->trans("ProcessSaved"));
            if ($action == 'add') {
                $process->fetch($result);
                $action = 'update';
                $id = $result;
            } elseif ($action == 'edit') {
                $action = 'update';
            }
        } elseif ($result == -1) {
            setEventMessage($langs->trans('DuplicatedProcess'), 'errors');
        } elseif ($result == -3) {
            setEventMessage($langs->trans('Error'), 'errors');
        }
    } else {
        if ($action == 'add') {
            $action = 'create';
        } elseif ($action == 'edit') {
            $action = 'update';
        }
    }
} elseif ($action == 'component_add') {
    $component->fk_material = GETPOST('component_fk_material');
    $component->amount = GETPOST('component_amount');
    $component->required_amount = GETPOST('required_amount');
    $component->stock = GETPOST('stock');
    $component->fk_entrepot = GETPOST('component_fk_entrepot');
    $component->emission_method = GETPOST('emission_method');
    $component->comment = GETPOST('comment');

    $errors = 0;

    if ($process->status == 'C') { // if process is closed it can be added more components
        setEventMessage($langs->trans('ProcessIsClosed'), 'errors');
        $errors++;
    }

    if (empty($component->fk_material)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Material')), 'errors');
        $errors++;
    }

    if (empty($component->amount)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Quantity')), 'errors');
        $errors++;
    }

    if (empty($component->fk_entrepot)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Warehouse')), 'errors');
        $errors++;
    }

    if ($errors == 0) {
        $result = $component->create();
        if ($result > 0) {
            $process_component = new ProductionProcessComponent($db);

            $process_component->fk_process = $id;
            $process_component->fk_component = $result;

            $res = $process_component->create();

            if ($res == 0) {
                setEventMessage($langs->trans("ComponentSaved"));
            } elseif ($result == -1) {
                setEventMessage($langs->trans('ComponentDuplicated'), 'errors');
            } elseif ($result == -3) {
                setEventMessage($langs->trans('Error'), 'errors');
            }
        }
    }
}

if ($action == 'edit_line_component_material') {


    $component->fk_material = GETPOST('component_fk_material');
    $component->amount = GETPOST('component_amount');
    $component->required_amount = GETPOST('required_amount');
    $component->fk_entrepot = GETPOST('component_fk_entrepot1');
    $component->stock = GETPOST('stock');
    $component->comment = GETPOST('comment');

    $result = $component->edit_line_component1(GETPOST('lineid'));

    if ($result < 0) {

        setEventMessage($material->error, 'errors');
    }
    // header("Location:".DOL_URL_ROOT."/production/process.php?action=list");
}









//if($action == 'gen_sal'){
//
//  $creatv = $process->create_valsal($id);
//  
//  if ($creatv < 0) {
//        setEventMessage($process->error, 'Error vale salida');
//    }else{
//         setEventMessage($langs->trans("Vale de Salida ingresado"));
//         print ''; 
//    }
//        
//
//}
//





/* @var $listps type */


if($action == 'gen_ing'){
    $creatv = $process->create_valsal($id);
  
  if ($creatv < 0) {
        setEventMessage($process->error, 'Error vale salida');
    }else{
         setEventMessage($langs->trans("Vale de Salida ingresado"));
         print ''; 
    }
    
        $valeig = $process->create_valing($id);
     if ($valeig < 0) {
     setEventMessage($process->error, 'Error vale ingreso');
    }else{   
         setEventMessage($langs->trans("Vale de Entrada ingresado"));
         print '';
      }
          
    
$listps = $component->count_product_stock($process->fk_product,$process->planned_amount);
}

 print '<script> alert("'.$action.'"); </script>';


//
//
//if($action == 'gen_ing'){
//    
//   
//    $valeig = $process->create_valing($id);
//     if ($valeig < 0) {
//     setEventMessage($process->error, 'Error vale ingreso');
//    }else{   
//         setEventMessage($langs->trans("Vale de Entrada ingresado"));
//         print '';
//      }
//        
//      
//
//   
//}




if ($id > 0) {
    $process->fetch($id);
}


/**
 * View
 */
llxHeader('', $langs->trans("Production"), $help_url);
print $formconfirm;

print load_fiche_titre($langs->trans("ProductionOrderArea"), '', 'title_hrm.png');
// adding an process
if ($action == 'create') {
     
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
    $formproject = new FormProjets($db);


    print '<form action="" method="post" name="formprocessadd" id="formprocessadd">';
    print '<input type="hidden" name="action" value="add">';
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td colspan="1" class="fieldrequired">' . $langs->trans('ProductiveProcess') . '</td>';
    print '<td>' . $process->ref . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('Nombre del Proceso') . '</td>';
    print '<td><input type="text" size="30" name="process_type" value="' . GETPOST('process_type') . '" /></td></tr>';
    print '<tr><td>' . $langs->trans('Status') . '</td><td>';
    print $form->select_status_process('C', 'status', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('Product') . '</td><td>';
    print $form->select_production_product(GETPOST('fk_product') ? GETPOST('fk_product') : $process->fk_product, 'fk_product', 'style="width:300px;"');
    print '<tr><td class="fieldrequired">' . $langs->trans('PlannedAmount') . '</td>';
    print '<td><input type="text" size="30" id="planned_amount" name="planned_amount" value="' . GETPOST('planned_amount') . '"/></td></tr>';
    print '<tr style="display: none;"><td>' . $langs->trans('Warehouse') . '</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot'), 'fk_entrepot', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('Date') . '</td><td>';
    $date = GETPOST('dateday') ? GETPOST('dateyear') . '-' . GETPOST('datemonth') . '-' . GETPOST('dateday') : '';
    print $form->select_date($date, 'date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('DateTo') . '</td><td>';
    $date = GETPOST('date_end') ? GETPOST('date_endyear') . '-' . GETPOST('date_endmonth') . '-' . GETPOST('date_endday') : -1;
    print $form->select_date($date, 'date_end', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print '<tr style="display: none;" ><td>' . $langs->trans('User') . '</td><td>';
    print $form->select_dolusers(GETPOST('fk_user') ? GETPOST('fk_user') : $user->id, 'fk_user', 1);
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('ThirdParty') . '</td><td>';
    print $form->select_compania(GETPOST('fk_societe'), 'fk_societe', 's.client IN (1, 3)', 1);
    print '</td></tr>';
    print '<tr style="display: none;"><td>' . $langs->trans('ProfitCenter') . '</td><td>';
    print $form->select_profit_center(GETPOST('fk_profit_center'), 'fk_profit_center', 1, 'style="width:300px;"');
    print '<tr style="display: none;"><td>' . $langs->trans('Project') . '</td><td>';
    print $formproject->select_projects('-1', GETPOST('fk_project'), 'fk_project');
    print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" name="save" value="' . $langs->trans('Save') . '">';
    print '</div>';
    print '</form>';
}
// list processs
elseif (empty($action) || $action == 'list') {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
    $formproject = new FormProjets($db);

    $search_status = GETPOST('search_status');

    $sortfield = GETPOST('sortfield', 'alpha');
    $sortorder = GETPOST('sortorder', 'alpha');
    $page = GETPOST('page', 'int');
    if ($page == -1) {
        $page = 0;
    }
    $offset = $conf->liste_limit * $page;
    $pageprev = $page - 1;
    $pagenext = $page + 1;
    $limit = $conf->liste_limit;
    if (!$sortfield)
        $sortfield = "rowid";
    if (!$sortorder)
        $sortorder = "DESC";

    if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) { // Both test are required to be compatible with all browsers
        $search_status = "";
    }

    $sql = "SELECT rowid,ref, process_type, status, planned_amount, real_component_cost, aditional_cost, real_product_cost, ";
    $sql .= "completed_amount, rejected_amount, order_number ";
    $sql .= "FROM " . MAIN_DB_PREFIX . "productive_process ";
    $sql .= "WHERE entity = " . $conf->entity;
    //echo $sql;
    if (!empty($search_status))
        $sql .= " AND status = '$search_status' ";

    $sql .= $db->order($sortfield, $sortorder);
    //echo $sql;
    $result = $db->query($sql);

    $param = "search_status=$search_status";

    print $formconfirm;

    print '<div class="fichecenter">';
    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<table class="border" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("N°"), $_SERVER["PHP_SELF"], "rowid", '', '', 'style="width:5%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans("Ref"), $_SERVER["PHP_SELF"], "ref", '', '', 'style="width:10%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans("ProcessType"), $_SERVER["PHP_SELF"], "", "", $param, 'style="width:10%;"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("Status"), $_SERVER["PHP_SELF"], "status", "", $param, 'style="width:5%;"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans('PlannedAmount'), $_SERVER["PHP_SELF"], "", '', $param, 'style="width:5%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('CompletedAmount'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:4%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('RejectedAmount'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:5%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('RealComponentCost'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:10%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('AditionalCost'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:8%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('RealProductCost'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:8%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', 'style="width:5%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print "</tr>";
    print '<tr class="liste_titre">';
    print '<td class="liste_titre" align="left" colspan="2"></td>';
    print '<td class="liste_titre" align="left">';
    print '<td class="liste_titre" align="left">';
    print $form->select_status_process($search_status, 'search_status', 'style="width:80px;"');
    print '</td>';
    print '<td class="liste_titre" colspan="6"></td>';
    print '<td class="liste_titre nowrap" align="right">';
    print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
    print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("RemoveFilter"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
    print '</td>';
    // print '<td class="liste_titre" align="left" colspan="2"></td>';
    print '</tr>';
    if ($result) {
        $var = true;
        $num = $db->num_rows($result);
        $i = 0;
        while ($i < $num) {
            $var = !$var;
            $obj = $db->fetch_object($result);
            print '<tr ' . $bc[$var] . '>';
            print '<td style="width:5%;">' . $obj->order_number . '</td>';
            print '<td style="width:5%;">' . $obj->ref . '</td>';
            print '<td style="width:15%;">' . $obj->process_type . '</td>';
            print '<td style="width:5%;">' . ($obj->status == 'O' ? $langs->trans('Open') : $langs->trans('Closed')) . '</td>';
            print '<td style="width:5%;">' . $obj->planned_amount . '</td>';
            print '<td style="width:5%;">' . $obj->completed_amount . '</td>';
            print '<td style="width:5%;">' . $obj->rejected_amount . '</td>';
            print '<td style="width:10%;">' . $obj->real_component_cost . '</td>';
            print '<td style="width:8%;">' . $obj->aditional_cost . '</td>';
            print '<td style="width:8%;">' . $obj->real_product_cost . '</td>';
            print '<td style="width:5%;">';
            if ($user->rights->production->write) {
                print '<a href="./process.php?action=update&id=' . $obj->rowid . '">';
                print img_edit();
                print '</a>';
            }
            if ($user->rights->production->delete) {
                //  print '<a href="./process.php?action=delete&id=' . $obj->rowid . '">';
                //print img_delete();
                //print '</a>';
            }
            print '</td></tr>';
            $i++;
        }
    }
    print "</table>";
    print '</form>';
    print '</div>';
    if ($user->rights->production->write) {
        print '<form action="' . DOL_URL_ROOT . '/production/process.php?leftmenu=product&amp;mainmenu=production&amp;action=create" method="post">';
        print '<input type="hidden" name="action" value="create">';
        print '<p><input type="submit" class="button" value="' . $langs->trans('NewProductionOrden') . '"></p>';
        print '</form>';
    }
}
// adding an process
elseif ($action == 'update' || $action == 'component_add' || $action == 'gen_ing' || $action =='gen_sal') {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
    $formproject = new FormProjets($db);

    print '<form action="" method="post" name="formprocessedit" id="formprocessedit">';
    print '<input type="hidden" name="action" value="edit">';
    
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td class="fieldrequired" colspan="2">' . $langs->trans('ProductiveProcess') . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('OrderNumber') . '</td>';
    print '<td>' . $process->ref . '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('ProcessType') . '</td>';
    print '<td><input type="text" size="30" name="process_type" value="' . (GETPOST('process_type') ? GETPOST('process_type') : $process->process_type) . '" /></td></tr>';
    print '<tr><td>' . $langs->trans('Status') . '</td><td>';
    print $form->select_status_process(GETPOST('status') ? GETPOST('status') : $process->status, 'status', 'style="width:300px;"');
    if ($conf->use_javascript_ajax) {
        print '<script type="text/javascript">';
        print '$(document).ready(function () {
                        $("#status").change(function() {
                        	document.formprocessedit.action.value="update";
                        	document.formprocessedit.submit();
                        });
                     });';
        
        print '</script>';
    }
    
    
   
    if($process->status == 'O' || GETPOST('status') == 'O' ){
        if ($conf->use_javascript_ajax) {
        print '<script type="text/javascript">
            $(document).ready(function () {
                $("#status").ready(function() {
                    $("#val_sal").css({"display":"block" ,"margin-top":"-32px","margin-left":"366px" ,"width":"110px"});
                    $("#val_ing").css("display", "none");
                        });
                     });';
 
        
        print '</script>';
    }
    }
    
     if($process->status == 'C' || GETPOST('status') == 'C'){
        if ($conf->use_javascript_ajax) {
        print '<script type="text/javascript">
            $(document).ready(function () {
                $("#status").ready(function() {
                    $("#val_ing").css({"display":"block" ,"margin-top":"-32px","margin-left":"366px","width":"120px"});
                    $("#val_sal").css("display", "none");
                    
                        });
                     });';
        
     
 
        
        print '</script>';
    }
    }

    print '</td></tr>';
    
          

   //$list = $component->material_amount($process->fk_product);
  
   
   

   
//   var_dump($process->fk_product);
//
//   
//   foreach ($list as $val){
//      $resp= $val['amount']*$process->planned_amount;
//      $resp2= $listps[$val['fk_product']]['stock'];
//      
//       
//  if ($resp >= $resp2) {
//      //      setEventMessage("La cantidad de stock del producto ".$val['description']." es menor a la cantidad requerida ", "errors");
//        } else {
//            //setEventMessage('La cantidad requerida');
//        }
//
//   }
//       
     //print '<script> alert("'.$action.'")</script>';
   

    print '<tr><td class="fieldrequired">' . $langs->trans('Product') . '</td><td>';
    print $form->select_production_product(GETPOST('fk_product') ? GETPOST('fk_product') : $process->fk_product, 'fk_product', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('PlannedAmount') . '</td>';
    print '<td><input type="text" size="30" readonly name="planned_amount" value="' . (GETPOST('planned_amount') ? GETPOST('planned_amount') : $process->planned_amount) . '" /></td></tr>';
    print '<tr style="display: none;"><td>' . $langs->trans('Warehouse') . '</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot') ? GETPOST('fk_entrepot') : $process->fk_entrepot, 'fk_entrepot', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td class="fieldrequired">' . $langs->trans('Date') . '</td><td>';
    $date = GETPOST('dateday') ? GETPOST('dateyear') . '-' . GETPOST('datemonth') . '-' . GETPOST('dateday') : '';
    print $form->select_date(GETPOST('dateday') ? $date : $process->date, 'date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('DateTo') . '</td><td>';
    $date = GETPOST('date_end') ? GETPOST('date_endyear') . '-' . GETPOST('date_endmonth') . '-' . GETPOST('date_endday') : -1;
    print $form->select_date(GETPOST('date_end') ? $date : $process->date_end, 'date_end', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print '<tr style="display: none;" ><td>' . $langs->trans('User') . '</td><td>';
    print $form->select_dolusers(GETPOST('fk_user') ? GETPOST('fk_user') : $process->fk_user, 'fk_user', 1);
    print '</td></tr>';
    print '<tr style="display: none;"><td>' . $langs->trans('ThirdParty') . '</td><td>';
    print $form->select_company(GETPOST('fk_societe') ? GETPOST('fk_societe') : $process->fk_societe, 'fk_societe', 's.client IN (1, 3)', 1);
    print '</td></tr>';
    print '<tr style="display: none;"><td>' . $langs->trans('ProfitCenter') . '</td><td>';
    print $form->select_profit_center(GETPOST('fk_profit_center') ? GETPOST('fk_profit_center') : $process->fk_profit_center, 'fk_profit_center', 1, 'style="width:300px;"');
    print '<tr style="display: none;"><td>' . $langs->trans('Project') . '</td><td>';
    print $formproject->select_projects('-1', GETPOST('fk_project') ? GETPOST('fk_project') : $process->fk_project, 'fk_project');
    print '</td></tr>';


    print '<tr  ><td colspan="2" class="fieldrequired">' . $langs->trans('Costs') . '</td></tr>';
    print '<tr  ><td>' . $form->textwithtooltip($langs->trans('RealComponentCost'), $langs->trans('RealComponentCostExplanation'), 2, 1, img_picto('', 'info'), '', 2) . '</td>';
    $real_component_cost_tmp = ((int) $process->material_price * (int) $process->planned_amount);
    print '<td><input type="text" size="30" name="real_component_cost" id="real_component_cost" readonly value="' . (GETPOST('real_component_cost') ? GETPOST('real_component_cost') : $real_component_cost_tmp) . '" /></td></tr>';
    print '<tr  ><td>' . $langs->trans('AditionalCost') . '</td>';
    print '<td><input type="text" size="30" name="aditional_cost" id="aditional_cost" value="' . (GETPOST('aditional_cost') ? GETPOST('aditional_cost') : $process->aditional_cost) . '" onkeyup="calculate_costs()" />';
    if ($conf->use_javascript_ajax) {
        print '<script type="text/javascript">';
        print 'function calculate_costs(){
                    component_cost = parseInt(document.getElementById(\'real_component_cost\').value);
                    aditional_cost = parseInt(document.getElementById(\'aditional_cost\').value);
                    
                    real_product_cost = parseInt(component_cost + aditional_cost);
                    total_deviation = parseInt(component_cost - real_product_cost);
                    deviation_percent = total_deviation / component_cost * 100;
                    
                    document.getElementById(\'real_product_cost\').value = real_product_cost;
                    document.getElementById(\'total_deviation\').value = total_deviation;
                    document.getElementById(\'deviation_percent\').value = deviation_percent;
                }
                
                  function calculate_rejected_amount(){
                    planned_qty = parseInt(document.getElementById(\'planned_amount_2\').value);
                    complete_qty = parseInt(document.getElementById(\'completed_amount\').value);
                    
                   
                    rejected_amount = parseInt(planned_qty - complete_qty);
                    
                    
                    document.getElementById(\'rejected_amount\').value = rejected_amount;
               
                   //console.log(rejected_amount);

                }';
        print '</script>';
    }
    print '</td></tr>';
    print '<tr  ><td>' . $langs->trans('RealProductCost') . '</td>';
    print '<td><input type="text" size="30" name="real_product_cost" id="real_product_cost" readonly value="' . ($process->real_product_cost > 0 ? $process->real_product_cost : $real_component_cost_tmp) . '" /></td></tr>';
    print '<tr  ><td>' . $langs->trans('TotalDeviation') . '</td>';
    print '<td><input type="text" size="30" name="total_deviation" id="total_deviation" readonly value="' . $process->total_deviation . '" /></td></tr>';
    print '<tr style="display:none;"><td>' . $langs->trans('DeviationPercent') . '</td>';
    print '<td><input type="text" size="30" name="deviation_percent" id="deviation_percent" readonly value="' . $process->deviation_percent . '" /></td></tr>';


    print '<tr  ><td colspan="2" class="fieldrequired">' . $langs->trans('Quantities') . '</td></tr>';
    print '<tr  ><td>' . $langs->trans('PlannedAmount') . '</td>';
    print '<td><input type="text" size="30" name="planned_amount_2" id="planned_amount_2" readonly value=' . $process->planned_amount . ' /></td></tr>';
    print '<tr  ><td>' . $form->textwithtooltip($langs->trans('CompletedAmount'), $langs->trans('EnterWhenClosedStatus'), 2, 1, img_picto('', 'info'), '', 2) . '</td>';
    // readonly when process status is not closed (open or empty)
    print '<td><input type="text" size="30" name="completed_amount" id="completed_amount" ' . (GETPOST('status') == 'C' ? 'readonly' : '') . ' value="' . (GETPOST('completed_amount') ? GETPOST('completed_amount') : $process->completed_amount) . '" onkeyup="calculate_rejected_amount()" /></td></tr>';

    print '<tr  ><td>' . $form->textwithtooltip($langs->trans('RejectedAmount'), $langs->trans('EnterWhenClosedStatus'), 2, 1, img_picto('', 'info'), '', 2) . '</td>';
    // readonly when process status is not closed (open or empty)
    print '<td><input type="text" size="30" name="rejected_amount" id="rejected_amount" ' . (GETPOST('status') != 'C' ? 'readonly' : '') . ' readonly value="' . $process->rejected_amount . '" /></td></tr>';
    print '<tr  ><td colspan="2" class="fieldrequired">' . $langs->trans('Dates') . '</td>';
    print '<tr  ><td>' . $langs->trans('DateTo') . '</td>';
    $date_end = explode("-", $process->date_end);
    print '<td><input type="text" size="30" name="date_end_first" readonly value="' . ($date_end[2] . '/' . $date_end[1] . '/' . $date_end[0]) . '" /></td></tr>';
    print '<tr  ><td>' . $langs->trans('RealDateEnd') . '</td><td>';
    $date = GETPOST('real_date_endday') ? GETPOST('real_date_endyear') . '-' . GETPOST('real_date_endmonth') . '-' . GETPOST('real_date_endday') : '';
    print $form->select_date($date, 'real_date_end', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" action=gen_sal  name="save" value="' . $langs->trans('Save') . '">';
    //print '<a class= "butAction" id="val_sal" href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&amp;action=gen_sal"> ' . $langs->trans('Generar vale salida') . '</a>';
   // print '<a class= "butAction" id="val_ing" href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&amp;action=gen_ing"> ' . $langs->trans('Generar vale Entrada') . '</a>';
    
//    print '<input type="button" id="val_sal" class="button"  value="' . $langs->trans('Generar vale Salida') . '"" onclick="launch_export_sal();">';
//    print '<input type="button" id="val_ing" class="button"  name="val_ing" value="' . $langs->trans('Generar vale Entrada') . '"" onclick="launch_export_ing();">';
    print '</div>';
    print '</form>';
    
	print '
	<script type="text/javascript">
		function launch_export_sal() {
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("gen_sal");
			$("div.fiche div.tabBar form input[type=\"submit\"]").click();
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("");
		}
                
                function launch_export_xls() {
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("export_xls");
			$("div.fiche div.tabBar form input[type=\"submit\"]").click();
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("");    
		}
                
                function launch_export_pdf() {
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("export_pdf");
			$("div.fiche div.tabBar form input[type=\"submit\"]").click();
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("");    
		}

		function writebookkeeping() {
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("writebookkeeping");
			$("div.fiche div.tabBar form input[type=\"submit\"]").click();
		    $("div.fiche div.tabBar form input[name=\"action\"]").val("");
		}
                   // alert(launch_export_xls);

	</script>';
        
}


//
//   $listps = $component->count_product_stock($process->fk_product);
//   
//      foreach ($listps as $val){
//      // $upd= $val['stock'] - $val['amount'];
//       
//            }
            


   //var_dump($listps);


// adding components
//if(($action_tmp != 'delete' && $action_tmp != 'confirm_delete')){


if (!empty($id) || $id > 0) {
    //print load_fiche_titre();
    print '<br>';
    print '<table class="border" width="100%">';
    print '<tr class="liste_titre"><td>' . $langs->trans("Type") . '</td>';
    print '<td>' . $langs->trans("Code") . '</td>';
    print '<td>' . $langs->trans("Description") . '</td>';
    print '<td>' . $langs->trans("Quantity") . '</td>';
    print '<td>' . $langs->trans("Price") . '</td>';
    print '<td>' . $langs->trans("Total") . '</td>';
    print '<td>' . $langs->trans("Comment") . '</td>';
    print '<td>' . $langs->trans("") . '</td></tr>';

    
         $list = $material->fetch_product_materials($process->fk_product);

    //$list1 = $component->fetch_process_components($id);
    $var = true;

    for ($i = 0; $i < count($list); $i++) {
        $linea = $i + 1;
        $var = !$var;
        if ($action == 'edit_line_component' && $list[$i]['rowid'] == GETPOST('lineid')) {
            /*             * ************************************************************************************ */



            print '<form action=""  method="post" name="form_component_update" id="form_component_update">';
            print '<input type="hidden" name="action" value="edit_line_component_material">';
            print '<input type="hidden" name="lineid" value="' . $lineid . '">';
            print '<input type="hidden" name="id" value="' . $id . '">';

            print '<tr ' . $bc[$var] . '>';

            print '<td>' . $form->select_material(!empty($list[$i]['fk_material']) ? $list[$i]['fk_material'] : '' . $component->fk_material, 'component_fk_material', 'style="width:200px;"') . '</td>';
            print '<td><input type="text" size="3" name="component_amount" value="' . $list[$i]['amount'] . '" /></td>';
            print '<td><input type="text" size="3" readonly name="required_amount" value="' . price2num($list[$i]['required_amount']) . '"/></td>';
            print '<td><input type="text" size="3" readonly name="stock" value="' . price2num($list[$i]['stock']) . '"/></td>';
            print '<td>' . $form->select_entrepot(!empty($list[$i]['fk_entrepot']) ? $list[$i]['fk_entrepot'] : '' . $component->fk_entrepot, 'component_fk_entrepot1', 'style="width:200px;"') . '</td>';
            print '<td><textarea cols="20" rows="4" name="comment">' . $list[$i]['comment'] . '</textarea></td>';
            print '<td align=right>';
            print '<button type="submit"  name="submit" style="background:none;border:none;margin-right:2px" class="button"><img src="' . DOL_URL_ROOT . '/theme/md/img/acept.png" /></button>';
            print '<button type="submit"  name="cancel" style="background:none;border:none;margin-right:2px" class="button"><img src="' . DOL_URL_ROOT . '/theme/md/img/cancel.png" /></button>';

            print '</td>';
            print '</tr>';
            print '</form>';

            /*             * ************************************************************************************ */
        }
    }

    for ($i = 0; $i < count($list); $i++) {
      
        $linea = $i + 1;
        $var = !$var;
            print '<tr ' . $bc[$var] . '>';
            print '<td>' . ($list[$i]['type'] == 'P' ? $langs->trans('Product') : $langs->trans('Other')) . '</td>';
            print '<td>' . $list[$i]['code'] . '</td>';
            print '<td>' . $list[$i]['description'] . '</td>';
            print '<td>' . $list[$i]['amount'] . '</td>';
            print '<td>' . price(round($list[$i]['price'])) . '</td>';
            print '<td>' . price(round($list[$i]['total'])) . '</td>';
            print '<td>' . $list[$i]['comment'] . '</td>';
            print '<td style="display:none">' . $list[$i]['comment'] . '</td>';
            //print '<td>' . $list[$i]['comment'] . '</td></tr>';
            // print '<script> alert("'.$list[$i]['amount'].'");</script>';
             //print '<script> alert("' .$product->amount.'");</script>';
            //print '<td align=right></td>';
           // print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_line_product&amp;lineid=' . $list[$i]['rowid'] . '&amp;id=' . $id . '">' . img_edit('Editar ' . $langs->trans('Line') . ' ' . $linea, 0) . '</a>';

            //print '<a href="' . $_SERVER["PHP_SELF"] . '?action=deleteline&id=' . $id . '&lineid=' . $list[$i]['rowid'] . '">' . img_delete('Eliminar ' . $langs->trans('Line') . ' ' . $linea, 1) . '</a>';

            print '</tr>';
      //var_dump( $list[$i]['amount']);   
    }
    print '</table>';
    print '<br><br>';
    
    
     

    if ($process->material_type == 'P' || GETPOST('component_fk_entrepot')) {
        require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
        $product = new Product($db);

        if (GETPOST('component_fk_entrepot')) {
            $product_stock = $product->stock_by_entrepot($process->fk_product, GETPOST('component_fk_entrepot'));
        } else {
            $product->fetch($process->fk_product);
            $product->load_stock();
            $product_stock = $product->stock_reel;
        }
    } else {
        unset($product_stock);
    }
    


//    print '<form action="" method="post" name="form_component_add" id="form_component_add">';
//    print '<input type="hidden" name="action" value="component_add">';
//    print '<input type="hidden" name="id" value="' . $id . '">';
//    print '<div class="tabBar">';
//    print '<table class="border" width="100%">';
//    print '<tr><td colspan="2" class="fieldrequired">' . $langs->trans('AddComponent') . '</td></tr>';
//    print '<tr><td class="fieldrequired">' . $langs->trans('Material') . '</td><td>';
//
//    print $form->select_material(GETPOST('component_fk_material') ? GETPOST('component_fk_material') : $component->fk_material, 'component_fk_material', 'style="width:300px;"', $process->fk_product);
//    print '</td></tr>';
//    print '<tr><td class="fieldrequired">' . $langs->trans('Warehouse') . '</td><td>';
//    print $form->select_entrepot(GETPOST('component_fk_entrepot'), 'component_fk_entrepot', 'style="width:300px;"');
//    if ($conf->use_javascript_ajax) {
//        print '<script type="text/javascript">';
//        print '$(document).ready(function () {
//                        $("#component_fk_entrepot").change(function() {
//                        	document.form_component_add.action.value="update";
//                        	document.form_component_add.submit();
//                        });
//                     });';
//        print '</script>';
//    }
//    print '</td></tr>';
//    print '<tr><td class="fieldrequired">' . $langs->trans('Quantity') . '</td>';
//    print '<td><input type="text" size="30" name="component_amount" value="' . GETPOST('component_amount') . '" /></td></tr>';
//    print '<tr><td>' . $langs->trans('RequiredQuantity') . '</td>';
//    $required_amount = $component->fetch_productive_process_planned_amount($id);
//    print '<td><input type="text" size="30" name="required_amount" readonly value="' . (GETPOST('required_amount') ? GETPOST('required_amount') : $required_amount) . '" /></td></tr>';
//    print '<tr><td>' . $langs->trans('Stock') . '</td>';
//    print '<td><input type="text" size="30" name="stock" readonly value="' . (isset($product_stock) ? $product_stock : 0) . '" /></td></tr>';
//    print '<tr style="display:none"><td>' . $langs->trans('EmissionMethod') . '</td>';
//    print '<td><input type="text" size="30" name="emission_method" value="' . GETPOST('emission_method') . '" /></td></tr>';
//    print '<tr><td>' . $langs->trans('Comment') . '</td>';
//    print '<td><textarea cols="32" rows="4" name="comment">' . GETPOST('comment') . '</textarea></td></tr>';
//    print "</table>";
//    print '</div>';
//    print '<div class="center">';
//    print '<input type="submit" class="button" name="save" value="' . $langs->trans('Save') . '">';
//    print '</div>';
//    print '</form>';
}
//}

llxFooter();
$db->close();
