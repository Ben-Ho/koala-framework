Ext.namespace('Vpc.Abstract.List');
Vpc.Abstract.List.Panel = Ext.extend(Vps.Binding.ProxyPanel,
{
    initComponent: function()
    {
        this.childPanel = Ext.ComponentMgr.create(Ext.applyIf(this.childConfig, {
            region: 'center'
        }));

        this.grid = new Vps.Auto.GridPanel({
            controllerUrl: this.controllerUrl,
            width: 300,
            split: true,
            region: 'west',
            baseParams: this.baseParams, //Kompatibilität zu ComponentPanel
            autoLoad: this.autoLoad,
            bindings: [{
                item        : this.childPanel,
                componentIdSuffix: '-{0}'
            }],
            onAdd: this.onAdd
        });
        this.proxyItem = this.grid;

        this.layout = 'border';
        this.items = [this.grid, this.childPanel];
        Vpc.Abstract.List.Panel.superclass.initComponent.call(this);
    },

    load: function()
    {
        this.grid.load();
        this.grid.selectId(false);

        this.childPanel.setBaseParams({});
        var f = this.childPanel.getForm();
        if (f) {
            f.clearValues();
            f.clearInvalid();
        }
        this.childPanel.disable();
    },

    onAdd : function()
    {
        Ext.Ajax.request({
            mask: true,
            url: this.controllerUrl + '/json-insert',
            params: this.getBaseParams(),
            success: function(response, options, r) {
                this.getSelectionModel().clearSelections();
                this.reload({
                    callback: function(o, r, s) {
                        this.getSelectionModel().selectLastRow();
                    },
                    scope: this
                });
            },
            scope: this
        });
    }
});
Ext.reg('vpc.list', Vpc.Abstract.List.Panel);
