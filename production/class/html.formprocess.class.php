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
class FormProcess extends Form
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
     * Returns a list of status_process
     * 
     * @param Integer $selected
     * @param String $htmlname
     * @param String $htmloption Some html options to add in the html tag
     * @return String HTML select tag
     */
    function select_status_process($selected, $htmlname='status_process', $htmloption = '')
    {
        global $langs;
        
        $out = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$htmloption.'>';
        $out .= '<option value="">--</option>';
        $out .= '<option value="O" '.($selected == 'O' ? 'selected' : '').'>'.$langs->trans('Open').'</option>';
        $out .= '<option value="C" '.($selected == 'C' ? 'selected' : '').'>'.$langs->trans('Closed').'</option>';
        $out .= '</select>';
        
        return $out;
        

    }
   
       
    /**
     * Returns a list of materials
     * 
     * @param Integer $selected
     * @param String $htmlname
     * @param String $htmloption Some html options to add in the html tag
     * @return String HTML select tag
     */
    function select_material($selected, $htmlname='material', $htmloption = '', $productid = '')
    {
        global $conf;
        $sql = "SELECT rowid, code, description FROM ".MAIN_DB_PREFIX."production_material WHERE entity = '".$conf->entity."'";
        if(strlen($productid)){
            $sql .= " AND rowid IN (SELECT fk_material FROM ".MAIN_DB_PREFIX."product_material WHERE fk_product = $productid)";
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
                    $out .= '<option value="'.$obj->rowid.'" '.$sel.'>'.$obj->code.' - '.$obj->description.'</option>';
                    
                    $i++;
                }
                $out .= '</select>';
            }
            else{
                global $langs;
                $langs->load('compta');
                $out = '<font class="error">'.$langs->trans('MaterialNotFound').'</font>';
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
    
    /**
     * Returns a list of product
     * 
     * @param Integer $selected
     * @param String $htmlname
     * @param String $htmloption Some html options to add in the html tag
     * @param String $order_number Order production number
     * @param Integer $showempty 0: does not show an empty option; 1: shows an empty option
     * @return String HTML select tag
     */
    function select_production_product($selected, $htmlname='product', $htmloption = '', $order_number = '', $showempty = 1)
    {
        global $conf;
        $sql  = "SELECT pp.rowid, p.ref, p.label, pp.amount ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_product as pp ";
        $sql .= "INNER JOIN ".MAIN_DB_PREFIX."product as p ON p.rowid = pp.fk_product ";
        $sql .= "WHERE pp.entity = '".$conf->entity."'";
        if(strlen($order_number)){
            $sql .= " AND pp.rowid = (SELECT fk_product FROM ".MAIN_DB_PREFIX."productive_process WHERE rowid = $order_number)";
        }

        //echo $sql;
        dol_syslog(get_class($this)."::select_production_product", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql){
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num){
                $out = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$htmloption.'>';
                if($showempty == 1){
                    $out .= '<option value="">&nbsp;</option>';
                }
                while ($i < $num){
                    $obj = $this->db->fetch_object($resql);
                    
                    $sel = "";
                    if ($selected && $selected == $obj->rowid){
                        $sel = "selected";
                    }
                    $out .= '<option value="'.$obj->rowid.'" '.$sel.'>'.$obj->ref.' - '.$obj->label.'- Stock: '.$obj->amount.'</option>';
                    
                    $i++;
                }
                $out .= '</select>';         
            }
            else{
                global $langs;
                $out = '<font class="error">'.$langs->trans('ProductNotFound').'</font>';
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
    
  function select_order_production($selected, $htmlname='rowid', $htmloption = '', $showempty = 1)
    {
      
        global $conf;
        $sql  = "SELECT rowid,ref, process_type FROM ".MAIN_DB_PREFIX."productive_process WHERE entity = '".$conf->entity."'";
       //echo $sql;
        dol_syslog(get_class($this)."::select_productive_process", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql){
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num){
                $out = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$htmloption.'>';
                if($showempty == 1){
                    $out .= '<option value="">&nbsp;</option>';
                }
                while ($i < $num){
                    $obj = $this->db->fetch_object($resql);
                    
                    $sel = "";
                    if ($selected && $selected == $obj->rowid){
                        $sel = "selected";
                    }
                    $out .= '<option value="'.$obj->rowid.'" '.$sel.'>'.$obj->ref.' - '.$obj->process_type.'</option>';
                    
                    $i++;
                }
                $out .= '</select>';
            }
            else{
                global $langs;
                $out = '<font class="error">'.$langs->trans('OrderNotFound').'</font>';
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