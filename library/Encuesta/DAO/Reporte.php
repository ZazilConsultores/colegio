<?php
/**
 * 
 * @author EnginnerRodriguez
 *
 */
class Encuesta_DAO_Reporte implements Encuesta_Interfaces_IReporte {
	
	private $tablaERealizadas;
    private $tablaReportesEncuesta;
    
    private $tablaGrupoEscolar;
    private $tablaAsignacionGrupo;
    
	public function __construct($dbAdapter) {
	    $config = array('db' => $dbAdapter);
		//$dbAdapter = Zend_Registry::get('dbmodencuesta');
		
		$this->tablaReporte = new Encuesta_Data_DbTable_ReportesEncuesta($config);
		$this->tablaERealizadas = new Encuesta_Data_DbTable_EncuestasRealizadas($config);
        $this->tablaReportesEncuesta = new Encuesta_Data_DbTable_ReportesEncuesta($config);
        
        $this->tablaGrupoEscolar = new Encuesta_Data_DbTable_GrupoEscolar($config);
        $this->tablaAsignacionGrupo = new Encuesta_Data_DbTable_AsignacionGrupo($config);
        //$this->tablaReportesGenerales = new Encuesta_Data_DbTable_ReportesGenerales($config);
	}
	
	/**
	 * Agrega un nuevo reporte de encuesta grupal en la tabla Reporte
	 */
	public function agregarReporteGrupal($nombreReporte,$idEncuesta,$idAsignacion){
		$tRE = $this->tablaReportesEncuesta;
		$select = $tRE->select()->from($tRE)->where("nombreReporte=?",$nombreReporte);
		$rowReporte = $tablaReporte->fetchRow($select);
		$idReporte = null;
		if(is_null($rowReporte)){
		    $datos = array(
		        'idEncuesta'=>$idEncuesta,
		        'nombreReporte'=>$nombreReporte,
		        "tipoReporte" => "RG",
		        "rutaReporte" => '',
		        'creacion'=> date("Y-m-d H:i:s",time()));
			$idReporte = $tablaReporte->insert();
			
		}else{
			$idReporte = $rowReporte->idReporte;
		}
		
		//$tablaERealizadas = $this->tablaERealizadas;
		//$select = $tablaERealizadas->select()->from($tablaERealizadas)->where("idEncuesta=?",$idEncuesta)->where("idAsignacionGrupo=?",$idAsignacion);
		//$rowRealizadas = $tablaERealizadas->fetchRow($select);
		
		//$rowRealizadas->idReporte = $idReporte;
		//$rowRealizadas->save();
		
		//print_r($idReporte);
		return $idReporte;
	}
	
	/**
	 * Agrega un nuevo reporte de encuesta general en la tabla Reporte
	 * Agrega una referencia en la tabla EReportesGenerales
	 */
	public function agregarReporteGeneral($nombreReporte,$idEncuesta,$idDocente){
		$tablaReporte = $this->tablaReporte;
		$select = $tablaReporte->select()->from($tablaReporte)->where("nombreReporte=?",$nombreReporte);
		$rowReporte = $tablaReporte->fetchRow($select);
		$idReporte = null;
		if(is_null($rowReporte)){
			$tablaReporte->insert(array('nombreReporte'=>$nombreReporte,'estatus'=>'A'));
			$idReporte = $tablaReporte->getAdapter()->lastInsertId('Reporte','idReporte');
		}else{
			$idReporte = $rowReporte->idReporte;
		}
		
		$tablaERealizadas = $this->tablaERealizadas;
		$select = $tablaERealizadas->select()->from($tablaERealizadas)->where("idEncuesta=?",$idEncuesta)->where("idAsignacion=?",$idAsignacion);
		$rowRealizadas = $tablaERealizadas->fetchRow($select);
		
		$rowRealizadas->idReporte = $idReporte;
		$rowRealizadas->save();
		
		//print_r($idReporte);
		return $idReporte;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see Encuesta_Interfaces_IReporte::obtenerReporte()
	 */
	public function obtenerReporte($idReporte){
		$tRE = $this->tablaReportesEncuesta;
		$select = $tRE->select()->from($tRE)->where("idReporte=?",$idReporte);
		$rowReporte = $tRE->fetchRow($select);
		
		if (is_null($rowReporte)) {
			return null;
		} else {
			return $rowReporte->toArray();
		}
	}
    
    public function obtenerReporteGeneral($idReporte) {
        $tablaReporteGral = $this->tablaReportesGenerales;
        $select = $tablaReporteGral->select()->from($tablaReporteGral)->where("idReportesGenerales=?",$idReporte);
        $rowReporte = $tablaReporteGral->fetchRow($select)->toArray();
        
        return $rowReporte;
    }
    
	/**
     * Obtiene los reportes de evaluacion obtenidos por un docente en el ciclo escolar especificado.
     */
	public function getReportesDocenteByIdCiclo($idCicloEscolar,$idDocente) {
		$tablaReporte = $this->tablaReporteEncuesta;
        $tablaGE = $this->tablaGrupoEscolar;
        // Solo seleccionamos los ids de los grupos escolares
        $select = $tablaGE->select()->from($tablaGE,array('idGrupoEscolar'))->where("idCicloEscolar=?",$idCicloEscolar);
        //print_r($select->__toString());
        $idsGruposEscolares = $tablaGE->fetchAll($select);
        //$idsGruposEscolares = array();
        $tablaAG = $this->tablaAsignacionGrupo;
        $select = $tablaAG->select()->from($tablaAG,array('idAsignacionGrupo'))->where("idGrupoEscolar IN (?)",$idsGruposEscolares->toArray())->where("idRegistro=?",$idDocente);
        $idsAsignacion = $tablaAG->fetchAll($select);
        //print_r($select->__toString());
        $tablaER = $this->tablaERealizadas;
        //$select = $tablaER->select()->from($tablaER)->where("idAsignacionGrupo IN (?)",$idsAsignacion->toArray());
        //print_r($select->__toString());
        //$idsEncuestasR =
        $select = $tablaReporte->select()->from($tablaReporte)->where("idAsignacionGrupo IN (?)",$idsAsignacion->toArray());
        $rowsReportes = $tablaReporte->fetchAll($select);
        //print_r($select->__toString());
        if(!is_null($rowsReportes)){
            return $rowsReportes->toArray();
        }
         
	}

    public function getReportesGrupo($idGrupo) {
        $tablaAsignacion = $this->tablaAsignacionGrupo;
        $select = $tablaAsignacion->select()->from($tablaAsignacion,array("idAsignacionGrupo"))->where("idGrupoEscolar=?",$idGrupo);
        //print_r($select->__toString());
        $rowsAsignacion = $tablaAsignacion->fetchAll($select);
        
        $tablaRE = $this->tablaReportesEncuesta;
        $select = $tablaRE->select()->from($tablaRE)->where("idAsignacionGrupo IN (?)", $rowsAsignacion->toArray());
        $rowsRepos = $tablaRE->fetchAll($select)->toArray();
        //print_r($rowsRepos);
        return $rowsRepos;
    }
    
    /**
     * Obtenemos los tipos de reportes que hay en el grado especificado
     */
    public function getTiposReportesGenerales($idGrado) {
        $tablaRG = $this->tablaReportesGenerales;
        $select = $tablaRG->select()->distinct()->from($tablaRG,'tipoReporte')->where("idGradoEscolar=?",$idGrado);
        $rowsTiposR = $tablaRG->fetchAll($select);
        
        return $rowsTiposR->toArray();
    }
    
    public function getReportesGenerales($idGrado) {
        $tablaRG = $this->tablaReportesGenerales;
        $select = $tablaRG->select()->from($tablaRG)->where("idGradoEscolar=?",$idGrado);
        $rowsReportesGenerales = $tablaRG->fetchAll($select)->toArray();
        
        return $rowsReportesGenerales;
    }
    
}
