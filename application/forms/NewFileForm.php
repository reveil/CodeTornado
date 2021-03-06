<?php
class NewFileForm extends Zend_Form
{
    const typeFile = 1;
    const typeDir = 2;
    const pattern = '/^[[:alnum:]]+[\.[:alnum:]]*$/';

    public function init()
    {
        parent::init();
        $this->setName('newFileForm');
        $this->setMethod('post');
        $this->setAction('ajax/newfile/');

        $name = new Zend_Form_Element_Text('name_file');
        $name->setLabel('Enter new file name');
        $name->addValidator('StringLength', true, array(1, 128));
        $name->addValidator('Regex', true, array('pattern' => self::pattern) );

        $cancel = new Zend_Form_Element_Button('cancel_file');
        $cancel->setLabel('Cancel');
        $cancel->setAttrib('onclick', 'hideNewFile()');
        $cancel->setAttrib('class', 'button');

        $submit = new Zend_Form_Element_Submit('new_file');
        $submit->setLabel('New');
        $submit->setAttrib('class', 'button');

        $this->setElements( array(
            $name,
            $cancel,
            $submit
        ));
    }
}