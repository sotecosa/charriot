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
 *   \file       htdocs/process/class/component.class.php
 *   \brief      Production
 *   \ingroup    process
 */
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 * Description of component
 *
 * @author henryseron
 */
class ProductionComponent extends CommonObject {

    var $fk_material;
    var $amount;
    var $required_amount;
    var $stock;
    var $fk_entrepot;
    var $emission_method;
    var $comment;

    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Save a new component
     * 
     * @return Integer  >0 Component ID. -1: component already registered. -3: some db error. 0: component was sucessfully saved 
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();

        $this->db->begin();

        $verify = $this->checkDuplicatedEntries();

        if ($verify == 0) {
            global $conf;
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "process_components (rowid, fk_material, amount, required_amount, "
                    . "stock, fk_entrepot, emission_method, comment, entity) "
                    . "VALUES (NULL, " . $this->fk_material . ", " . $this->amount . ", " . $this->required_amount . ", " . $this->stock . ", "
                    . "" . $this->fk_entrepot . ", " . $this->emission_method . ", " . $this->comment . ", '" . $conf->entity . "')";
//            echo $sql;
            dol_syslog(get_class($this) . "::save component success");
            $result = $this->db->query($sql);
            if ($result) {
                $id = $this->db->last_insert_id(MAIN_DB_PREFIX . "process_components");
                $this->db->commit();
                return $id;
            } else {
                $this->db->rollback();
                return -3;
            }
        } else {
            return $verify;
        }
    }

    /**
     * 
     * @return Integer -1: component already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries() {
//        $this->db->begin();
//        
//        dol_syslog(get_class($this)."::checkDuplicatedEntries", LOG_DEBUG);
//        global $conf;
//        
//        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."process_components WHERE type = ".$this->type." AND "
//                . "required_amount = ".$this->required_amount." AND fk_material = ".$this->fk_material." AND "
//                . "description = ".$this->description." AND amount = ".$this->amount." AND "
//                . "fk_entrepot = ".$this->fk_entrepot." AND stock = ".$this->stock." AND "
//                . "total = ".$this->total." AND emission_method = ".$this->emission_method." AND "
//                . "comment = ".$this->comment." AND entity = '".$conf->entity."'";
//        $result = $this->db->query($sql);
////        echo $sql;
//        if($result){
//            $numRows = $this->db->num_rows($result);
//            $this->db->commit();
//            if($numRows > 0){ // name already registered
//                return -1;
//            }
//        }
//        else {
//            $this->db->rollback();
//            return -3; // some db error
//        }

        return 0; // component is not registered
    }

    private function cleanParameters() {
        $this->fk_material = !empty($this->fk_material) && $this->fk_material != 0 ? "'" . $this->fk_material . "'" : 'NULL';
        $this->amount = !empty($this->amount) ? "'" . $this->amount . "'" : 0;
        $this->required_amount = !empty($this->required_amount) ? "'" . $this->required_amount . "'" : 0;
        $this->stock = !empty($this->stock) ? "'" . $this->stock . "'" : 0;
        $this->fk_entrepot = $this->fk_entrepot != 0 ? "'" . $this->fk_entrepot . "'" : 'NULL';
        $this->emission_method = !empty($this->emission_method) ? "'" . $this->emission_method . "'" : 'NULL';
        $this->comment = !empty($this->comment) ? "'" . $this->comment . "'" : 'NULL';
    }

    /**
     * Delete a component
     * 
     * @return Integer -3: some db error. 0: component was sucessfully deleted
     */
    public function delete($id) {
        $this->db->begin();
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "process_component WHERE fk_component = $id";
//        echo $sql;
        $result = $this->db->query($sql);
        if ($result) {
            $sql = "DELETE FROM " . MAIN_DB_PREFIX . "process_components WHERE fk_component = $id";
            $result = $this->db->query($sql);
            if ($result) {
                dol_syslog(get_class($this) . "::delete component success rowid=" . $id);
                $this->db->commit();
                return 0;
            } else {
                $this->db->rollback();
                return -3;
            }
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    /**
     * Returns  the component details
     * 
     * @param Integer $id component id
     */
    public function fetch($id) {
        $sql = "SELECT fk_material, amount, required_amount, stock, fk_entrepot, emission_method, comment ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "process_components WHERE rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);

        if ($result) {
            $obj = $this->db->fetch_object($result);

            $this->fk_material = $obj->fk_material;
            $this->amount = $obj->amount;
            $this->required_amount = $obj->required_amount;
            $this->stock = $obj->stock;
            $this->fk_entrepot = $obj->fk_entrepot;
            $this->emission_method = $obj->emission_method;
            $this->comment = $obj->comment;
        }
    }

    /**
     * Update a component
     * 
     * @return Integer -3: some db error. 0: component was sucessfully upamountd
     */
    public function update($id) {
        // Clean parameters
        $this->cleanParameters();

        $this->db->begin();

        $sql = "UPDATE " . MAIN_DB_PREFIX . "process_components SET fk_material = " . $this->fk_material . ", amount = " . $this->amount . ", ";
        $sql .= "required_amount = " . $this->required_amount . ", stock = " . $this->stock . ", ";
        $sql .= "fk_entrepot = " . $this->fk_entrepot . ", emission_method = " . $this->emission_method . ", comment = " . $this->comment . " ";
        $sql .= "WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this) . "::update component success rowid=" . $id);
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 0;
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    /**
     * Returns the process components
     * 
     * @param Integer $processid process id
     */
    public function fetch_process_components($processid) {
        $sql = "SELECT pc.fk_material,pc.fk_entrepot, pc.rowid, m.code, m.description, pc.amount, pc.required_amount, pc.stock,  pc.comment, e.lieu as entrepot_name ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "process_components as pc ";
        $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "production_material as m ON m.rowid =  pc.fk_material ";
        $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "entrepot as e ON e.rowid =  pc.fk_entrepot ";
        $sql .= "WHERE pc.rowid IN (SELECT fk_component FROM " . MAIN_DB_PREFIX . "process_component WHERE fk_process = $processid)";
        // echo $sql;
        $result = $this->db->query($sql);
        $list = array();
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $list[$i]['material'] = $obj->code . ' - ' . $obj->description;
                $list[$i]['fk_material'] = $obj->fk_material;
                $list[$i]['fk_entrepot'] = $obj->fk_entrepot;
                $list[$i]['amount'] = $obj->amount;
                $list[$i]['required_amount'] = $obj->required_amount;
                $list[$i]['stock'] = $obj->stock;
                //    $list[$i]['emission_method']    = $obj->emission_method;
                $list[$i]['comment'] = $obj->comment;
                $list[$i]['entrepot_name'] = $obj->entrepot_name;
                $list[$i]['rowid'] = $obj->rowid;
                $i++;
            }
        } else {
            $this->db->rollback();
            return -3;
        }

        return $list;
    }

    /**
     * Returns  the productive process planned amount
     * 
     * @param Integer $id process id
     */
    public function fetch_productive_process_planned_amount($processid) {
        $sql = "SELECT planned_amount FROM " . MAIN_DB_PREFIX . "productive_process WHERE rowid  = $processid";
        //echo $sql;
        $result = $this->db->query($sql);
        if ($result) {
            $obj = $this->db->fetch_object($result);
            $planned_amount = $obj->planned_amount;
            $this->db->commit();
        } else {
            $this->db->rollback();
            return -3;
        }

        return $planned_amount;
    }

    public function edit_line_component1($id) {
        // Clean parameters
        //$this->cleanParameters();

        $this->db->begin();

        $sql = "UPDATE " . MAIN_DB_PREFIX . "process_components SET fk_material = " . $this->fk_material . ", amount = " . $this->amount . ", ";
        $sql .= "required_amount = " . $this->required_amount . ", stock = " . $this->stock . ", ";
        $sql .= "fk_entrepot = " . $this->fk_entrepot . ",comment = '" . $this->comment . "' ";
        $sql .= "WHERE rowid = '" . $id . "'";

        // var_dump($this);
        //echo $sql;  
        dol_syslog(get_class($this) . "::update process components success rowid=" . $id);
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 0;
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    public function delete1($id) {
        $this->db->begin();
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "process_components WHERE rowid = '" . $id . "'";
        //echo "--DELETE (1)-->". $sql;
        dol_syslog(get_class($this) . "::delete process components rowid=" . $id);
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 0;
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    public function deleteline($id, $idline) {
        $this->db->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "process_component WHERE fk_process = " . $id . " and fk_component=" . $idline;
        dol_syslog(get_class($this) . "::delete process components fk_process = " . $id . " and fk_component=" . $idline);
        $result = $this->db->query($sql);
        //echo "---DELETELINE-->". $sql;

        if ($result > 0) {

            dol_syslog(get_class($this) . "::delete component success lineid = " . $id);
            $sqlline = "DELETE FROM " . MAIN_DB_PREFIX . "process_components WHERE rowid =" . $idline;
            //echo "--DELETESQLLINE-->". $sqlline;
            //echo "<script>alert('".$sqlline."');</script>";
            $resultline = $this->db->query($sqlline);
            if ($resultline) {
                $this->db->commit();
                return 0;
            } else {
                $this->db->rollback();
                return -3;
            }
        }
    }

    public function material_amount($productid) {
        $sql = "SELECT p.rowid, p.type, p.code,p.fk_product, p.description, p.amount, p.price, p.total, p.emission_method, p.comment, c.label as unit_name ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "production_material as p ";
        $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "c_units as c ON c.rowid =  p.fk_unit_metric ";
        $sql .= "WHERE p.rowid IN (SELECT fk_material FROM " . MAIN_DB_PREFIX . "product_material WHERE fk_product = $productid)";
        //echo $sql;
        $result = $this->db->query($sql);
        $list = array();
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($result);
                $list[$i]['rowid'] = $obj->rowid;
                $list[$i]['type'] = $obj->type;
                $list[$i]['fk_product'] = $obj->fk_product;
                $list[$i]['code'] = $obj->code;
                $list[$i]['description'] = $obj->description;
                $list[$i]['amount'] = $obj->amount;
                $list[$i]['price'] = $obj->price;
                $list[$i]['total'] = $obj->total;
                $list[$i]['emission_method'] = $obj->emission_method;
                $list[$i]['comment'] = $obj->comment;
                $list[$i]['unit_name'] = $obj->unit_name;
                $i++;
            }
        } else {
            $this->db->rollback();
            return -3;
        }

        return $list;
    }

    public function count_product_stock($productid, $producamo) {
        $sql = "SELECT prm.fk_product AS product, ps.stock as stock,ps.description as name, prm.amount";
        $sql .= " FROM " . MAIN_DB_PREFIX . "product_material as pm";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "production_material as prm ON prm.rowid = pm.fk_material";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product as ps ON ps.rowid = prm.fk_product";
        $sql .= " WHERE pm.fk_product = " . $productid . "";
        //
        //echo $sql;
        $result = $this->db->query($sql);
        $listps = array();
        if ($result) {
            $num = $this->db->num_rows($result);
            $i = 0;
            while ($i < $num) {
                $objsp = $this->db->fetch_object($result);

                $listps[$objsp->product]['stock'] = $objsp->stock;
                $listps[$objsp->product]['fk_product'] = $objsp->fk_product;
                $listps[$objsp->product]['amount'] = $objsp->amount;

                /****RESTA DE LOS STOCK**/
                $restmat = ($objsp->stock - $objsp->amount);

                
                
              //  echo "-->stock".$objsp->stock;
               // echo "-->amount".$objsp->amount;
               // echo "-->restmat".$restmat;
               // var_dump($objsp->stock);
              //  var_dump($objsp->amount);
                //var_dump($restmat);
                
                
                
                 // $sqldo =  "UPDATE  " . MAIN_DB_PREFIX . "product_stock  SET reel =  $restmat   WHERE fk_product = $objsp->product ";
                   //echo $sqldo;
                $i++;
            }


            $sqlpp = "SELECT  pp.fk_product , p.stock, pp.amount, pp.rowid, p.ref, p.label ";
            $sqlpp .= "FROM " . MAIN_DB_PREFIX . "production_product as pp ";
            $sqlpp .= "INNER JOIN " . MAIN_DB_PREFIX . "product as p ON p.rowid = pp.fk_product ";
            $sqlpp .= "WHERE pp.entity = 3";
           // echo $sqlpp;
            $result2 = $this->db->query($sqlpp);
            if ($result2) {

                $objpp = $this->db->fetch_object($result2);

                
                /**********SUMA DE LOS MATERIALES**/
                $sumcabe = ($objpp->amount + $objpp->stock);
//var_dump($sumcabe);
            //     $sqlup = "UPDATE  " . MAIN_DB_PREFIX . "product_stock  SET reel =  $sumcabe   WHERE fk_product =$objpp->fk_product ";
                 
             //    echo $sqlup;
                  
            }

        } else {
            $this->db->rollback();
            return -3;
        }

        return $listps;
    }

}

class ProductionProcessComponent extends CommonObject {

    var $fk_process;
    var $fk_component;

    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Save a new process component
     * 
     * @return Integer -1: component already registered. -3: some db error. 0: component was sucessfully saved 
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();

        $this->db->begin();

        $verify = $this->checkDuplicatedEntries();

        if ($verify == 0) {
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "process_component (fk_process, fk_component) "
                    . "VALUES (" . $this->fk_process . ", " . $this->fk_component . ")";
//            echo $sql;
            dol_syslog(get_class($this) . "::save process component success");
            $result = $this->db->query($sql);
            if ($result) {
                $this->db->commit();
                return 0;
            } else {
                $this->db->rollback();
                return -3;
            }
        } else {
            return $verify;
        }
    }

    /**
     * 
     * @return Integer -1: component already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries() {
        $this->db->begin();

        dol_syslog(get_class($this) . "::checkDuplicatedEntries", LOG_DEBUG);

        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "process_component WHERE fk_process = " . $this->fk_process . " AND "
                . "fk_component = " . $this->fk_component;
        $result = $this->db->query($sql);
//        echo $sql;
        if ($result) {
            $numRows = $this->db->num_rows($result);
            $this->db->commit();
            if ($numRows > 0) { // name already registered
                return -1;
            }
        } else {
            $this->db->rollback();
            return -3; // some db error
        }

        return 0; // component is not registered
    }

    private function cleanParameters() {
        $this->fk_process = !empty($this->fk_process) ? "'" . trim($this->fk_process) . "'" : 'NULL';
        $this->fk_component = !empty($this->fk_component) ? "'" . trim($this->fk_component) . "'" : 'NULL';
    }

}
