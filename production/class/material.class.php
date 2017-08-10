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
 *   \file       htdocs/material/class/material.class.php
 *   \brief      Salary
 *   \ingroup    material
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Description of material
 *
 * @author henryseron
 */
class ProductionMaterial extends CommonObject {
    
    var $type;

    var $code;
    
    var $fk_product;
    
    var $description;
    
    var $amount;
    
    var $fk_unit_metric;
    
    var $price;
    
    var $total;
    
    var $emission_method;
    
    var $comment;
    
    
    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db){
        $this->db = $db;
    }
    
    /**
     * Save a new product material
     * 
     * @return Integer -1: material already registered. -3: some db error. 0: material was sucessfully saved 
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $verify = $this->checkDuplicatedEntries();
        
        if($verify == 0){
            global $conf;
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."production_material (rowid, type, code, fk_product, "
                    . "description, amount, fk_unit_metric, price, total, emission_method, comment, entity) "
                    . "VALUES (NULL, ".$this->type.", ".$this->code.", ".$this->fk_product.", ".$this->description.", "
                    . "".$this->amount.", ".$this->fk_unit_metric.", ".$this->price.", ".$this->total.", "
                    . "".$this->emission_method.", ".$this->comment.", '".$conf->entity."')";
//            echo $sql;
            dol_syslog(get_class($this)."::save product material success");
            $result = $this->db->query($sql);
            if($result){
                $id = $this->db->last_insert_id(MAIN_DB_PREFIX."product_material");
                $this->db->commit();
                return $id;
            }
            else {
                $this->db->rollback();
                return -3;
            }
        }
        else{
            return $verify;
        }
    }
    
    /**
     * 
     * @return Integer -1: material already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries(){
        $this->db->begin();
        
        dol_syslog(get_class($this)."::checkDuplicatedEntries", LOG_DEBUG);
        global $conf;
        
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."production_material WHERE type = ".$this->type." AND "
                . "code = ".$this->code." AND fk_product = ".$this->fk_product." AND "
                . "description = ".$this->description." AND amount = ".$this->amount." AND "
                . "fk_unit_metric = ".$this->fk_unit_metric." AND price = ".$this->price." AND "
                . "total = ".$this->total." AND emission_method = ".$this->emission_method." AND "
                . "comment = ".$this->comment." AND entity = '".$conf->entity."'";
        $result = $this->db->query($sql);
//        echo $sql;
        if($result){
            $numRows = $this->db->num_rows($result);
            $this->db->commit();
            if($numRows > 0){ // name already registered
                return -1;
            }
        }
        else {
            $this->db->rollback();
            return -3; // some db error
        }
        
        return 0; // material is not registered
    }

    private function cleanParameters(){
        $this->type = !empty($this->type) ? "'".trim($this->type)."'" : 'NULL';
        $this->code = !empty($this->code) ? "'".trim($this->code)."'" : 'NULL';
        $this->fk_product = !empty($this->fk_product) && $this->fk_product != 0 ? "'".$this->fk_product."'" : 'NULL';
        $this->description = !empty($this->description) ? "'".trim($this->description)."'" : 'NULL';
        $this->amount = !empty($this->amount) ? "'".$this->amount."'" : 0;
        $this->fk_unit_metric = $this->fk_unit_metric != 0 ? "'".$this->fk_unit_metric."'" : 'NULL';
        $this->price = !empty($this->price) ? "'".price2num($this->price)."'" : 0;
        $this->total = $this->total != 0 ? "'".price2num($this->total)."'" : 0;
        $this->comment = !empty($this->comment) ? "'".$this->comment."'" : 'NULL';
        $this->emission_method = !empty($this->emission_method) ? "'".$this->emission_method."'" : 'NULL';
    }
    
    /**
     * Delete a product material
     * 
     * @return Integer -3: some db error. 0: material was sucessfully deleted
     */
    public function delete1($id) {
        $this->db->begin();
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."production_material WHERE rowid = '".$id."'";
        //echo $sql;
        dol_syslog(get_class($this)."::delete material success rowid=".$id);
        $result = $this->db->query($sql);
        if($result){
            $this->db->commit();
            return 0;
        }
        else {
            $this->db->rollback();
            return -3;
        }
    }
    
    
        public function deleteline ($id,$idline) {
        $this->db->begin();
        
        $sql= "DELETE FROM ".MAIN_DB_PREFIX."product_material WHERE fk_product = ".$id." and fk_material=".$idline;
        dol_syslog(get_class($this)."::delete material success fk_product = ".$id." and fk_material=".$idline);
        $result = $this->db->query($sql);
       //echo $sql;
        
        if($result>0){
            
            dol_syslog(get_class($this)."::delete material success lineid = ".$id);
            $sqlline = "DELETE FROM ".MAIN_DB_PREFIX."production_material WHERE rowid =".$idline;
            //echo $sqlline;
            //echo "<script>alert('".$sqlline."');</script>";
            $resultline = $this->db->query($sqlline);
            if($resultline){
                $this->db->commit();
                return 0;
            }else {
                $this->db->rollback();
                return -3;
            }
        }
    }
    
    /**
     * Returns  the product material details
     * 
     * @param Integer $id material id
     */
    public function fetch($id) {
        $sql  = "SELECT type, code, fk_product, description, amount, fk_unit_metric, price, total, emission_method, comment ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_material WHERE rowid = $id";
       // echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            
            $this->type             = $obj->type;
            $this->code             = $obj->code;
            $this->fk_product       = $obj->fk_product;
            $this->description      = $obj->description;
            $this->amount           = amount2num($obj->amount);
            $this->fk_unit_metric   = $obj->fk_unit_metric;
            $this->price            = $obj->price;
            $this->total            = $obj->total;
            $this->emission_method  = $obj->emission_method;
            $this->comment          = $obj->comment;
        }
    }
       
    /**
     * Update a product material
     * 
     * @return Integer -3: some db error. 0: material was sucessfully upamountd
     */
    public function update($id) {
        // Clean parameters
        //$this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_material SET type = ".$this->type.", code = ".$this->code.", ";
        $sql .= "fk_product = ".$this->fk_product.", description = ".$this->description.", amount = ".$this->amount.", ";
        $sql .= "fk_unit_metric = ".$this->fk_unit_metric.", price = ".$this->price.", ";
        $sql .= "total = ".$this->total.", emission_method = ".$this->emission_method.", comment = ".$this->comment." WHERE rowid = '$id'";
    

        //echo $sql;
        dol_syslog(get_class($this)."::update product material success rowid=".$id);
        $result = $this->db->query($sql);
        if($result){
            $this->db->commit();
            return 0;
        }
        else {
            $this->db->rollback();
            return -3;
        }
    }
    
    /**
     * Returns  the production product materials
     * 
     * @param Integer $id production id
     */
    public function fetch_product_materials($productid) {
        $sql  = "SELECT p.rowid, p.type, p.code,p.fk_product, p.description, p.amount, p.price, p.total, p.emission_method, p.comment, c.label as unit_name ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_material as p ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."c_units as c ON c.rowid =  p.fk_unit_metric ";
        $sql .= "WHERE p.rowid IN (SELECT fk_material FROM ".MAIN_DB_PREFIX."product_material WHERE fk_product = $productid)";
    //   echo $sql;
      
        $result = $this->db->query($sql);
        $list = array();
        if ($result){
            $num = $this->db->num_rows($result);
            $i = 0;
            while($i < $num){
  
                $obj = $this->db->fetch_object($result);
                $list[$i]['rowid']               = $obj->rowid;
                $list[$i]['type']               = $obj->type;
                $list[$i]['fk_product']         = $obj->fk_product;
                $list[$i]['code']               = $obj->code;
                $list[$i]['description']        = $obj->description;
                $list[$i]['amount']             = $obj->amount;
                $list[$i]['price']              = $obj->price;
                $list[$i]['total']              = $obj->total;
                $list[$i]['emission_method']    = $obj->emission_method;
                $list[$i]['comment']            = $obj->comment;
                $list[$i]['unit_name']          = $obj->unit_name;
                $i++;
            }
        }
        else{
            $this->db->rollback();
            return -3;
        }
        
        return $list;
    }
    
    /**
     * Returns  the production product materials value
     * 
     * @param Integer $id production id
     */
    public function fetch_product_materials_value($productid) {
        $sql  = "SELECT SUM(total) as total FROM ".MAIN_DB_PREFIX."production_material ";
        $sql .= "WHERE rowid IN (SELECT fk_material FROM ".MAIN_DB_PREFIX."product_material WHERE fk_product = $productid)";
//        echo $sql;
        $result = $this->db->query($sql);
        if ($result){
            $obj = $this->db->fetch_object($result);
            $total = $obj->total;
        }
        else{
            $this->db->rollback();
            return -3;
        }
        
        return $total;
    }
    
    
        public function edit_material_production_line($id) {
        // Clean parameters
        //$this->cleanParameters();
        
        $this->db->begin();
        
        //var_dump($this);
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_material SET  code = '".$this->code."', description = '".$this->description."', ";
        $sql .= " amount = '".$this->amount."', price= '".$this->price."', total= '".$this->total."', comment = '".$this->comment."' ";
       $sql .= " WHERE rowid = '".$id."'";
       
      
      // var_dump($this);
       
      // echo $sql;  
        dol_syslog(get_class($this)."::update production material success rowid=".$id);
        $result = $this->db->query($sql);
        if($result){
            $this->db->commit();
            return 0;
        }
        else {
            $this->db->rollback();
            return -3;
        }
    }
}

class ProductionProductMaterial extends CommonObject {
    
    var $fk_product;
    
    var $fk_material;
    
    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db){
        $this->db = $db;
    }
    
    /**
     * Save a new production product material
     * 
     * @return Integer -1: product material already registered. -3: some db error. 0: product material was sucessfully saved 
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $verify = $this->checkDuplicatedEntries();
        
        if($verify == 0){
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."product_material (fk_product, fk_material) "
                    . "VALUES (".$this->fk_product.", ".$this->fk_material.")";
//            echo $sql;
            dol_syslog(get_class($this)."::save product material success");
            $result = $this->db->query($sql);
            if($result){
                $this->db->commit();
                return 0;
            }
            else {
                $this->db->rollback();
                return -3;
            }
        }
        else{
            return $verify;
        }
    }
    
    /**
     * 
     * @return Integer -1: product material already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries(){
        $this->db->begin();
        
        dol_syslog(get_class($this)."::checkDuplicatedEntries", LOG_DEBUG);
        
        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."product_material WHERE fk_product = ".$this->fk_product." AND "
                . "fk_material = ".$this->fk_material;
        $result = $this->db->query($sql);
//        echo $sql;
        if($result){
            $numRows = $this->db->num_rows($result);
            $this->db->commit();
            if($numRows > 0){ // name already registered
                return -1;
            }
        }
        else {
            $this->db->rollback();
            return -3; // some db error
        }
        
        return 0; // material is not registered
    }
    
    private function cleanParameters(){
        $this->fk_product = !empty($this->fk_product) ? "'".trim($this->fk_product)."'" : 'NULL';
        $this->fk_material = !empty($this->fk_material) ? "'".trim($this->fk_material)."'" : 'NULL';
    }

    }