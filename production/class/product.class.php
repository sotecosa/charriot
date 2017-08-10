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
 *   \file       htdocs/production/class/production.class.php
 *   \brief      Salary
 *   \ingroup    production
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Description of production
 *
 * @author henryseron
 */
class ProductionProduct extends CommonObject {
    
    var $fk_product;

    var $material_type;
    
    var $amount;
    
    var $fk_entrepot;
    
    var $price;
    
    var $fk_accounting_account;
    
    var $fk_profit_center;
    
    var $fk_project;
    
    
    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db){
        $this->db = $db;
    }
    
    /**
     * Save a new production product
     * 
     * @return Integer -1: production already registered. -3: some db error. 0: production was sucessfully saved 
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $verify = $this->checkDuplicatedEntries();
        
        if($verify == 0){
            global $conf;
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."production_product (rowid, fk_product, material_type, amount, "
                    . "fk_entrepot, price, fk_accounting_account, fk_profit_center, fk_project, entity) "
                    . "VALUES (NULL, ".$this->fk_product.", ".$this->material_type.", ".$this->amount.", ".$this->fk_entrepot.", "
                    . "".$this->price.", ".$this->fk_accounting_account.", ".$this->fk_profit_center.", ".$this->fk_project.", '".$conf->entity."')";
//            echo $sql;
            dol_syslog(get_class($this)."::save production product success");
            $result = $this->db->query($sql);
            if($result){
                $id = $this->db->last_insert_id(MAIN_DB_PREFIX."production_product");
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
     * @return Integer -1: production already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries(){
        $this->db->begin();
        
        dol_syslog(get_class($this)."::checkDuplicatedEntries", LOG_DEBUG);
        global $conf;
        
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."production_product WHERE fk_product = ".$this->fk_product." AND "
                . "material_type = ".$this->material_type." AND amount = ".$this->amount." AND "
                . "fk_entrepot = ".$this->fk_entrepot." AND price = ".$this->price." AND "
                . "fk_accounting_account = ".$this->fk_accounting_account." AND fk_profit_center = ".$this->fk_profit_center." AND "
                . "fk_project = ".$this->fk_project." AND entity = '".$conf->entity."'";
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
        
        return 0; // production is not registered
    }

    private function cleanParameters(){
        $this->fk_product = $this->fk_product != 0 ? $this->fk_product : 'NULL';
        $this->material_type = !empty($this->material_type) ? "'".trim($this->material_type)."'" : 'NULL';
        $this->amount = !empty($this->amount) ? "'".$this->amount."'" : 0;
        $this->fk_entrepot = !empty($this->fk_entrepot) ? "'".trim($this->fk_entrepot)."'" : 'NULL';
        $this->price = !empty($this->price) ? "'".price2num($this->price)."'" : 0;
        $this->fk_accounting_account = $this->fk_accounting_account != 0 ? "'".$this->fk_accounting_account."'" : 'NULL';
        $this->fk_profit_center = !empty($this->fk_profit_center) ? "'".$this->fk_profit_center."'" : 'NULL';
        $this->fk_project = $this->fk_project != 0 ? "'".$this->fk_project."'" : 'NULL';
    }
    
    /**
     * Delete a production product
     * 
     * @return Integer -3: some db error. 0: production was sucessfully deleted
     */
    public function delete($id) {
        $this->db->begin();
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."production_product WHERE rowid = $id";
            
//        echo $sql;
        dol_syslog(get_class($this)."::delete production success rowid=".$id);
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
     * Returns  the production product details
     * 
     * @param Integer $id production id
     */
    public function fetch($id) {
        $sql  = "SELECT fk_product, material_type, amount, fk_entrepot, price, fk_accounting_account, fk_profit_center, fk_project ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_product WHERE rowid = $id";
       // echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            
            $this->fk_product               = $obj->fk_product;
            $this->material_type            = $obj->material_type;
            $this->amount                   = $obj->amount;
            $this->fk_entrepot              = $obj->fk_entrepot;
            $this->price                    = price2num($obj->price);
            $this->fk_accounting_account    = $obj->fk_accounting_account;
            $this->fk_profit_center         = $obj->fk_profit_center;
            $this->fk_project               = $obj->fk_project;
        }
        return $this;
    }
    
    /**
     * Update a production product
     * 
     * @return Integer -3: some db error. 0: production was sucessfully uppriced
     */
    public function update($id) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_product SET fk_product = ".$this->fk_product.", material_type = ".$this->material_type.", ";
        $sql .= "amount = ".$this->amount.", fk_entrepot = ".$this->fk_entrepot.", price = ".$this->price.", ";
        $sql .= "fk_accounting_account = ".$this->fk_accounting_account.", fk_profit_center = ".$this->fk_profit_center.", ";
        $sql .= "fk_project = ".$this->fk_project." WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this)."::update production product success rowid=".$id);
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
    
        
    
/****Nuevo 09032017******/



    	function ProductionProduct_desactivate($id)
	{
		//$result = $this->checkUsage();

//		if ($result != 0) {
			$this->db->begin();

			$sql = "UPDATE " . MAIN_DB_PREFIX . "production_product ";
			$sql .= "SET active = '0'";
			$sql .= " WHERE rowid = ".$this->db->escape($id);
//echo $sql;
			dol_syslog(get_class($this) . "::desactivate sql=" . $sql, LOG_DEBUG);
			$result = $this->db->query($sql);

			if ($result) {
				$this->db->commit();
				return 1;
			} else {
				$this->error = $this->db->lasterror();
				$this->db->rollback();
				return - 1;
			}
//		} else {
//			return - 1;
//		}
	}

	/**
	 * Account activate
	 *
	 * @param 	int		$id		Id
	 * @return 	int 			<0 if KO, >0 if OK
	 */
	function ProductionProduct_activate($id)
	{
		$this->db->begin();

		$sql = "UPDATE " . MAIN_DB_PREFIX . "production_product ";
		$sql .= "SET active = '1'";
		$sql .= " WHERE rowid = ".$this->db->escape($id);

		dol_syslog(get_class($this) . "::activate sql=" . $sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return - 1;
		}
	}

        public function updatebyfield($fieldname, $value, $id) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_product SET ".$fieldname." = '".$value."'";
        $sql .= " WHERE rowid ='".$id."'";
//        echo $sql;
        dol_syslog(get_class($this)."::update production product success rowid=".$id);
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
     * Update a production product
     * 
     * @return Integer -3: some db error. 0: production was sucessfully uppriced
     */
    public function updateprice($id) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_product SET price = ".$this->price;
        $sql .= " WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this)."::update production price product success rowid=".$id);
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
    
    
    public function getnameproduct($id) {
        $sql  = "SELECT ref  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."product WHERE rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
        }
        return $obj->ref;
    }
    
    public function getdataproduct($id) {
        global $conf;
        $sql  = "SELECT ref, label, pmp  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."product WHERE rowid = $id and entity = $conf->entity";
       //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
           $obj = $this->db->fetch_object($result);
            
            $this->ref              = $obj->ref;
            $this->label            = $obj->label;
            $this->pmp              = $obj->pmp;
        }
        return $this;
    }
    
    public function getpriceproduct($id) {
        $sql  = "SELECT label,pmp  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."product WHERE rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            
        }
        return $obj->pmp.'-'.$obj->label;
    }
    
    public function getnameentrepotbyproduct($id) {
        $sql  = "SELECT ep.label, pp.fk_entrepot, pp.price  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_product as pp"
                . " LEFT JOIN ".MAIN_DB_PREFIX."entrepot as ep on pp.fk_entrepot=ep.rowid" 
                . " WHERE pp.rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            //var_dump($obj);
        }
        return "<sein>".$obj->label."<sein>".$obj->fk_entrepot."<sein>".$obj->price;
    }
    
    public function getnameentrepot($id) {
        $sql  = "SELECT label  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."entrepot WHERE rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
        }
        return $obj->label;
    }
    
    public function getnameaccount($id) {
        $sql  = "SELECT label  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."accountingaccount WHERE rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
        }
        return $obj->label;
    }
    
     public function getnameprofitcenter($id) {
        $sql  = "SELECT name  ";
        $sql .= "FROM ".MAIN_DB_PREFIX."profit_center WHERE profit_id = $id";
  //      echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
        }
        return $obj->name;
    }
    
}

