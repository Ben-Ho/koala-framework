<?php
class Vps_Update_Action_Db_RenameField extends Vps_Update_Action_Db_Abstract
{
    public $field;
    public $newName;

    public function checkSettings()
    {
        parent::checkSettings();
        if (!$this->field) {
            throw new Vps_ClientException("Required parameter: field");
        }
        if (!$this->newName) {
            throw new Vps_ClientException("Required parameter: newName");
        }
    }

    public function update()
    {
        $table = $this->model->getRow($this->table);
        $field = $table->getChildRows('Fields', $this->model->select()
                    ->whereId($this->field))->current();
        if (!$field) {
            throw new Vps_ClientException("Field $this->field does not exist");
        }
        $field->field = $this->newName;
        $field->save();

        return array();
    }
}
