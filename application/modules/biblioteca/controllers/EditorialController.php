<?php

class Biblioteca_EditorialController extends Zend_Controller_Action
{
		
	private $editorialDAO;
	
	
    public function init()
    {
        /* Initialize action controller here */
         $dbAdapter = Zend_Registry::get("dbmodqueryb");
		 
		 $this->editorialDAO = new Biblioteca_DAO_Editorial($dbAdapter);
        
    }

    public function indexAction()
    {
        // action body
        
        $editoriales = $this->editorialDAO->getAllEditoriales();
        $this->view->editoriales = $editoriales;
    }

    public function altaAction()
    {
        // action body
        $request = $this->getRequest();
        $formulario = new Biblioteca_Form_AltaEditorial;
        $this->view->formulario = $formulario;
		
		if ($request->isPost()) {
            if ($formulario->isValid($request->getPost())) {
                $datos = $formulario->getValues();
                $datos["creacion"] = date("Y-m-d h:i:s",time());
                //print_r($datos);
                
                try{
                	
					 $this->editorialDAO->agregarEditorial($datos);
                    $this->view->messageSuccess = "Editorial dada  de alta exitosamente.";
                }catch(Exception $ex){
                    $this->view->messageFail = "Error en alta de editorial <strong>".$ex->getMessage()."</strong>";
                }
            }
        }
    }


}


