<?php

class Encuesta_JsonController extends Zend_Controller_Action
{

    private $encuestaDAO = null;

    private $gradosDAO = null;

    private $gruposDAO = null;

    private $cicloDAO = null;

    private $asignacionDAO = null;

    private $materiaDAO = null;

    private $reporteDAO = null;

    private $nivelDAO = null;

    private $encoder = null;

    private $evaluacionDAO = null;

    private $docenteDAO = null;

    private $dbConnector = null;

    private $serviceLogin = null;
    
    private $seccionEncuestaDAO;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $this->encoder = new App_Util_Encode;
        $this->serviceLogin = new App_Data_DAO_Login();
        
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity()){
            $testData = array('nickname' =>'test', 'password' => sha1('zazil'));
            $this->dbConnector = $this->serviceLogin->getTestConnector($testData);
            $dbAdapter = $this->dbConnector;
        }else{
            $identity = $auth->getIdentity();
            $dbAdapter = $identity['adapter'];
        }
        
        //$this->encuestaDAO = new Encuesta_DAO_Encuesta($dbAdapter);
        $this->encuestaDAO = new Encuesta_Data_DAO_Encuesta($dbAdapter);
        // $this->gradosDAO = new Encuesta_DAO_Grado($dbAdapter);
        $this->gradosDAO = new Encuesta_Data_DAO_GradoEducativo($dbAdapter);
        
		$this->gruposDAO = new Encuesta_DAO_Grupos($dbAdapter);
		$this->cicloDAO = new Encuesta_DAO_Ciclo($dbAdapter);
		$this->asignacionDAO = new Encuesta_DAO_AsignacionGrupo($dbAdapter);
        //$this->reporteDAO = new Encuesta_DAO_Reporte($dbAdapter);
		
		
		$this->materiaDAO = new Encuesta_DAO_Materia($dbAdapter);
		//$this->registroDAO = new Encuesta_DAO_Registro($dbAdapter);
        $this->nivelDAO = new Encuesta_Data_DAO_NivelEducativo($dbAdapter);
		
		$this->evaluacionDAO = new Encuesta_DAO_Evaluacion($dbAdapter);
		$this->docenteDAO = new Encuesta_Data_DAO_Docente($dbAdapter);
		
		$this->seccionEncuestaDAO = new Encuesta_Data_DAO_SeccionEncuesta($dbAdapter);
    }

    public function indexAction()
    {
        // action body
    }

    public function encrealizadasAction()
    {
        // action body
        $idGrupoEscolar = $this->getParam("idGrupo");
        $encuestaDAO = $this->encuestaDAO;
		$asignacionDAO = $this->asignacionDAO;
		//Materia y Docente
		$materiaDAO = $this->materiaDAO;
		$docenteDAO = $this->docenteDAO;
		
		$encuestasRealizadas = $encuestaDAO->getEncuestasRealizadasByIdGrupoEscolar($idGrupoEscolar);
		// array de encuestas realizadas extendido.
		$arrayERExt = array();
		foreach ($encuestasRealizadas as $er) {
			$asignacionGrupo = $asignacionDAO->getAsignacionById($er["idAsignacionGrupo"]);
			
			$materia = $materiaDAO->obtenerMateria($asignacionGrupo["idMateriaEscolar"]);
			$docente = $docenteDAO->getDocenteById($asignacionGrupo['idDocente']);
			$encuesta = $encuestaDAO->getEncuestaById($er["idEncuesta"]);
			
			$contenedor = array();
			$contenedor["asignacion"] = $er;
			$contenedor["encuesta"] = $encuesta->toArray();
			$contenedor["docente"] = $docente;
			$contenedor["materia"] = $materia->toArray();
			
			$arrayERExt[] = $contenedor;
		}
		
		echo Zend_Json::encode($arrayERExt);
    }

    public function gradosAction()
    {
        // action body
        $idNivel = $this->getParam("idNivel");
        $grados = $this->gradosDAO->getAllGradosEducativosByIdNivelEducativo($idNivel);
		
		echo Zend_Json::encode($grados);
    }

    public function gruposAction()
    {
        // action body
        $idCiclo = $this->getParam("idCiclo");
		$idGrado = $this->getParam("idGrado");
		if(is_null($idCiclo)) $idCiclo = $this->cicloDAO->getCurrentCiclo()->getIdCiclo();
		$grupos = $this->gruposDAO->obtenerGrupos($idGrado, $idCiclo);
		
		echo Zend_Json::encode($grupos);
    }

    public function reposdocenteAction()
    {
        // action body
        $request = $this->getRequest();
        
        $idDocente = $this->getParam("idDocente");
        $idCicloEscolar = $this->getParam("idCicloEscolar");
		
		// Obtenemos los reportes del docente mediante
        $reportes = $this->reporteDAO->getReportesDocenteByIdCiclo($idCicloEscolar, $idDocente);
		$reposEncuestas = array();
        
        foreach ($reportes as $key => $value) {
        	$container = array();
			$container["reporte"] = $value;
			//$reporte = new Encuesta_Model_Registro($value);
			
            //Obtenemos la encuesta
			$encuesta = $this->encuestaDAO->obtenerEncuestaById($value["idEncuesta"]);
			//$encModel = new Encuesta_Model_Encuesta($encuesta);
			//$encModel->toArray();
			$container["encuesta"] = $encuesta;
			$asignacion = $this->asignacionDAO->getAsignacionById($value["idAsignacionGrupo"]);
			//print_r($asignacion);
			$materia = $this->materiaDAO->getMateriaById($asignacion["idMateriaEscolar"]);
			//$materiaModel = new Encuesta_Model_Materia($materia);
			$grupoEscolar = $this->gruposDAO->obtenerGrupo($asignacion["idGrupoEscolar"]);
			//$grado = $this->gradosDAO->getGradoById($grupoEscolar->getIdGrado());
			$grado = $this->gradosDAO->getGradoEducativoById($grupoEscolar->getIdGrupo());
			$nivel = $this->nivelDAO->getNivelEducativoById($grado['idNivelEducativo']);
			
			$detalle = array();
			$detalle["materia"] = $materia; // materia viene como array
			$detalle["grupo"] = $grupoEscolar->toArray();
			$detalle["grado"] = $grado;
			$detalle["nivel"] = $nivel;
			
			$container["detalle"] = $detalle;
			$reposEncuestas[] = $container;
        }
		
		echo Zend_Json::encode($reposEncuestas);
    }

    public function evaluadoresAction()
    {
        // action body
        $nombres = $this->getParam("nombre");
		$apellidos = $this->getParam("apellidos");
		
        $evaluacionDAO = $this->evaluacionDAO;
		$evaluadores = $evaluacionDAO->getEvaluadoresByNombresOApellidos($nombres, $apellidos);
		
		if(is_null($evaluadores)){
			echo Zend_Json::encode(array());
		}else{
			echo Zend_Json::encode($evaluadores);
		}
        
    }

    public function evalsAction()
    {
        // action body
        $eval = $this->getParam("eval");
		$evaluadores = $this->evaluacionDAO->getEvaluadoresByString($eval);
		
		if(is_null($evaluadores)){
			echo Zend_Json::encode(array());
		}else{
			echo Zend_Json::encode($evaluadores);
		}
    }

    public function processformAction()
    {
        // action body
        $idEvaluador = $this->getParam("evaluador");
		$idAsignacion = $this->getParam("asignacion");
		$idConjunto = $this->getParam("conjunto");
		$idEvaluacion = $this->getParam("evaluacion");
        
        $request = $this->getRequest();
		$post = $request->getPost();
		
		$myData = $post["myData"];
		$contenedores = json_decode($myData,true);
		//$jsonEncuesta = $contenedores;
		//$jsonEncuesta = Zend_Json::encode($myData);
		$jsonEncuesta = json_encode(utf8_encode($myData));
		// $registrada = [true | false]
		$registrada = $this->evaluacionDAO->saveEncuestaEvaluador($idEvaluador, $idConjunto, $idEvaluacion, $idAsignacion, $jsonEncuesta);
		
		$msg = array();
		$msg["status"] = $registrada;
		
		echo Zend_Json::encode($msg);
    }

    public function reportesAction()
    {
        // action body
        $idCicloEscolar = $this->getParam("ce");
        $idGrupo = $this->getParam("ge");
        
        //$ciclo = $this->cicloDAO->getCicloById($idCicloEscolar);
        //$asignaciones = $this->asignacionDAO->obtenerAsignacionesGrupo($idGrupo);
        
        //print_r($asignaciones);
        $reportes = $this->reporteDAO->getReportesGrupo($idGrupo);
        
        echo Zend_Json::encode($reportes);
        
    }

    public function docentesAction()
    {
        // action body
        $param = $this->getParam("param");
        $docentes = $this->registroDAO->getDocentesByParam($param);
        
        echo Zend_Json::encode($docentes);
        /*
        if (!empty($docentes)) {
            
        }else{
            echo Zend_Json::encode(array());
        }
        */
    }

    public function tiposrepAction()
    {
        // action body
        $idGrado = $this->getParam("gr");
        $tipos = $this->reporteDAO->getReportesGenerales($idGrado);
        //print_r($tipos);
        
        echo Zend_Json::encode($tipos);
    }

    public function reportesgralsAction()
    {
        // action body
        $idGrado = $this->getParam("gr");
        $reportes = $this->reporteDAO->getReportesGenerales($idGrado);
        //print_r($tipos);
        
        echo Zend_Json::encode($reportes);
    }

    public function seccionesAction()
    {
        // action body
        $idEncuesta = $this->getParam('en');
        $secciones = $this->encuestaDAO->getSeccionesByIdEncuesta($idEncuesta);
        
        echo Zend_Json::encode($secciones);
    }

    public function grupossecAction()
    {
        // action body
        $idSeccion = $this->getParam('se');
        $gruposSeccion = $this->seccionEncuestaDAO->getGruposSeccionByIdSeccionEncuesta($idSeccion);
        
        echo Zend_Json::encode($gruposSeccion);
    }

    public function preguntasAction()
    {
        // action body
    }


}










