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
 *   \file       htdocs/production/voucher.php
 *   \brief      Production module
 *   \ingroup    production
 */

require_once '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/production/class/html.formvoucher.class.php';
require_once DOL_DOCUMENT_ROOT.'/production/class/html.formprocess.class.php';
require_once DOL_DOCUMENT_ROOT.'/production/class/voucher.class.php';

$help_url='EN:Module_Production|ES:Producción';

$langs->load('production');
$langs->load('bills');

llxHeader('', $langs->trans("Production"), $help_url);

$form = new FormProductionVoucher($db);
$formprocess = new FormProcess($db);
$voucher = new ProductionVoucher($db);

$action = GETPOST('action', 'alpha', 3);
$id = GETPOST('id', 'int', 1);

if($id > 0){
    $voucher->fetch($id);
}

/**
 * Actions
 */
// saving voucher
if($action == 'add' || $action == 'edit')
{
    $voucher->accounting_date       = GETPOST('accounting_dateyear').'-'.GETPOST('accounting_datemonth').'-'.GETPOST('accounting_dateday');
    $voucher->reference             = GETPOST('reference');
    $voucher->comment               = GETPOST('comment');
    $voucher->gloss_accounting_entry= GETPOST('gloss_accounting_entry');
    $voucher->fk_process_number     = GETPOST('fk_process_number');
    $voucher->fk_product            = GETPOST('fk_product');
    $voucher->amount                = GETPOST('amount');
    $voucher->price                 = GETPOST('price');
    $voucher->fk_entrepot           = GETPOST('fk_entrepot');
    $voucher->serial_number         = GETPOST('serial_number');
    $voucher->expiration_date       = GETPOST('expiration_dateyear').'-'.GETPOST('expiration_datemonth').'-'.GETPOST('expiration_dateday');
    
    $errors = 0;
    if(empty($voucher->fk_process_number)){
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('OrderNumber')), 'errors');
        $errors++;
    }
    
    if(empty($voucher->fk_product)){
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Product')), 'errors');
        $errors++;
    }
    
    if(empty($voucher->amount)){
        setEventMessage($langs->trans('ErrorFieldRequired', $langs->transnoentities('Quantity')), 'errors');
        $errors++;
    }
    
    if($voucher->amount > GETPOST('hidden_amount')){
        setEventMessage($langs->trans('AmountMustToBeLess'), 'errors');
        $errors++;
    }
    
    if($errors == 0){
        if($action == 'add'){
            $result = $voucher->create();
        }
        elseif($action == 'edit'){
            $result = $voucher->update($id);
        }
    
        if($result == 0){
            setEventMessage($langs->trans("VoucherSaved"));
            $action = 'list';
        }
        elseif($result == -1){
            setEventMessage($langs->trans('DuplicatedVoucher'), 'errors');
        }
        elseif($result == -3){
            setEventMessage($langs->trans('Error'), 'errors');
        }
    }
    else{
        if($action == 'add'){
            $action = 'create';
        }
        elseif($action == 'edit'){
            $action = 'update';
        }
    }
}
elseif($action == 'delete'){
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('DeleteVoucher'), $langs->trans('ConfirmDeleteVoucher'), 'confirm_delete', '', 'yes', 1);
    $action = 'list';
}
elseif($action == 'confirm_delete'){
    $result = $voucher->delete($id);
    if($result == 0){
        setEventMessage($langs->trans("VoucherDeleted"));
    }
    elseif($result == -3){
        setEventMessage($langs->trans('Error'), 'errors');
    }
    $action = 'list';
}

if($action == 'aprove'){
    $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$id, $langs->trans('AproveVoucher'), $langs->trans('ConfirmAproveVoucher'), 'confirm_aprove_voucher', '', 'yes', 1);
    $action = 'list';
}
elseif($action == 'confirm_aprove_voucher'){
    $voucher->status = 'A';
    $result = $voucher->change_voucher_status($id);
    if($result == 0){
        setEventMessage($langs->trans("VoucherAproved"));
    }
    elseif($result == -3){
        setEventMessage($langs->trans('Error'), 'errors');
    }
    $action = 'list';
}

/**
 * View
 */
print load_fiche_titre($langs->trans("ProductionVoucherArea"),'', 'title_hrm.png');
// adding an process
if($action == 'create'){    
    print '<form action="" method="post" name="form_process_add" id="form_process_add">';
    print '<input type="hidden" name="action" value="add">';
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td>'.$langs->trans('AccountingDate').'</td><td>';
    $date = GETPOST('accounting_dateday') ? GETPOST('accounting_dateyear').'-'.GETPOST('accounting_datemonth').'-'.GETPOST('accounting_dateday') : '';
    print $form->select_date($date, 'accounting_date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
   print '<tr><td class="fieldrequired">'.$langs->trans('ProcessNumber').'</td><td>';
    print $form->select_order_number(GETPOST('fk_process_number'), 'fk_process_number', 'style="width:300px;"', 'C');
    if($conf->use_javascript_ajax){
        print '<script type="text/javascript">';
        print '$(document).ready(function () {
                        $("#fk_process_number").change(function() {
                        	document.form_process_add.action.value="create";
                        	document.form_process_add.submit();
                        });
                     });';
        print '</script>';
    }
    print '</td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('Product').'</td><td>';
    $showempty = 1;
    if(GETPOST('fk_process_number')){
        $showempty = 0;
    }
    print $formprocess->select_production_product(GETPOST('fk_product'), 'fk_product', 'style="width:300px;"', GETPOST('fk_process_number'), $showempty);
    print '</td></tr>';
    $price = 0;
    if(GETPOST('fk_process_number')){
        $voucher->fetch_process_number(GETPOST('fk_process_number'));
        $price = $voucher->completed_amount > 0 ? round(price2num($voucher->real_product_cost / $voucher->completed_amount), 0) : 0;
    }
    print '<tr><td class="fieldrequired">'.$form->textwithtooltip($langs->trans('CompletedAmount'), $langs->trans('QuantityProductionOrderExplanation'), 2, 1, img_picto('', 'info'), '', 2).'</td>';
    print '<td><input type="text" size="30" name="amount" value="'.$voucher->completed_amount.'" />';
    print '<input type="hidden" name="hidden_amount" value="'.$voucher->completed_amount.'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Reference').'</td>';
    print '<td><input type="text" size="30" name="reference" value="'.GETPOST('reference').'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Comment').'</td>';
    print '<td><input type="text" size="30" name="comment" value="'.GETPOST('comment').'" /></td></tr>';
    print '<tr><td>'.$langs->trans('GlossAccountingEntry').'</td>';
    print '<td><input type="text" size="30" name="gloss_accounting_entry" value="'.GETPOST('gloss_accounting_entry').'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Price').'</td>';
    print '<td><input type="text" size="30" name="price" readonly value="'.$price.'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Warehouse').'</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot'), 'fk_entrepot', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td>'.$langs->trans('SerialNumber').'</td>';
    print '<td><input type="text" size="30" name="serial_number" value="'.GETPOST('serial_number').'" /></td></tr>';
    print '<tr><td>'.$langs->trans('ExpirationDate').'</td><td>';
    $date = GETPOST('expiration_date') ? GETPOST('expiration_dateyear').'-'.GETPOST('expiration_datemonth').'-'.GETPOST('expiration_dateday') : '';
    print $form->select_date($date, 'expiration_date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
    print '</div>';
    print '</form>';
}
// list vouchers
elseif(empty ($action) || $action == 'list'){
    $search_entrepot        = GETPOST('search_entrepot');
    $search_serial_number   = GETPOST('search_serial_number');
    $search_process_number  = GETPOST('search_process_number');

    $sortfield = GETPOST('sortfield','alpha');
    $sortorder = GETPOST('sortorder','alpha');
    $page = GETPOST('page','int');
    if ($page == -1) { $page = 0; }
    $offset = $conf->liste_limit * $page;
    $pageprev = $page - 1;
    $pagenext = $page + 1;
    $limit = $conf->liste_limit;
    if (! $sortfield) $sortfield="v.voucher_number";
    if (! $sortorder) $sortorder="DESC";
    
    if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")){ // Both test are required to be compatible with all browsers
	$search_entrepot        = "";
        $search_serial_number   = "";
        $search_process_number  = "";
    }
    
    $sql  = "SELECT v.rowid, v.accounting_date, v.gloss_accounting_entry, v.fk_process_number, v.fk_product, v.fk_entrepot, v.serial_number, ";
    $sql .= "v.expiration_date, v.voucher_number, v.status, e.lieu as entrepot_name, p.label as product_name, pp.order_number, pp.process_type ";
    $sql .= "FROM ".MAIN_DB_PREFIX."production_voucher as v ";
    $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."entrepot as e ON e.rowid = v.fk_entrepot ";
    $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."product as p ON p.rowid = v.fk_product ";
    $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."productive_process as pp ON pp.rowid = v.fk_process_number ";
    $sql .= "WHERE v.entity = ".$conf->entity;
    
    if (!empty($search_entrepot)) $sql .= " AND v.fk_entrepot = '$search_entrepot' ";
    if(!empty($search_serial_number)) $sql .= " AND v.serial_number LIKE '%$search_serial_number%' ";
    if(!empty($search_process_number)) $sql .= " AND v.fk_process_number LIKE '%$search_process_number%'";
    
    $sql .= $db->order($sortfield,$sortorder);
//    echo $sql;
    $result = $db->query($sql);
    
    $param  = "search_entrepot=$search_entrepot&search_serial_number=$search_serial_number";
    
    print $formconfirm;
    
    print '<div class="fichecenter">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<table class="border" width="100%">';
    print '<tr class="liste_titre">';
    print_liste_field_titre($langs->trans("N°"),$_SERVER["PHP_SELF"],"",'','','style="width:5%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans("AccountingDate"),$_SERVER["PHP_SELF"],"v.accounting_date","",$param,'style="width:10%;"',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans("GlossAccountingEntry"),$_SERVER["PHP_SELF"],"","",$param,'style="width:15%;"',$sortfield,$sortorder);
    print_liste_field_titre($langs->trans('ProcessNumber'),$_SERVER["PHP_SELF"],"v.fk_process_number",'',$param,'style="width:10%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Product'),$_SERVER["PHP_SELF"],"product_name",'','','style="width:15%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Warehouse'),$_SERVER["PHP_SELF"],"entrepot_name",'','','style="width:10%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans('SerialNumber'),$_SERVER["PHP_SELF"],"v.serial_number",'','','style="width:10%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans('ExpirationDate'),$_SERVER["PHP_SELF"],"",'','','style="width:10%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre($langs->trans('Status'),$_SERVER["PHP_SELF"],"",'','','style="width:10%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print_liste_field_titre('',$_SERVER["PHP_SELF"],"",'','','style="width:5%;"',$sortfield,$sortorder,'maxwidthsearch ');
    print "</tr>";
    print '<tr class="liste_titre">';
    print '<td class="liste_titre" align="left" colspan="3"></td>';
    print '<td class="liste_titre" align="left">';
    print $form->select_order_number($search_process_number, 'search_process_number', 'style="width:150px;"', 'C');
    print '</td>';
    print '<td class="liste_titre" align="left"></td>';
    print '<td class="liste_titre" align="left">';
    print $form->select_entrepot($search_entrepot, 'search_entrepot', 'style="width:150px;"');
    print '</td>';
    print '<td class="liste_titre" align="left"><input type="text" name="search_serial_number" value="'.$search_serial_number.'" /></td>';
    print '<td class="liste_titre" colspan="2"></td>';
    print '<td class="liste_titre nowrap" align="right">';
    print '<input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans("Search"),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
    print '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans("RemoveFilter"),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
    print '</td>';
    print '</tr>';
    if ($result){
        $var = true;
        $num = $db->num_rows($result);
        $i = 0;
        while ($i < $num){
            $var = !$var;
            $obj = $db->fetch_object($result);
            print '<tr '.$bc[$var].'>';
            print '<td style="width:5%;">'.$obj->voucher_number.'</td>';
            print '<td style="width:10%;">'.$obj->accounting_date.'</td>';
            print '<td style="width:15%;">'.$obj->gloss_accounting_entry.'</td>';
            print '<td style="width:10%;">'.$obj->order_number.' - '.$obj->process_type.'</td>';
            print '<td style="width:15%;">'.$obj->product_name.'</td>';
            print '<td style="width:10%;">'.$obj->entrepot_name.'</td>';
            print '<td style="width:10%;">'.$obj->serial_number.'</td>';
            print '<td style="width:10%;">'.$obj->expiration_date.'</td>';
            if($obj->status == 'P' || $obj->status == NULL){
                $status = $langs->trans('Rest');// Pending
                $img = 'statut1';
            }
            else{
                $status = $langs->trans('Approved');
                $img = 'statut6';
            }
            print '<td style="width:10%;">'.$status.' '.img_picto($langs->trans($status), $img);
            if($obj->status == 'P' || $obj->status == NULL){
                print '<br>';
                print '<a href="./voucher.php?action=aprove&id=' . $obj->rowid . '">';
                print $langs->trans('ApproveVoucher');
                print '</a>';
            }
            print '</td>';
            print '<td style="width:5%;">';
            if($user->rights->production->write){
                print '<a href="./voucher.php?action=update&id=' . $obj->rowid . '">';
                print img_edit();
                print '</a>';
            }
            if($user->rights->production->delete){
                print '<a href="./voucher.php?action=delete&id=' . $obj->rowid . '">';
                print img_delete();
                print '</a>';
            }
            print '</td></tr>';
            $i++;
        }
    }
    print "</table>";
    print '</form>';
    print '</div>';
    if($user->rights->production->write){
        print '<form action="' . DOL_URL_ROOT . '/production/voucher.php?leftmenu=product&amp;mainmenu=production&amp;action=create" method="post">';
        print '<input type="hidden" name="action" value="create">';
        print '<p><input type="submit" class="button" value="'.$langs->trans('NewVoucher').'"></p>';
        print '</form>';
    }
}
// adding a voucher
elseif($action == 'update'){
    print '<form action="" method="post" name="formprocessedit" id="formprocessedit">';
    print '<input type="hidden" name="action" value="edit">';
    print '<div class="tabBar">';
    print '<table class="border" width="100%">';
    print '<tr><td>'.$langs->trans('AccountingDate').'</td><td>';
    $date = GETPOST('accounting_dateday') ? GETPOST('accounting_dateyear').'-'.GETPOST('accounting_datemonth').'-'.GETPOST('accounting_dateday') : $voucher->accounting_date;
    print $form->select_date($date, 'accounting_date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('ProcessNumber').'</td><td>';
    print $form->select_order_number(GETPOST('fk_process_number') ? GETPOST('fk_process_number') : $voucher->fk_process_number, 'fk_process_number', 'style="width:300px;"', 'C');
    print '</td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('Product').'</td><td>';
    print $formprocess->select_production_product(GETPOST('fk_product') ? GETPOST('fk_product') : $voucher->fk_product, 'fk_product', 'style="width:300px;"', $voucher->fk_process_number ? $voucher->fk_process_number : GETPOST('fk_process_number'), 0);
    print '</td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('CompletedAmount').'</td>';
    print '<td><input type="text" size="30" name="amount" value="'.(GETPOST('amount') ? GETPOST('amount') : $voucher->amount).'" />';
    print '<input type="hidden" name="hidden_amount" value="'.$voucher->amount.'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Reference').'</td>';
    print '<td><input type="text" size="30" name="reference" value="'.(GETPOST('reference') ? GETPOST('reference') : $voucher->reference).'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Comment').'</td>';
    print '<td><input type="text" size="30" name="comment" value="'.(GETPOST('comment') ? GETPOST('comment') : $voucher->comment).'" /></td></tr>';
    print '<tr><td>'.$langs->trans('GlossAccountingEntry').'</td>';
    print '<td><input type="text" size="30" name="gloss_accounting_entry" value="'.(GETPOST('gloss_accounting_entry') ? GETPOST('gloss_accounting_entry') : $voucher->gloss_accounting_entry).'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Price').'</td>';
    print '<td><input type="text" size="30" name="price" value="'.(GETPOST('price') ? GETPOST('price') : $voucher->price).'" /></td></tr>';
    print '<tr><td>'.$langs->trans('Warehouse').'</td><td>';
    print $form->select_entrepot(GETPOST('fk_entrepot') ? GETPOST('fk_entrepot') : $voucher->fk_entrepot, 'fk_entrepot', 'style="width:300px;"');
    print '</td></tr>';
    print '<tr><td>'.$langs->trans('SerialNumber').'</td>';
    print '<td><input type="text" size="30" name="serial_number" value="'.(GETPOST('serial_number') ? GETPOST('serial_number') : $voucher->serial_number).'" /></td></tr>';
    print '<tr><td>'.$langs->trans('ExpirationDate').'</td><td>';
    $date = GETPOST('expiration_date') ? GETPOST('expiration_dateyear').'-'.GETPOST('expiration_datemonth').'-'.GETPOST('expiration_dateday') : $voucher->expiration_date;
    print $form->select_date($date, 'expiration_date', 0, 0, 0, 'notused', 1, 1, 1, 0);
    print '</td></tr>';
    print "</table>";
    print '</div>';
    print '<div class="center">';
    print '<input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
    print '</div>';
    print '</form>';
}

llxFooter();
$db->close();