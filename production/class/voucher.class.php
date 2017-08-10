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
 *   \file       htdocs/production/class/voucher.class.php
 *   \brief      Salary
 *   \ingroup    production
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Description of production
 *
 * @author henryseron
 */
class ProductionVoucher extends CommonObject {
    
    var $accounting_date;
    var $reference;
    var $comment;
    var $gloss_accounting_entry;
    var $fk_process_number;
    var $fk_product;
    var $amount;
    var $price;
    var $fk_entrepot;
    var $serial_number;
    var $expiration_date;
    var $voucher_number;
    var $fk_accounting_account;
    /**
     * A: Aproved | P: Pending
     *
     * @var CHAR
     */
    var $status;
    
    var $completed_amount;
    var $real_product_cost;
    
    
    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db){
        $this->db = $db;
        
        $this->status = 'P';
    }
    
    /**
     * Save a new voucher
     * 
     * @return Integer -1: voucher already registered. -3: some db error. 0: voucher was sucessfully saved
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $verify = $this->checkDuplicatedEntries();
        
        if($verify == 0){
            global $conf;
            
            $sql = "SELECT MAX(voucher_number) + 1 as voucher_number FROM ".MAIN_DB_PREFIX."production_voucher WHERE entity = ".$conf->entity;
            $result = $this->db->query($sql);
            $obj = $this->db->fetch_object($result);
            $this->voucher_number = ($obj->voucher_number != NULL) ? $obj->voucher_number : 1;
            
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."production_voucher (rowid, accounting_date, reference, comment, gloss_accounting_entry, "
                    . "fk_process_number, fk_product, amount, price, fk_entrepot, serial_number, expiration_date, entity, voucher_number, "
                    . "status, fk_accounting_account) "
                    . "VALUES (NULL, ".$this->accounting_date.", ".$this->reference.", ".$this->comment.", ".$this->gloss_accounting_entry.", "
                    . "".$this->fk_process_number.", ".$this->fk_product.", ".$this->amount.", ".$this->price.", ".$this->fk_entrepot.", "
                    . "".$this->serial_number.", ".$this->expiration_date.", ".$conf->entity.", ".$this->voucher_number.", "
                    . "'".$this->status."', ".$this->fk_accounting_accounts.")";
//            echo $sql;
            dol_syslog(get_class($this)."::save voucher success");
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
     * @return Integer -1: voucher already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries(){
        $this->db->begin();
        
        dol_syslog(get_class($this)."::checkDuplicatedEntries", LOG_DEBUG);
        global $conf;
        
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."production_voucher WHERE fk_process_number = ".$this->fk_process_number." "
                . "AND entity = '".$conf->entity."'";
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
        $this->accounting_date          = !empty($this->accounting_date) ? "'".$this->accounting_date."'" : 'NULL';
        $this->reference                = !empty($this->reference) ? "'".trim($this->reference)."'" : 'NULL';
        $this->comment                  = !empty($this->comment) ? "'".trim($this->comment)."'" : 'NULL';
        $this->gloss_accounting_entry   = !empty($this->gloss_accounting_entry) ? "'".$this->gloss_accounting_entry."'" : 'NULL';
        $this->fk_process_number        = !empty($this->fk_process_number) ? "'".trim($this->fk_process_number)."'" : 'NULL';
        $this->fk_product               = !empty($this->fk_product) && $this->fk_product > 0 ? "'".trim($this->fk_product)."'" : 'NULL';
        $this->amount                   = !empty($this->amount) ? "'".price2num($this->amount)."'" : 0;
        $this->price                    = !empty($this->price) ? "'".price2num($this->price)."'" : 0;
        $this->fk_entrepot              = !empty($this->fk_entrepot) ? "'".trim($this->fk_entrepot)."'" : 'NULL';
        $this->serial_number            = !empty($this->serial_number) ? "'".$this->serial_number."'" : 'NULL';
        $this->expiration_date          = !empty($this->expiration_date) ? "'".$this->expiration_date."'" : 'NULL';
        $this->fk_accounting_account    = !empty($this->fk_accounting_account) ? "'".$this->fk_accounting_account."'" : 'NULL';
    }
    
    /**
     * Delete a voucher
     * 
     * @return Integer -3: some db error. 0: vouvher was sucessfully deleted
     */
    public function delete($id) {
        $this->db->begin();
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."production_voucher WHERE rowid = $id";
            
//        echo $sql;
        dol_syslog(get_class($this)."::delete voucher success rowid=".$id);
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
     * Returns  the voucher details
     * 
     * @param Integer $id                   voucher id
     * @return Integer -3: some db error. 0: process sucessful
     */
    public function fetch($id) {
        global $conf;
        $sql  = "SELECT v.accounting_date, v.reference, v.comment, v.gloss_accounting_entry, v.fk_process_number, v.fk_product, v.amount, ";
        $sql .= "v.price, v.fk_entrepot, v.serial_number, v.expiration_date, v.voucher_number, v.status, p.completed_amount, p.real_product_cost ";
        $sql .= "FROM ".MAIN_DB_PREFIX."production_voucher as v ";
        $sql .= "LEFT JOIN ".MAIN_DB_PREFIX."productive_process as p ON p.order_number = v.fk_process_number AND p.entity = ".$conf->entity." ";
        $sql .= "WHERE v.rowid = $id";
//        echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            
            $this->accounting_date          = $obj->accounting_date;
            $this->reference                = $obj->reference;
            $this->comment                  = $obj->comment;
            $this->gloss_accounting_entry   = $obj->gloss_accounting_entry;
            $this->fk_process_number        = $obj->fk_process_number;
            $this->fk_product               = $obj->fk_product;
            $this->amount                   = $obj->amount;
            $this->price                    = $obj->price;
            $this->fk_entrepot              = $obj->fk_entrepot;
            $this->serial_number            = $obj->serial_number;
            $this->expiration_date          = $obj->expiration_date;
            $this->voucher_number           = $obj->voucher_number;
            $this->status                   = $obj->status;
            $this->completed_amount         = $obj->completed_amount;
            $this->real_product_cost        = $obj->real_product_cost;
            
            $this->db->commit();
            return 0;
        }
        else {
            $this->db->rollback();
            return -3;
        }
    }
    
    /**
     * Update a voucher
     * 
     * @return Integer -3: some db error. 0: voucher was sucessfully updated
     */
    public function update($id) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_voucher SET accounting_date = ".$this->accounting_date.", reference = ".$this->reference.", ";
        $sql .= "comment = ".$this->comment.", gloss_accounting_entry = ".$this->gloss_accounting_entry.", ";
        $sql .= "fk_process_number = ".$this->fk_process_number.", fk_product = ".$this->fk_product.", amount = ".$this->amount.", ";
        $sql .= "price = ".$this->price.", fk_entrepot = ".$this->fk_entrepot.", ";
        $sql .= "serial_number = ".$this->serial_number.", expiration_date = ".$this->expiration_date." ";
        $sql .= "WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this)."::update voucher success rowid=".$id);
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
     * Returns  the voucher process number details
     * 
     * @param String  $fk_process_number    process order number
     * @return Integer -3: some db error. 0: process sucessful
     */
    public function fetch_process_number($fk_process_number) {
        $sql  = "SELECT p.completed_amount, p.real_product_cost ";
        $sql .= "FROM ".MAIN_DB_PREFIX."productive_process as p ";
        $sql .= "WHERE p.rowid = '$fk_process_number'";
//        echo $sql;
        $result = $this->db->query($sql);
        
        if ($result){
            $obj = $this->db->fetch_object($result);
            
            $this->completed_amount         = $obj->completed_amount;
            $this->real_product_cost        = $obj->real_product_cost;
            
            $this->db->commit();
            return 0;
        }
        else {
            $this->db->rollback();
            return -3;
        }
    }
    
    /**
     * Update a voucher status
     * 
     * @return Integer -3: some db error. 0: voucher was sucessfully updated
     */
    public function change_voucher_status($id) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_voucher SET status = '".$this->status."' WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this)."::update voucher status success rowid=".$id);
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
     * Update ventilation for prodution voucher (set accounting account)
     * 
     * @return Integer -3: some db error. 0: voucher was sucessfully updated
     */
    public function set_voucher_accounting_account($id, $accounting_account) {
        // Clean parameters
        $this->cleanParameters();
        
        $this->db->begin();
        
        $sql  = "UPDATE ".MAIN_DB_PREFIX."production_voucher SET fk_accounting_account = '$accounting_account' ";
        if(is_string($id)){
            $sql .= "WHERE rowid = '$id'";
        }
        elseif(is_array($id)){
            $sql .= "WHERE rowid IN (".implode(',', $id).") ";
        }
//        echo $sql;
        dol_syslog(get_class($this)."::update voucher accounting account success rowid=".$id);
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
