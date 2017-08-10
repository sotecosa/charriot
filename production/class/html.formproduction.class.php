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
class FormProduction extends Form
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
     * Returns a list of type of materials
     * 
     * @param Integer $selected
     * @param String $htmlname
     * @param String $htmloption Some html options to add in the html tag
     * @return String HTML select tag
     */
    function select_material_type($selected, $htmlname='material_type', $htmloption = '')
    {
        global $langs;
        
        $out = '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$htmloption.'>';
        $out .= '<option value="">--</option>';
        $out .= '<option value="PP" '.($selected == 'PP' ? 'selected' : '').'>'.$langs->trans('PP').'</option>';
        $out .= '<option value="PF" '.($selected == 'PF' ? 'selected' : '').'>'.$langs->trans('PF').'</option>';
        $out .= '</select>';
        
        return $out;
    }
}