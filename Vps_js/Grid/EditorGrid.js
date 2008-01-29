//Zusätzliches AfterEditComplete event, das auch gefeuert wird, wenn edit
//abgebrochen wurde bzw. der wert nicht geändert wurde
//(das normale afteredit wird nur gefeuert wenn wert geändert wurde)
//
//wird benötigt um den save-button im grid wida zu disablen wenn nix geändert wurde

Ext.grid.EditorGridPanel.baseOnEditComplete = Ext.grid.EditorGridPanel.prototype.onEditComplete;
Ext.grid.EditorGridPanel.prototype.onEditComplete = function(ed, value, startValue){
    Ext.grid.EditorGridPanel.baseOnEditComplete.apply(this, arguments);
    this.fireEvent("aftereditcomplete", ed, value, startValue);
};
