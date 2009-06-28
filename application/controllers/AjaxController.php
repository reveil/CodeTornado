<?php
require_once MODEL_PATH . 'FileNavigation.php';
require_once MODEL_PATH . 'BranchNavigation.php';
require_once MODEL_PATH . 'HistoryNavigation.php';
require_once MODEL_PATH . 'Remotes.php';
require_once FORM_PATH . 'NewFileForm.php';
require_once FORM_PATH . 'NewDirForm.php';
require_once FORM_PATH . 'NewBranchForm.php';
class AjaxController extends MainController
{
    /**
     * Temporary action while siple ajax in the prototype is based on innerHTML
     * TODO: rewrite all actions to use JSON helper and remove this method
    */
    public function postDispatch()
    {
        $this->_helper->layout->disableLayout();
    }

    /**
     * Check if a user is logged in and a project is set as active
    */
    private function _check()
    {
        if( ! ( isset($this->_user->loggedIn) && isset($this->_storage->project->pid) ) )
        {
            throw new Exception('AJAX: User or project not active in session');
        }
    }

    /**
     * Universal method used by file / directory creation
    */
    private function _newFileDir( $form, $newMethod )
    {
        $this->_check();
        $historyNavigation = new HistoryNavigation();
        $branchNavigation = new BranchNavigation();
        $fileNavigation = new FileNavigation();
        $request = $this->getRequest();
        if( $request->isPost() )
        {
            $post = $request->getPost();
            if( $form->isValid($post) )
            {
                $fileNavigation->$newMethod($post);
            }
        }
        $this->view->path = '/' . $fileNavigation->getDir();
        $this->view->files = $fileNavigation->ls();
        $this->view->branch = $branchNavigation->getActiveBranch();
        $this->view->branches = $branchNavigation->getBranches();
        $this->view->history = $historyNavigation->getHistory();
        $this->view->headName = $historyNavigation->getHeadName();
    }

    /**
     * Create a new branch from the current HEAD
    */
    public function newbranchAction()
    {
        $this->_check();
        $form = new NewBranchForm();
        $historyNavigation = new HistoryNavigation();
        $branchNavigation = new BranchNavigation();
        $fileNavigation = new FileNavigation();
        $request = $this->getRequest();
        if( $request->isPost() )
        {
            $post = $request->getPost();
            if( $form->isValid($post) )
            {
                $branchNavigation->newBranch($post);
            }
        }
        $this->view->path = '/' . $fileNavigation->getDir();
        $this->view->files = $fileNavigation->ls();
        $this->view->branch = $branchNavigation->getActiveBranch();
        $this->view->branches = $branchNavigation->getBranches();
        $this->view->history = $historyNavigation->getHistory();
        $this->view->headName = $historyNavigation->getHeadName();
    }

    /**
     * Create a new file
    */
    public function newfileAction()
    {
        $this->_newFileDir( new NewFileForm(), 'newfile' );
    }

    /**
     * Create a new directory
    */
    public function newdirAction()
    {
        $this->_newFileDir( new NewDirForm(), 'newdir' );
    }

    /**
     * TODO: test
    */
    public function enterdirAction()
    {
        if( $request->getQuery('dir') != NULL )
        {
            $fileNavigation->enterDir( $request->getQuery('dir') );
        }
    }

    /**
     * TODO: test
    */
    public function getfileAction()
    {

        if( $request->getQuery('file') != NULL )
        {
            if( $fileNavigation->validFile( $request->getQuery('file') ) )
            {
                $io->setFile( $fileNavigation->getPath(), $request->getQuery('file') );
            }
        }
    }

    /**
     * TODO: test
    */
    public function saveAction()
    {
        $request = $this->getRequest();
        $io = new RawIO();
        if( $request->isPost() )
        {
            $post = $request->getPost();
            $io->saveContent($code);
        }
    }

    /**
     * TODO: finish status
    */
    public function updateAction()
    {
        $result = array();
        $remotes = new Remotes();
        $remotes->setUid( $this->_user->loggedIn->uid );
        $result['remotes'] = $remotes->getRemotes();
        $result['avail']['uid'] = array_keys( $remotes->getRepos() );
        $result['avail']['user'] = array_values( $remotes->getRepos() );
        $result['status'] = array();
        $this->_helper->json($result);
    }
}
