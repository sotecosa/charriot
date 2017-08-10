<?php
/* Copyright (c) 2015  Henry Seron    <henryseronbe@yahoo.com>
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
 *	\file       htdocs/production/class/html.formproduction.class.php
 *      \ingroup    production
 *	\brief      File of class with all html predefined components
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
/**
 *	Class to manage generation of HTML components
 *	Only common components must be here.
 */
class FormProductionVoucher extends Form
{
    /**
     * Constructor
     *
     * @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        parent::__construct($db);
    }
    
    /**
     * Returns a list process order number
     * 
     * @param Integer $selected
     * @param String $htmlname
     * @param String $htmloption Some html options to add in the html tag
     * @param String $status O: Open; C: Closed
     * @return String HTML select tag
     */
    function select_order_number($selected, $htmlname='order_number', $htmloption = '', $status = '')
    {
        global $conf;
        $sql = "SELECT rowid, order_number, ref FROM ".MAIN_DB_PREFIX."productive_process WHERE entity = '".$conf->entity."'";
        if(strlen($status)){
            $sql .= " AND status = '$status'";
        }
        //echo $sql;
        dol_syslog(get_class($this)."::select_material", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql){
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num){
                $out = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$htmloption.'>';
                $out .= '<option value="">&nbsp;</option>';
                while ($i < $num){
                    $obj = $this->db->fetch_object($resql);
                    
                    $sel = "";
                    if ($selected && $selected == $obj->rowid){
                        $sel = "selected";
                    }
                    $out .= '<option value="'.$obj->rowid.'" '.$sel.'>'.$obj->order_number.' - '.$obj->ref.'</option>';
                    
                    $i++;
                }
                $out .= '</select>';
            }
            else{
                global $langs;
                $langs->load('compta');
                $out = '<font class="error">'.$langs->trans('OrderNumberNotFound').'</font>';
            }
        }
        else{
            dol_print_error($this->db);
        }
        
        // Make select dynamic
        include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        $out .= ajax_combobox($htmlname);
        
        return $out;
    }
}