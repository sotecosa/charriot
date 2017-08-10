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
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 * Description of production
 *
 * @author henryseron
 */
class ProductionProcess extends CommonObject {

    const TYPE_STANDARD = 0;
    const TYPE_REPLACEMENT = 1;
    const TYPE_CREDIT_NOTE_CANCELLATION = 1;
    const TYPE_CREDIT_NOTE_ADMINISTRATIVE = 2;
    const TYPE_CREDIT_NOTE_PARTIAL_DISCCOUNT = 3;
    const TYPE_DEPOSIT = 3;
    const TYPE_PROFORMA = 4;
    const TYPE_SITUATION = 5;
    const TYPE_DEBIT_NOTE = 6;
    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;
    const STATUS_CLOSED = 2;
    const STATUS_ABANDONED = 3;

    var $rowid;
    var $obj;
    var $process_type;
    var $status;
    var $fk_material;
    var $planned_amount;
    var $fk_entrepot;
    var $order_number;
    var $date;
    var $date_end;
    var $fk_user;
    var $fk_societe;
    var $fk_profit_center;
    var $fk_project;
    var $material_type;
    var $fk_product;
    var $material_price;
    var $table_element = 'productive_process';
    var $real_component_cost;
    var $aditional_cost;
    var $real_product_cost;
    var $total_deviation;
    var $deviation_percent;
    var $completed_amount;
    var $rejected_amount;
    var $real_date_end;

    /**
     *    Constructor
     *
     *    @param	DoliDB		$db		Database handler
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Save a new productive process
     * 
     * @return Integer -1: process already registered. -3: some db error. 0: process was sucessfully saved
     */
    public function create() {
        // Clean parameters
        $this->cleanParameters();

        $this->db->begin();


        $this->setValidate();

        $verify = $this->checkDuplicatedEntries();

        if ($verify == 0) {
            global $conf;



            $sql = "SELECT MAX(order_number) + 1 as order_number FROM " . MAIN_DB_PREFIX . "productive_process WHERE entity = " . $conf->entity;
            $result = $this->db->query($sql);
            $obj = $this->db->fetch_object($result);
            $this->order_number = ($obj->order_number != NULL) ? $obj->order_number : 1;

            //echo $sql;

            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "productive_process (ref, process_type, status, fk_product, planned_amount, "
                    . "fk_entrepot, order_number, date, date_end, fk_user, fk_societe, fk_profit_center, fk_project, entity,real_component_cost) "
                    . "VALUES ( '" . $this->ref . "',  " . $this->process_type . ", " . $this->status . ", " . $this->fk_product . ", " . $this->planned_amount . ", "
                    . "" . $this->fk_entrepot . ", " . $this->order_number . ", " . $this->date . ", " . $this->date_end . ", " . $this->fk_user . ", "
                    . "" . $this->fk_societe . ", " . $this->fk_profit_center . ", " . $this->fk_project . ", " . $conf->entity . "," . $this->real_component_cost . ")";
            //echo "-->". $sql;
            dol_syslog(get_class($this) . "::save productive process success");
            $result = $this->db->query($sql);
            if ($result) {
                $id = $this->db->last_insert_id(MAIN_DB_PREFIX . "productive_process");
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
     * @return Integer -1: process already registered. -3: some db error. 0: it is OK
     */
    private function checkDuplicatedEntries() {
        $this->db->begin();

        dol_syslog(get_class($this) . "::checkDuplicatedEntries", LOG_DEBUG);
        global $conf;

        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "productive_process WHERE order_number = " . $this->order_number . " AND entity = '" . $conf->entity . "'";
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

        return 0; // production is not registered
    }

    private function cleanParameters() {
        $this->process_type = !empty($this->process_type) ? "'" . trim($this->process_type) . "'" : 'NULL';
        $this->status = !empty($this->status) ? "'" . trim($this->status) . "'" : 'NULL';
        $this->fk_product = !empty($this->fk_product) || $this->fk_product != 0 ? "'" . $this->fk_product . "'" : 'NULL';
        $this->planned_amount = !empty($this->planned_amount) ? "'" . price2num($this->planned_amount) . "'" : 0;
        $this->fk_entrepot = !empty($this->fk_entrepot) ? "'" . trim($this->fk_entrepot) . "'" : 'NULL';
        $this->order_number = !empty($this->order_number) ? "'" . price2num($this->order_number) . "'" : 0;
        $this->date = !empty($this->date) ? "'" . $this->date . "'" : 'NULL';
        $this->date_end = !empty($this->date_end) ? "'" . $this->date_end . "'" : 'NULL';
        $this->fk_user = $this->fk_user > 0 ? "'" . trim($this->fk_user) . "'" : 'NULL';
        $this->fk_societe = !empty($this->fk_societe) ? "'" . trim($this->fk_societe) . "'" : 'NULL';
        $this->fk_profit_center = !empty($this->fk_profit_center) ? "'" . trim($this->fk_profit_center) . "'" : 'NULL';
        $this->fk_project = !empty($this->fk_project) ? "'" . trim($this->fk_project) . "'" : 'NULL';

        $this->real_component_cost = !empty($this->real_component_cost) ? "'" . price2num($this->real_component_cost) . "'" : 0;
        $this->aditional_cost = !empty($this->aditional_cost) ? "'" . price2num($this->aditional_cost) . "'" : 0;
        $this->real_product_cost = !empty($this->real_product_cost) ? "'" . price2num($this->real_product_cost) . "'" : 0;
        $this->total_deviation = !empty($this->total_deviation) ? "'" . price2num($this->total_deviation) . "'" : 0;
        $this->deviation_percent = !empty($this->deviation_percent) ? "'" . price2num($this->deviation_percent) . "'" : 0;
        $this->completed_amount = !empty($this->completed_amount) ? "'" . price2num($this->completed_amount) . "'" : 0;
        $this->rejected_amount = !empty($this->rejected_amount) ? "'" . price2num($this->rejected_amount) . "'" : 0;
        $this->real_date_end = !empty($this->real_date_end) ? "'" . $this->real_date_end . "'" : 'NULL';
    }

    public function deleteline($id, $idline) {
        $this->db->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "product_material WHERE fk_product = " . $id . " and fk_material=" . $idline;
        dol_syslog(get_class($this) . "::delete material success fk_product = " . $id . " and fk_material=" . $idline);
        $result = $this->db->query($sql);
        //echo $sql;

        if ($result > 0) {

            dol_syslog(get_class($this) . "::delete material success lineid = " . $id);
            $sqlline = "DELETE FROM " . MAIN_DB_PREFIX . "production_material WHERE rowid =" . $idline;
            //  echo $sqlline;
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

    /**
     * Delete a productive process
     * 
     * @return Integer -3: some db error. 0: process was sucessfully deleted
     */
    public function delete1($id) {
        $this->db->begin();
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "productive_process WHERE rowid = $id";

//        echo $sql;
        dol_syslog(get_class($this) . "::delete productive process success rowid=" . $id);
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
     * Returns  the productive process details
     * 
     * @param Integer $id process id
     * @return Integer -3: some db error. 0: process sucessful
     */
    public function fetch($id) {
        $sql = "SELECT p.ref, p.process_type, p.status, p.fk_product, p.planned_amount, p.fk_entrepot, p.order_number, p.date, p.date_end, p.fk_user, ";
        $sql .= "p.fk_societe, p.fk_profit_center, p.fk_project, p.real_component_cost, p.aditional_cost, p.real_product_cost, p.total_deviation, ";
        $sql .= "p.deviation_percent, p.completed_amount, p.rejected_amount, p.real_date_end, pp.material_type, pp.price ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "productive_process as p ";
        $sql .= "LEFT JOIN " . MAIN_DB_PREFIX . "production_product as pp ON pp.rowid = p.fk_product ";
        $sql .= "WHERE p.rowid = $id";
        //echo $sql;
        $result = $this->db->query($sql);

        if ($result) {
            $obj = $this->db->fetch_object($result);


            $this->ref = $obj->ref;
            $this->process_type = $obj->process_type;
            $this->status = $obj->status;
            $this->planned_amount = $obj->planned_amount;
            $this->fk_entrepot = $obj->fk_entrepot;
            $this->order_number = $obj->order_number;
            $this->date = $obj->date;
            $this->date_end = $obj->date_end;
            $this->fk_user = $obj->fk_user;
            $this->fk_societe = $obj->fk_societe;
            $this->fk_profit_center = $obj->fk_profit_center;
            $this->fk_project = $obj->fk_project;
            $this->material_type = $obj->material_type;
            $this->fk_product = $obj->fk_product;
            $this->material_price = $obj->price;

            $this->real_component_cost = $obj->real_component_cost;
            $this->aditional_cost = $obj->aditional_cost;
            $this->real_product_cost = $obj->real_product_cost;
            $this->total_deviation = $obj->total_deviation;
            $this->deviation_percent = $obj->deviation_percent;
            $this->completed_amount = $obj->completed_amount;
            $this->rejected_amount = $obj->rejected_amount;
            $this->real_date_end = $obj->real_date_end;

            $this->db->commit();
            return 0;
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    /**
     * Update a productive process
     * 
     * @return Integer -3: some db error. 0: process was sucessfully updated
     */
    public function update($id) {
        // Clean parameters
        $this->cleanParameters();

        $this->db->begin();

        $sql = "UPDATE " . MAIN_DB_PREFIX . "productive_process SET process_type = " . $this->process_type . ", status = " . $this->status . ", ";
        $sql .= "fk_product = " . $this->fk_product . ", planned_amount = " . $this->planned_amount . ", fk_entrepot = " . $this->fk_entrepot . ", ";
        $sql .= "date = " . $this->date . ", date_end = " . $this->date_end . ", fk_user = " . $this->fk_user . ", ";
        $sql .= "fk_societe = " . $this->fk_societe . ", fk_profit_center = " . $this->fk_profit_center . ", fk_project = " . $this->fk_project . ", ";
        $sql .= "real_component_cost = " . $this->real_component_cost . ", aditional_cost = " . $this->aditional_cost . ", ";
        $sql .= "real_product_cost = " . $this->real_product_cost . ", total_deviation = " . $this->total_deviation . ", ";
        $sql .= "deviation_percent = " . $this->deviation_percent . ", completed_amount = " . $this->completed_amount . ", ";
        $sql .= "rejected_amount = " . $this->rejected_amount . ", real_date_end = " . $this->real_date_end . " ";
        $sql .= "WHERE rowid = '$id'";
//        echo $sql;
        dol_syslog(get_class($this) . "::update productive process success rowid=" . $id);
        $result = $this->db->query($sql);
        if ($result) {
            $this->db->commit();
            return 0;
        } else {
            $this->db->rollback();
            return -3;
        }
    }

    function getNextNumRef1() {
        global $conf;

        $expld_car = (empty($conf->global->NDF_EXPLODE_CHAR)) ? "-" : $conf->global->NDF_EXPLODE_CHAR;
        $num_car = (empty($conf->global->NDF_NUM_CAR_REF)) ? "5" : $conf->global->NDF_NUM_CAR_REF;

        $sql = 'SELECT order_number';
        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . '';
        $sql .= ' ORDER BY order_number DESC';

        //echo "-getNextNumRef--> ". $sql;

        $result = $this->db->query($sql);

        if ($this->db->num_rows($result) > 0):
            $obj = $this->db->fetch_object($result);
            $this->ref = $obj->order_number;
            $this->ref++;
            while (strlen($this->ref) < $num_car):
                $this->ref = "0" . $this->ref;
            endwhile;
        else:
            $this->ref = 1;
            while (strlen($this->ref) < $num_car):
                $this->ref = "0" . $this->ref;
            endwhile;
        endif;

        if ($result):
            return 1;
        else:
            $this->error = $this->db->error("aqui es el error");
            return -1;
        endif;
    }

    private function setValidate() {
        global $conf, $langs;

        $expld_car = (empty($conf->global->NDF_EXPLODE_CHAR)) ? "" : $conf->global->NDF_EXPLODE_CHAR;

        // Sélection du numéro de ref suivant
        $ref_next = $this->getNextNumRef1();
        $ref_number_int = ($this->ref + 1) - 1;

        // Création du ref_number suivant
        if ($ref_next) {
            $prefix = "NOP";
            if (!empty($conf->global->EXPENSE_REPORT_PREFIX))
                $prefix = $conf->global->EXPENSE_REPORT_PREFIX;
            $this->ref = $prefix . "-" . $this->ref;
        } else {
            dol_syslog(get_class($this) . "::set_save expensereport already with save status", LOG_WARNING);
        }
    }

    function create_valsal($id) {
        global $langs, $conf, $mysoc, $hookmanager;
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "productive_process AS pp WHERE rowid='" . $id . "'";
        // echo  $sql;
        $resql = $this->db->query($sql);

        if ($this->db->num_rows($sql) > 0) {
            if ($resql) {
                $cab = $this->db->fetch_object($resql);



                if ($cab->state == 1) {

                    setEventMessage('Vale de Salida ya existe', 'errors');
                    $this->db->rollback();
                    return -3;
                } else {


                    $sqlhead = "INSERT INTO " . MAIN_DB_PREFIX . "facture "
                            . "(facnumber, entity, fk_soc, datec, datef, date_valid,"
                            . "tms, paye, amount,total, "
                            . " date_lim_reglement, note_private, note_public, model_pdf, "
                            . "tax_indicator,typedoc,idmove) "
                            . "VALUES ('PROV','" . $conf->entity . "','2963','" . $cab->date . "',"
                            . "'" . $cab->date . "',"
                            . "'" . $cab->date . "','" . $cab->tms . "','" . $this->paye . "','" . $cab->planned_amount . "',"
                            . "'" . $cab->real_component_cost . "',"
                            . "'" . $cab->date_end . "','Enviado a taller','Enviado a taller','crabe',"
                            . "'1004','1','1')";

                    //   echo $sqlhead;

                    dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                    $reshead = $this->db->query($sqlhead);

                    // var_dump($reshead);

                    if ($reshead) {
                        $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . 'facture');
                        //var_dump($this->id);
                        $this->ref = '(PROV' . $this->id . ')';
                        $sqlup = 'UPDATE ' . MAIN_DB_PREFIX . "facture SET facnumber='" . $this->ref . "' WHERE rowid=" . $this->id;
                        // echo $sqlup;
                        dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                        $resuphead = $this->db->query($sqlup);

                        if ($resuphead) {

                            //var_dump($cab->rowid);

                            $sqlstate = 'UPDATE ' . MAIN_DB_PREFIX . "productive_process SET state='1' WHERE rowid=" . $cab->rowid;
                            //echo $sqlstate;
                            dol_syslog(get_class($this) . "::update", LOG_DEBUG);
                            $restate = $this->db->query($sqlstate);




                            $sqline = "SELECT prm.fk_product AS produ_mate, prm.description, prm.amount AS amount_mate, "
                                    . "prm.price AS buy_mateprice, prm.total AS total_mate FROM " . MAIN_DB_PREFIX . "product_material AS pp "
                                    . " LEFT JOIN " . MAIN_DB_PREFIX . "production_material prm ON prm.rowid = pp.fk_material "
                                    . " WHERE prm.entity = '" . $conf->entity . "' AND pp.fk_product = $cab->fk_product ";
                            $resqline = $this->db->query($sqline);
                            $num = $this->db->num_rows($sqline);


                            // echo $sqline;
                           // var_dump($num);

                            $i = 1;
                            if ($resqline) {
                                while ($i <= $num) {

                                    $line = $this->db->fetch_object($resqline);


                                    $sqlinsline = "INSERT INTO " . MAIN_DB_PREFIX . "facturedet "
                                            . "(fk_facture,description, qty,"
                                            . "fk_product, "
                                            . "subprice,total_ht) "
                                            . "VALUES ('" . $this->id . "','" . $line->description . "','" . $line->amount_mate . "',"
                                            . "'" . $line->produ_mate . "','" . $line->buy_mateprice . "',"
                                            . "'" . $line->total_mate . "')";
                                    //echo $sqlinsline;
                                    $this->db->query($sqlinsline);
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
            if ($resql) {
                $this->db->commit();
                return $this->id;
            } else {
                $this->db->rollback();
                return -3;
            }
        }
    }

    function create_valing($id) {
        global $langs, $conf, $mysoc, $hookmanager;

        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "productive_process AS pp WHERE rowid='" . $id . "'";
        //echo $sql;

        $resql = $this->db->query($sql);
        if ($this->db->num_rows($sql) > 0) {
            if ($resql) {
       $cab = $this->db->fetch_object($resql);
       
       
                if ($cab->stateing == 1) {

                    setEventMessage('Vale de Entrada ya existe', 'errors');
                    $this->db->rollback();
                    return -3;
                } else {
       
       
             
                $sqlheadpro = "INSERT INTO " . MAIN_DB_PREFIX . "facture_fourn "
                        . "(ref,ref_supplier, entity, fk_soc,datec, datef,"
                        . "total_ht,total_ttc, "
                        . " date_lim_reglement, note_private, note_public, "
                        . "tax_indicator,where_from,idmove) "
                        . "VALUES ('PROV','1004','" . $conf->entity . "','2963','" . $cab->date . "','" . $cab->date . "',"
                        . "'" . $cab->real_component_cost . "',"
                        . "'" . $cab->real_component_cost . "',"
                        . "'" . $cab->date_end . "','Enviado a taller','Enviado a taller',"
                        . "'30','M','7')";

                //echo $sqlheadpro;
                //   dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                $resheadpro = $this->db->query($sqlheadpro);

 if ($resheadpro) {






                    $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . 'facture_fourn');
                    //var_dump($resheadpro);
                    //var_dump($this->id);
                    $this->ref = '(PROV' . $this->id . ')';
                    $this->folio = '1004-' . $this->id . '';
                    $sqlupro = 'UPDATE ' . MAIN_DB_PREFIX . "facture_fourn SET ref='" . $this->ref . "' , ref_supplier='" . $this->folio . "'   WHERE rowid=" . $this->id;
                    //     echo $sqlupro;
                    dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                    $resuplinepro = $this->db->query($sqlupro);

                    //    $sqlstateing = 'UPDATE ' . MAIN_DB_PREFIX . "productive_process SET stateing='1' WHERE rowid=" . $cab->rowid;
                    dol_syslog(get_class($this) . "::update", LOG_DEBUG);
                    $restateing = $this->db->query($sqlstateing);


                    $sqlheadextra = "INSERT INTO " . MAIN_DB_PREFIX . "facture_fourn_extrafields"
                            . "(tms,fk_object, tipodoc, fechadoc) "
                            . "VALUES ('" . $cab->date . "','" . $this->id . "','1004','" . $cab->date . "')";
                    dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                    $resheadextra = $this->db->query($sqlheadextra);
//echo $sqlheadextra;



                    if ($resuplinepro) {

                        $sqlinepro = "SELECT pp.rowid, pp.amount, pp.price,pp.fk_product, prod.ref, prod.label "
                                . "FROM " . MAIN_DB_PREFIX . "production_product AS pp "
                                . "LEFT JOIN " . MAIN_DB_PREFIX . "product AS prod ON prod.rowid = pp.fk_product "
                                . "WHERE pp.entity = '" . $conf->entity . "' AND pp.rowid = $cab->fk_product ";
                        $resqlinepro = $this->db->query($sqlinepro);
                        //echo $sqlinepro  ;




                        $i = 1;
                        if ($resqlinepro) {
                            while ($i <= $resqlinepro->num_rows) {

                                $linepro = $this->db->fetch_object($resqlinepro);


                                $sqlinspro = "INSERT INTO " . MAIN_DB_PREFIX . "facture_fourn_det "
                                        . "(fk_facture_fourn,description, qty,"
                                        . "fk_product, "
                                        . "total_ht) "
                                        . "VALUES ('" . $this->id . "','" . $linepro->label . "',"
                                        . "'" . $linepro->amount . "',"
                                        . "'" . $linepro->fk_product . "','" . $linepro->price . "')";

                                $this->db->query($sqlinspro);
                                $i++;
                            }
                        }
                    }
                }
            }
            }
            if ($resql) {
                $this->db->commit();
                return $this->id;
            } else {
                $this->db->rollback();
                return -3;
            }
        }
    }

}
