<?php
require_once FORM_PATH . 'NewFileForm.php';
require_once MODEL_PATH . 'Git.php';
require_once MODEL_PATH . 'SessionStorage.php';
class FileNavigation
{
    private $_storage;
    private $_pathBase;
    private $_path;
    private $_dir;
    private $_dirArray = array();
    private $_files;
    private $_dirs;
    private $_file;
    private $_logger;

    /**
     * Constructor recreates current directory array
    */
    function __construct()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_storage = SessionStorage::getInstance();
        $this->_pathBase = $this->_storage->getDataPath();
        //$this->_logger->log($this->_pathBase, Zend_Log::INFO);
        $this->_dirArray = $this->_storage->getDirArray();
        $this->_updatePath();
    }

    /**
     * Update the _path variable and store the directory array in the session
    */
    private function _updatePath()
    {
        $this->_storage->path->dirArray = isset($this->_dirArray) ? $this->_dirArray : array();
        $this->_path = $this->_pathBase;
        $this->_dir = '';
        foreach( $this->_dirArray as $dir )
        {
            $this->_path .= $dir . '/';
            $this->_dir .= $dir . '/';
        }
        //$this->_storage->setLocalPath($this->_dir);
    }

    /**
     * Enter a directory that exists in the current listing
    */
    public function enterDir($dirname)
    {
        $this->ls();
        $key = array_search($dirname, $this->_dirs);
        if( $key !== false )
        {
            $this->_dirArray[] = $this->_dirs[$key];
            $this->_updatePath();
        }
        else
        {
            throw new Exception('Error: The specified directory does not exist');
        }
    }

    public function validFile($filename)
    {
        $result = false;
        $this->ls();
        $key = array_search($filename, $this->_files);
        if( $key !== false )
        {
            $result = true;
        }
        else
        {
            throw new Exception('Error: Specified file is not valid');
        }
        return( $result );
    }

    public function newFile($formData)
    {
        $result = false;
        if( is_array($formData) && isset($formData['name_file']) )
        {
            touch( $this->getPath() . $formData['name_file'] );
            $git = new Git();
            $result = $git->addFile( $this->getPath() . $formData['name_file'] );
        }
        else
        {
            throw new Exception('New file form data in incorrect format');
        }
        return($result);
    }

    public function deleteFile($formData)
    {
        $result = false;
        if( is_array($formData) && isset($formData['name']) )
        {
            $git = new Git();
            $result = $git->rmFile( $this->getPath() . $formData['name'] );
        }
        else
        {
            throw new Exception('New file form data in incorrect format');
        }
        return($result);
    }

    public function newDir($formData)
    {
        if( is_array($formData) && isset($formData['name_dir']) )
        {
            if( ! mkdir( $this->getPath() . $formData['name_dir'] ) )
            {
                throw new Exception('Error creating directory ' . $this->getPath() . $formData['name_dir'] );
            }
        }
        else
        {
            throw new Exception('New directory form data in incorrect format');
        }
    }

    /**
     * Go up dir equals cd ..
    */
    public function upDir($count)
    {
        for($i=0; $i<$count; ++$i )
        {
            if( count($this->_dirArray) > 0 )
            {
                array_pop( $this->_dirArray );
            }
        }
        $this->_updatePath();
    }

    /**
     * Get method for the _path
    */
    public function getPath()
    {
        return( $this->_path );
    }

    /**
     * Get the dir without the path base and project/user directory
    */
    public function getDir()
    {
        return( $this->_dir );
    }

    /**
     * Return the listing of current directory. Also updates _dirs and _files
    */
    public function ls()
    {
        $result = array( 'dirs' => array(), 'files' => array() );
        $content = scandir( $this->_path );
        if( is_array($content) && count($content) > 0 )
        {
            foreach( $content as $name )
            {
                if( $name !== '.' && $name !== '..' && $name !== '.git' )
                {
                    if( is_dir($this->_path . '/' .$name) )
                    {
                        $result['dirs'][] = $name;
                    }
                    else
                    {
                        $result['files'][] = $name;
                    }
                }
            }
        }
        //store the values in the model for further request
        $this->_files = $result['files'];
        $this->_dirs = $result['dirs'];
        return( $result );
    }


}