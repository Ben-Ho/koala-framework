<?php
class Vps_Update_Action_Db_DropTable extends Vps_Update_Action_Db_Abstract
{
    public function update()
    {
        $table = $this->model->getRow($this->table);
        if (!$table) {
            throw new Vps_ClientException("Table '$this->table' does not exist");
        }
        $table->delete();
        return array();
    }
}
