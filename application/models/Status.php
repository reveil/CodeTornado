<?php
require_once MODEL_PATH . 'DbModel.php';
class Status extends DbModel
{
    private $_pid;
    private $_uid;

    protected function init()
    {
        $this->_pid = $this->_storage->project->pid;
    }

    public function setUid($uid)
    {
        $this->_uid = $uid;
    }

    public function addStatus($msg)
    {
        $data = array(
            'pid' => $this->_pid,
            'uid' => $this->_uid,
            'action' => $msg
        );
        if( isset($this->_pid) )
        {
            $this->_db->insert('status', $data);
        }
    }

    public function getStatusMessages()
    {
        $sql = 'SELECT sid, t, action, name FROM status, users WHERE users.uid=status.uid AND pid=? ORDER BY sid DESC LIMIT 0, 5';
        $result = $this->_db->fetchAll($sql, array($this->_pid));
        return($result);
    }
}