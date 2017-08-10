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
 *   \file       htdocs/production/index.php
 *   \brief      Production module
 *   \ingroup    production
 */
require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/html.formproduction.class.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/production/class/material.class.php';

$help_url = 'EN:Module_Production|ES:Producción';

$langs->load('production');
$langs->load('accountingex@accountingex');
$langs->load('products');
$langs->load('projects');
$active = GETPOST('active');
$inactive = GETPOST('inactive');



$form = new FormProduction($db);
$product = new ProductionProduct($db);
$material = new ProductionMaterial($db);

$action = GETPOST('action', 'alpha', 3);
$id = GETPOST('id', 'int', 3);
$idline = GETPOST('lineid');
$confirm = GETPOST('confirm');

//echo "<script>alert('".$idline."');</script>";


if ($action == 'deleteline') {

    $form = new Form($db);
    $text = $langs->trans('ConfirmDeleteMaterial');
    $formquestion = array(
        array('type' => 'hidden', 'name' => 'id', 'label' => 'id', 'value' => $id),
        array('type' => 'hidden', 'name' => 'deletematerial', 'label' => 'idline', 'value' => $idline)
    );

    echo $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans('ConfirmDeleteMaterial'), $text, 'confirm_delete_material', $formquestion, "yes", 1);
}


if ($action == 'confirm_delete_material' && $confirm == 'yes') {
    $deletematerial = GETPOST('deletematerial');



    //echo "<script>alert('jam".$deletematerial."');</script>";

    $result = $material->deleteline($id, $deletematerial);
    // echo "<script>alert('".$result."');</script>";

    if ($result == 0) {
        $result = $material->delete1($idine);
        if ($result == 0) {
        //    setEventMessage('DeleteMaterial');
        } else {
      //      setEventMessage('MaterialError', 'errors');
        }
    } else {
    //    setEventMessage('MaterialError1', 'errors');
    }
}



if ($id > 0) {
    $product->fetch($id);
}
/**
 * Actions
 */
if ($action == 'active') {
    $result = $product->ProductionProduct_activate($id);
    if ($result < 0) {
        setEventMessage($product->error, 'errors');
    }
    header("Location:" . DOL_URL_ROOT . "/production/index.php?action=list");
}
if ($action == 'inactive') {
    $result = $product->ProductionProduct_desactivate($id);
    if ($result < 0) {
        setEventMessage($product->error, 'errors');
    }
    header("Location:" . DOL_URL_ROOT . "/production/index.php?action=list");
}





if ($action == 'edit_line_material') {

    $material->code = GETPOST('material_code');
    $material->description = GETPOST('material_description');
    $material->amount = GETPOST('material_amount');
    $material->fk_unit_metric = GETPOST('material_unit');
    $material->price = GETPOST('material_price');
    $material->comment = GETPOST('comment');
    $material->total = $material->amount * $material->price;


    $result = $material->edit_material_production_line(GETPOST('lineid'));
    //  $action='list';
    // header("Location: action=update&id='".$id."'");

    if ($result < 0) {

        setEventMessage($material->error, 'errors');
    }
}


// saving product
if ($action == 'add' || $action == 'edit') {
    $product->fk_product = GETPOST('fk_product');
    $product->material_type = GETPOST('material_type');
    $product->amount = GETPOST('amount');
    $product->fk_entrepot = GETPOST('fk_entrepot');
    $product->price = GETPOST('price');
    $product->fk_accounting_account = GETPOST('fk_accounting_account');
    $product->fk_profit_center = GETPOST('fk_profit_center');
    $product->fk_project = GETPOST('fk_project');

    $errors = 0;
    if ($product->fk_product == 0) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Product')), 'errors');
        $errors++;
    }

    if ($errors == 0) {
        if ($action == 'add') {
            $result = $product->create();
        } elseif ($action == 'edit') {
            $result = $product->update($id);
        }

        if ($result >= 0) {
            setEventMessage($langs->trans("ProductSaved"));
            if ($action == 'add') {
                $product->fetch($result);
                $action = 'update';
                $id = $result;
            } elseif ($action == 'edit') {
                $action = 'update';
            }
        } elseif ($result == -1) {
            setEventMessage($langs->trans('ProductDuplicated'), 'errors');
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
} elseif ($action == 'material_add') {
    $material->type = GETPOST('material_type');
    if ($material->type == 'P') {
        $material->fk_product = GETPOST('material_product');
    } else {
        $material->fk_product = 'NULL';
    }
    $material->code = GETPOST('material_code');
    $material->description = GETPOST('material_description');
    $material->amount = GETPOST('material_amount');
    $material->fk_unit_metric = GETPOST('material_unit');
    $material->emission_method = GETPOST('emission_method');
    $material->price = GETPOST('material_price');
    $material->comment = GETPOST('comment');
    $material->total = $material->amount * $material->price;

    $errors = 0;

    if (empty($material->type)) {
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Material')), 'errors');
        $errors++;
    }

    if ($errors == 0) {
        $result = $material->create();
        if ($result > 0) {
            $product_material = new ProductionProductMaterial($db);

            $product_material->fk_product = $id;
            $product_material->fk_material = $result;

            $res = $product_material->create();

            if ($res == 0) {
                setEventMessage($langs->trans("ProductMaterialSaved"));
		

            } elseif ($result == -1) {
                setEventMessage($langs->trans('ProductMaterialDuplicated'), 'errors');
            } elseif ($result == -3) {
                setEventMessage($langs->trans('Error'), 'errors');
            }

            // Updating Product Price
            $product->price = $material->fetch_product_materials_value($id);
            $res = $product->update($id);
            if ($res == 0) {
                setEventMessage($langs->trans("ProductPriceUpdate"));
            } elseif ($result == -3) {
                setEventMessage($langs->trans('Error'), 'errors');
            }
        }
    }

    $material->code = "";
    $material->description = "";
    $material->amount = "";
    $material->fk_unit_metric = "";
    $material->emission_method = "";
    $material->price = "";
    $material->comment = "";
 header("Location:" . DOL_URL_ROOT . "/production/index.php?action=update&id=$id");

}

$material_unit = GETPOST('material_unit');
$material_product = GETPOST('material_product');
$material_description = GETPOST('material_description');
$material_code = GETPOST('material_code');




if ($action== 'resetform'){
    
$material_unit = "";
$material_product = "";
$material_code = "";
$material_description = "";
}


/**
 * View
 */
llxHeader('', $langs->trans("Production"), $help_url);
print $formconfirm;


// adding an production
if ($action == 'create') {
    print load_fiche_titre($langs->trans("MaterialListArea"), '', 'title_hrm.png');
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
    $formproject = new FormProjets($db);

    print '<form action="" method="post" name="formproductionadd" id="formproductionadd">';
    print '<input type="hidden" name="action" value="add">';
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td class="fieldrequired">' . $langs->trans('ProductList') . '</td><td>';
    print $form->select_produits_list(GETPOST('fk_product'), 'fk_product', 0, 0, 0, '', 1, 2, 0, 0, $htmloption = 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('MaterialType') . '</td><td>';
    print $form->select_material_type('PF', 'material_type');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('Quantity') . '</td>';
    print '<td><input type="text" size="30" name="amount" id="amount" value=1 /></td></tr>';
    print '<tr><td>' . $langs->trans('Warehouse') . '</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot'), 'fk_entrepot');
    print '</td></tr>';
    print '<tr ' . (empty($id) || $id == 0 ? 'style="display:none"' : '') . '><td>' . $langs->trans('Price') . '</td>';
    print '<td><input type="text" size="30" name="price" id="price" readonly value="' . GETPOST('price') . '" /></td></tr>';
    print '<tr><td>' . $langs->trans('AccountingAccount') . '</td><td>';
    print $form->select_accounting_account(GETPOST('fk_accounting_account'), 'fk_accounting_account');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('ProfitCenter') . '</td><td>';
    print $form->select_profit_center(GETPOST('fk_profit_center'), 'fk_profit_center', 1);
    //print '<tr><td>'.$langs->trans('Project').'</td><td>';
    //print $formproject->select_projects('-1', GETPOST('fk_project'), 'fk_project');
    //print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" name="save" value="' . $langs->trans('Save') . '">';
    print '</div>';
    print '</form>';
}
// list productions
elseif (empty($action) || $action == 'list') {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';

    print load_fiche_titre($langs->trans("MaterialListArea"), '', 'title_hrm.png');

    $formproject = new FormProjets($db);

    $search_product = GETPOST('search_product', 'int');
    $search_material_type = GETPOST('search_material_type', 'alpha');
    $search_entrepot = GETPOST('search_entrepot', 'int');
    $search_acounting_account = GETPOST('search_acounting_account');
    $search_profit_center = GETPOST('search_profit_center');
    $search_project = GETPOST('search_project');

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
        $sortfield = "pp.rowid";
    if (!$sortorder)
        $sortorder = "DESC";

    if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) { // Both test are required to be compatible with all browsers
        $search_product = "";
        $search_material_type = "";
        $search_entrepot = "";
        $search_acounting_account = "";
        $search_profit_center = "";
        $search_project = "";
    }

    $sql = "SELECT pp.rowid, pp.material_type, pp.price, pp.amount, pp.fk_project, pp.fk_entrepot, pp.fk_accounting_account, ";
    $sql .= "pp.fk_profit_center, pp.fk_project, p.title, prod.ref, prod.label as label_product, aa.label, pc.name, e.lieu as entrepot_name, pp.active ";
    $sql .= "FROM " . MAIN_DB_PREFIX . "production_product AS pp ";
    $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "projet AS p ON p.rowid = pp.fk_project ";
    $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "product AS prod ON prod.rowid = pp.fk_product ";
    $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "accountingaccount AS aa ON aa.rowid = pp.fk_accounting_account ";
    $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "profit_center AS pc ON pc.profit_id = pp.fk_profit_center ";
    $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "entrepot AS e ON e.rowid = pp.fk_entrepot ";
    $sql .= "WHERE pp.entity = " . $conf->entity;
         if (empty($active)){
$sql.=' AND pp.active = 1 ';
         }else{
             
             if($active>=0){
                $sql.=' AND pp.active = '.$active.' '; 
             }
         
         }
//echo $sql;
    if (!empty($search_product) && $search_product != -1)
        $sql .= " AND pp.fk_product = '$search_product' ";
    if (!empty($search_material_type))
        $sql .= " AND pp.material_type = '$search_material_type' ";
    if (!empty($search_entrepot))
        $sql .= " AND pp.fk_entrepot = '$search_entrepot' ";
    if (!empty($search_acounting_account))
        $sql .= " AND pp.fk_accounting_account = '$search_acounting_account' ";
    if (!empty($search_profit_center))
        $sql .= " AND pp.fk_profit_center = '$search_profit_center' ";
    if (!empty($search_project))
        $sql .= " AND pp.fk_project = '$search_project' ";


    $sql .= $db->order($sortfield, $sortorder);
    echo $sql;
    $result = $db->query($sql);

    $param = "search_product=$search_product&search_material_type=$search_material_type&search_entrepot=$search_entrepot";

    print $formconfirm;

    print '<div class="fichecenter">';
    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<table class="border" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("N°"), $_SERVER["PHP_SELF"], "", '', '', 'style="width:4%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans("Product"), $_SERVER["PHP_SELF"], "pp.fk_project", "", $param, 'style="width:15%;"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans("MaterialType"), $_SERVER["PHP_SELF"], "pp.material_type", "", $param, 'style="width:15%;"', $sortfield, $sortorder);
    print_liste_field_titre($langs->trans('Quantity'), $_SERVER["PHP_SELF"], "", '', $param, 'style="width:4%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Warehouse'), $_SERVER["PHP_SELF"], "pp.fk_entrepot", '', '', 'style="width:10%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Price'), $_SERVER["PHP_SELF"], "", '', '', 'style="width:7%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('AccountingAccount'), $_SERVER["PHP_SELF"], "pp.fk_accounting_account", '', '', 'style="width:10%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('ProfitCenter'), $_SERVER["PHP_SELF"], "pp.fk_profit_center", '', '', 'style="width:15%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Activo'), $_SERVER["PHP_SELF"], "pp.active", '', '', 'style="width:15%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', 'style="width:5%;"', $sortfield, $sortorder, 'maxwidthsearch ');
    print "</tr>";
    print '<tr class="liste_titre">';
    print '<td class="liste_titre">';
    print '</td>';
    print '<td class="liste_titre" align="left">';
    print $form->select_produits_list($search_product, 'search_product', 0, 20, 0, '', 1, 2, 0, 0, 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre" align="left">';
    //print $form->select_material_type($search_material_type, 'search_material_type', 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre">';
    print '</td>';
    print '<td class="liste_titre" align="right">';
    print $form->select_entrepot($search_entrepot, 'search_entrepot', 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre">';
    print '</td>';
    print '<td class="liste_titre" align="right">';
    print $form->select_accounting_account($search_acounting_account, 'search_acounting_account', 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre" align="right">';
    print $form->select_profit_center($search_profit_center, 'search_profit_center', 1, 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre" align="right">';
    $active=array('1'=>'Activo', "'0'"=> 'Inactivo', '-1'=>'Todas');
    print $form->selectarray('active',$active,GETPOST('active'));
    
    print '</td>';
    print '<td class="liste_titre nowrap" align="right">';
    print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
    print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("RemoveFilter"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
    print '</td>';
    print '</tr>';
    if ($result) {
        $var = true;
        $num = $db->num_rows($result);
        $i = 0;
        while ($i < $num) {
            $var = !$var;
            $obj = $db->fetch_object($result);
            print '<tr ' . $bc[$var] . '>';
            print '<td style="width:4%;">' . ($i + 1) . '</td>';
            print '<td style="width:15%;">' . $obj->ref . '-' . $obj->label_product . '</td>';
            print '<td style="width:15%;">' . $langs->trans($obj->material_type) . '</td>';
            print '<td style="width:4%;">' . $obj->amount . '</td>';
            print '<td style="width:10%;">' . $obj->entrepot_name . '</td>';
            print '<td style="width:7%;">' . price(round($obj->price)) . '</td>';
            print '<td style="width:10%;">' . $obj->label . '</td>';
            print '<td style="width:15%;">' . $obj->name . '</td>';
            print '<td>';
            if (empty($obj->active)) {
                print '<a href="./index.php?id=' . $obj->rowid . '&action=active">';
                print img_picto($langs->trans("Disabled"), 'switch_off');
                print '</a>';
            } else {
                //print '<a href="./index.php?action=inactive&id=' . $obj->rowid . '">';
                print '<a href="./index.php?id=' . $obj->rowid . '&action=inactive">';
                print img_picto($langs->trans("Activated"), 'switch_on');
                print '</a>';
            }
            print '</td>';
            print '<td style="width:5%;">';
            if ($obj->active == 1) {
                print '<a href="./index.php?action=update&id=' . $obj->rowid . '">';
                print img_edit();
                print '</a>';
            }
            if ($user->rights->production->delete) {
                // print '<a href="./index.php?action=delete&id=' . $obj->rowid . '">';
                //     print img_delete();
                print '</a>';
            }
            print '</td></tr>';
            $i++;
        }
    }
    print "</table>";
    print '</form>';
    print '</div>';
    if ($user->rights->production->write) {
        print '<form action="' . DOL_URL_ROOT . '/production/index.php?leftmenu=product&amp;mainmenu=production&amp;action=create" method="post">';
        print '<input type="hidden" name="action" value="create">';
        print '<p><input type="submit" class="button" value="' . $langs->trans('NewProduct') . '"></p>';
        print '</form>';
    }
}
// adding an production
elseif ($action == 'update' || $action == 'material_add') {
    print load_fiche_titre($langs->trans("MaterialListArea"), '', 'title_hrm.png');

    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
    $formproject = new FormProjets($db);

    print '<form action="" method="post" name="formproducationedit" id="formproductionedit" >';
    print '<input type="hidden" name="action" value="edit">';
    print '<div class="tabBar">';
    print '<table class="border" width="100%" id="adding_material">';
    print '<tr><td class="fieldrequired">' . $langs->trans('ProductList') . '</td><td>';
    print $form->select_produits_list(GETPOST('fk_product') ? GETPOST('fk_product') : $product->fk_product, 'fk_product', 0, 0, 0, '', 1, 2, 0, 0, $htmloption = 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('MaterialType') . '</td><td>';
    print $form->select_material_type('PF' , 'material_type');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('Quantity') . '</td>';
    print '<td><input type="text" size="30" name="amount"  value="' . (GETPOST('amount') ? GETPOST('amount') : $product->amount) . '" /></td></tr>';
    print '<tr><td>' . $langs->trans('Warehouse') . '</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot') ? GETPOST('fk_entrepot') : $product->fk_entrepot, 'fk_entrepot');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('Price') . '</td>';
    print '<td><input type="text" size="30" name="price" readonly value="' . (GETPOST('price') ? GETPOST('price') : price(round($product->price)) ) . '" /></td></tr>';
    print '<tr><td>' . $langs->trans('AccountingAccount') . '</td><td>';
    print $form->select_accounting_account(GETPOST('fk_accounting_account') ? GETPOST('fk_accounting_account') : $product->fk_accounting_account, 'fk_accounting_account');
    print '</td></tr>';
    print '<tr><td>' . $langs->trans('ProfitCenter') . '</td><td>';
    print $form->select_profit_center(GETPOST('fk_profit_center') ? GETPOST('fk_profit_center') : $product->fk_profit_center, 'fk_profit_center', 1);
    print '<tr style="display:none"><td>' . $langs->trans('Project') . '</td><td>';
    print $formproject->select_projects('-1', GETPOST('project') ? GETPOST('fk_project') : $product->fk_project, 'fk_project');
    print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" name="save" value="' . $langs->trans('Save') . '">';
    print '</div>';
    print '</form>';
}

// adding materials
//if(($action_tmp != 'delete' && $action_tmp != 'confirm_delete' && $action!= 'active' || $action!='inactive')){

if (!empty($id) || $id > 0) {
    print load_fiche_titre($langs->trans("MaterialList"), '', 'title_hrm.png');
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


    $list = $material->fetch_product_materials($id);
    $var = true;

    for ($i = 0; $i < count($list); $i++) {
        $linea = $i + 1;
        $var = !$var;
        if ($action == 'edit_line_product' && $list[$i]['rowid'] == GETPOST('lineid')) {
            /*             * ************************************************************************************ */



            print '<form action=""  method="post" name="form_material_update" id="form_material_update">';
            print '<input type="hidden" name="action" value="edit_line_material">';
            print '<input type="hidden" name="lineid" value="' . $lineid . '">';
            print '<input type="hidden" name="id" value="' . $id . '">';

            print '<tr ' . $bc[$var] . '>';
            print '<td>' . ($list[$i]['type'] == 'P' ? $langs->trans('Product') : $langs->trans('Other')) . '</td>';


            if ($list[$i]['type'] == 'P') {

                if ($list[$i]['code']) {
                    require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
                    $productline = new Product($db);
                    $productline->fetch($list[$i]['fk_product']);
                }

                print '<td><input type="text" size="15" name="material_code" value="' . $list[$i]['code'] . '" /></td></td>';
            } else {
                print '<td> <input type="text" size="15" name="material_code" value="' . $list[$i]['code'] . '" /></td>';
            }

           // print '<script>alert("'.$list[$i]['amount'].'");</script>';
            
            
            print '<td><textarea cols="25" rows="4" name="material_description">' . (GETPOST('material_product') ? $material->description : GETPOST('material_description')) . $list[$i]['description'] . '</textarea></td>';
            print '<td><input type="text" size="1" name="material_amount" value="' . $list[$i]['amount'] . '" /></td>';
            print '<td><input type="text" size="8" name="material_price" value="' . price(round($list[$i]['price'])) . '" /></td>';
            print '<td>' . price(round($list[$i]['total'])) . '</td>';
            print '<td style="display:none"> <input type="text" size="20" name="emission_method" value="' . $list[$i]['emission_method'] . '" /> </td>';
            print '<td><textarea cols="20" rows="4" name="comment">' . $list[$i]['comment'] . '</textarea></td>';
            //print '<td>' . $list[$i]['comment'] . '</td></tr>';
            print '<td align=right>';

           
            
              

            print '<button type="submit"  name="submit" style="background:none;border:none;margin-right:2px" class="button"><img src="' . DOL_URL_ROOT . '/theme/md/img/acept.png" /></button>';
            print '<button type="submit"  name="cancel" style="background:none;border:none;margin-right:2px" class="button"><img src="' . DOL_URL_ROOT . '/theme/md/img/cancel.png" /></button>';

            print '</td>';
            print '</tr>';
            print '</form>';


            //  var_dump($list);
            /*             * ************************************************************************************ */
        } else {
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
            print '<td align=right>';
            print '<a href="' . $_SERVER["PHP_SELF"] . '?action=edit_line_product&amp;lineid=' . $list[$i]['rowid'] . '&amp;id=' . $id . '">' . img_edit('Editar ' . $langs->trans('Line') . ' ' . $linea, 0) . '</a>';

            print '<a href="' . $_SERVER["PHP_SELF"] . '?action=deleteline&id=' . $id . '&lineid=' . $list[$i]['rowid'] . '">' . img_delete('Eliminar ' . $langs->trans('Line') . ' ' . $linea, 1) . '</a>';

            print '</td></tr>';
        }
        //    echo "<script>alert('".$action."');</script>";
    }

    print '</table>';

    print '<br><br>';

    
    
    

    if ($action != 'edit_line_product') {
        print '<form action="" method="post" name="form_material_add" id="form_material_add"  ">';
        print '<input type="hidden" name="action" value="material_add">';
        print '<input type="hidden" name="id" value="' . $id . '">';
        print '<div class="tabBar">';
        print '<table class="border" width="100%">';
        print '<tr><td colspan="2" class="fieldrequired">' . $langs->trans('AddMaterial') . '</td></tr>';
        print '<tr><td class="fieldrequired">' . $langs->trans('Material') . '</td><td>';
        print $langs->trans('Product') . '<input type="radio" id="material_type" name="material_type" value="P" style="margin-left:10px;" onclick="show_products()" ' . (GETPOST('material_type') == 'P' ? 'checked' : '') . ' />&nbsp;&nbsp;';
        print $langs->trans('Other') . '<input type="radio" id="material_type" name="material_type" value="O" style="margin-left:10px;" onclick="hide_products()" ' . (GETPOST('material_type') == 'O' ? 'checked' : '') . ' />';
        if ($conf->use_javascript_ajax) {
            print '<script type="text/javascript">';
            print 'function show_products(){
                document.getElementById(\'col_products\').style.display = \'\';
               }';
            print 'function hide_products(){
                document.getElementById(\'col_products\').style.display = \'none\';
               }';
            print '</script>';
        }
        print '</td></tr>';
        $display = GETPOST('material_type') == 'P' ? '' : 'none';
        print '<tr id="col_products" style="display:' . $display . ';"><td>' . $langs->trans('ProductList') . '</td><td>';
        print $form->select_produits_list(GETPOST('material_product'), 'material_product', 0, 0, 0, '', 1, 2, 0, 0, $htmloption = 'style="width:300px;"');
        if ($conf->use_javascript_ajax) {
            print '<script type="text/javascript" language="javascript">
                $(document).ready(function () {
                    $("#material_product").change(function() {
                        document.form_material_add.action.value="update";
                	document.form_material_add.submit();
                    });
                    
                    
                })
                
               </script>';
        }
        print '</td></tr>';

        if (GETPOST('material_product')) {
            require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
            $product = new Product($db);
            $product->fetch(GETPOST('material_product'));
        }

        print '<tr><td>' . $langs->trans('Code') . '</td>';
        print '<td><input type="text" size="30" name="material_code" value="' . (GETPOST('material_product') ? $product->ref : GETPOST('material_code')) . '" /></td></tr>';
        print '<tr><td>' . $langs->trans('Description') . '</td>';
        print '<td><textarea cols="32" rows="4" id="material_description" name="material_description">' . (GETPOST('material_product') ? $product->description : GETPOST('material_description')) . '</textarea></td></tr>';
        print '<tr><td>' . $langs->trans('Quantity') . '</td>';
        print '<td><input type="text" size="30" id="material_amount" name="material_amount" value="' . (GETPOST('material_amount')) . '" /></td></tr>';       
        print '<tr><td>' . $langs->trans('UnitMetric') . '</td><td>';
        print $form->select_unit(GETPOST('material_unit'), 'material_unit', array("'unit'", "'weight'", "'length'", "'volume'"));
        print '</td></tr>';
        print '<tr style="display:none"><td>' . $langs->trans('EmissionMethod') . '</td>';
        print '<td><input type="text" size="30" name="emission_method" value="' . GETPOST('emission_method') . '" /></td></tr>';
        print '<tr><td>' . $langs->trans('PMP') . '</td>';
        print '<td><input type="text" size="30" name="material_price" value="' . (GETPOST('material_product') ? price2num($product->pmp) : GETPOST('material_price')) . '" /></td></tr>';
        print '<tr><td>' . $langs->trans('Comment') . '</td>';
        print '<td><textarea cols="32" rows="4" name="comment">' . GETPOST('comment') . '</textarea></td></tr>';

        print "</table>";
        print '</div>';
        print '<div class="center">';
        print '<input type="submit" class="button"  name="save" value="' . $langs->trans('Save') . '">';
        
        print '</div>';
        print '</form>';

        
        
        
     
    }
    //echo "<script>alert('".$action."');</script>";
}
llxFooter();
$db->close();
