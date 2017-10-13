<?php
/**
 * 
 * @author EnginnerRodriguez
 *
 */
class Evento_Model_DAO_Evento {
    
    private $tableModulo;
    private $tableOrganizacion;
    private $tableRol;
    private $tableSubscripcion;
    private $tableUsuario;
    
    private $tableAsistente;
    private $tableEvento;
    private $tableAsistentesEvento;
    
    
    public function __construct($dbAdapter) {
        $this->tableModulo = new Evento_Model_DbTable_Main_Modulo(array("db"=>$dbAdapter));
        $this->tableOrganizacion = new Evento_Model_DbTable_Main_Organizacion(array("db"=>$dbAdapter));
        $this->tableRol = new Evento_Model_DbTable_Main_Rol(array("db"=>$dbAdapter));
        $this->tableSubscripcion = new Evento_Model_DbTable_Main_Subscripcion(array("db"=>$dbAdapter));
        $this->tableUsuario = new Evento_Model_DbTable_Main_Usuario(array("db"=>$dbAdapter));
        
        $this->tableAsistente = new Evento_Model_DbTable_Asistente(array("db"=>$dbAdapter));
        $this->tableEvento = new Evento_Model_DbTable_Evento(array("db"=>$dbAdapter));
        $this->tableAsistentesEvento = new Evento_Model_DbTable_AsistentesEvento(array("db"=>$dbAdapter));
    }
    
    /**
     * 
     * @param String $claveOrganizacion
     * @return array
     */
    public function getOrganizacionByClave($claveOrganizacion) {
        $tOrg = $this->tableOrganizacion;
        $select = $tOrg->select()->from($tOrg)->where('claveOrganizacion=?',$claveOrganizacion);
        $rowOrg = $tOrg->fetchRow($select);
        
        return $rowOrg->toArray();
    }
    
    /**
     * 
     * @param int $idOrganizacion
     * @param number $idModulo
     */
    public function getSubscripcionesByIds($idOrganizacion, $tipo = 'O', $idModulo = 3) {
        $tSubs = $this->tableSubscripcion;
        $select = $tSubs->select()->from($tSubs)
            ->where('idOrganizacion=?', $idOrganizacion)
            ->where('idModulo=?', $idModulo)
            ->where('tipo=?', $tipo);
        
        $rowsSubs = $tSubs->fetchAll($select);
        
        return $rowsSubs->toArray();
    }
    
    public function getEventoById($idEvento) {
        $tEvento = $this->tableEvento;
        $select = $tEvento->select()->from($tEvento)->where("id=?",$idEvento);
        $rowEvento = $tEvento->fetchRow($select);
        
        return $rowEvento->toArray();
        
    }
    
    public function getAllEventos(){
        $tEv = $this->tableEvento;
        // Cuando solo traigamos los eventos vigentes
        // $select = $tEv->select()->from($tEv);
        $rowsEvents = $tEv->fetchAll();
        return $rowsEvents->toArray();
    }
    
    public function saveEvento($datos) {
        $tEv = $this->tableEvento;
        $tEv->insert($datos);
    }
    
    public function getAsistentesEventoByParams($idEvento,array $params) {
        $tAE = $this->tableAsistentesEvento;
        $select = $tAE->select()->from($tAE)->where("idEvento=?",$idEvento);
        $rowEvento = $tAE->fetchRow($select);
        
        $idsAsistentes = explode(",", $rowEvento->idsAsistentes);
        
        $tA = $this->tableAsistente;
        $select = $tA->select()->from($tA)->where("id IN (?)",$idsAsistentes);
        //$select = $tAE->select()->from($tAE);
        foreach ($params as $col => $val) :
            $select->orWhere($col." like ?", "%".$val."%");
        endforeach;
        
        $rowsAsistentes = $tA->fetchAll($select);
        
        // print_r($select->__toString());
        return $rowsAsistentes->toArray();
    }
    
}