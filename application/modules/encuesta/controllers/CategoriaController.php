<?php
/**
 * @author EnginnerRodriguez
 * Actualizado: Noviembre 2017
 * 
 */

class Encuesta_CategoriaController extends Zend_Controller_Action
{

    private $categoriaDAO = null;

    /**
     * Actualizado: Noviembre 2017
     */
    public function init()
    {
        /* Initialize action controller here */
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_helper->redirector->gotoSimple("index", "index", "encuesta");
		}
		$identity = $auth->getIdentity();
		
		$this->categoriaDAO = new Encuesta_Data_DAO_Categoria($identity['adapter']);
    }

    /**
     * Actualizado: Noviembre 2017
     */
    public function indexAction()
    {
        // action body
        $this->view->categorias = $this->categoriaDAO->getAllCategorias();
    }

    public function adminAction()
    {
        // action body
        $idCategoria = $this->getParam("ca");
		
        $categoria = $this->categoriaDAO->getCategoriaById($idCategoria);
		$opciones = $this->categoriaDAO->getOpcionesCategoria($idCategoria);
		
		$formulario = new Encuesta_Form_AltaCategoria();
		
		$formulario->getElement("categoria")->setValue($categoria["categoria"]);
		$formulario->getElement("descripcion")->setValue($categoria["descripcion"]);
		$formulario->getElement("submit")->setLabel("Actualizar Categoria");
		$formulario->getElement("submit")->setAttrib("class", "btn btn-warning");
		
		$this->view->categoria = $categoria;
		$this->view->opciones = $opciones;
		$this->view->formulario = $formulario;
    }

    public function altaAction()
    {
        // action body
        $request = $this->getRequest();
        if ($request->isPost()) {
            $datos = $request->getPost();
            $datos['idsOpciones'] = '';
            $datos['idOpcionMayor'] = 0;
            $datos['idOpcionMenor'] = 0;
            $datos["fecha"] = date("Y-m-d H:i:s", time());
            
            try{
                $this->categoriaDAO->addCategoria($datos);
                $this->view->messageSuccess = "La categoría <strong>".$datos["categoria"]."</strong> ha sido creada exitosamente.";
            }catch(Exception $ex){
                $this->view->messageFail = $ex->getMessage();
            }
        }
    }

    public function editaAction()
    {
        // action body
        $request = $this->getRequest();
		$idCategoria = $this->getParam("idCategoria");
        $post = $request->getPost();
		//eliminamos este elemento pues genera errores por que se toma como columna a editar
        unset($post["submit"]);
		$this->categoriaDAO->editarCategoria($idCategoria, $post);
		$this->_helper->redirector->gotoSimple("index", "categoria", "encuesta");
    }

    /**
     * Actualizado: Noviembre 2017
     */
    public function opcionesAction()
    {
        // action body
        $idCategoria = $this->getParam("ca");
        
		$categoria = $this->categoriaDAO->getCategoriaById($idCategoria);		
		$opciones = $this->categoriaDAO->getOpcionesCategoria($idCategoria);
		
		$this->view->categoria = $categoria;
		$this->view->opciones = $opciones;
		
    }

    public function normalizeAction()
    {
        // action body
    }


}

