Vps.Binding.ProxyPanel = Ext.extend(Vps.Binding.AbstractPanel,
{
    mabySubmit: function() {
        return this.proxyItem.mabySubmit.apply(this.proxyItem, arguments);
    },
    submit: function() {
        return this.proxyItem.submit.apply(this.proxyItem, arguments);
    },
    reset: function() {
        return this.proxyItem.reset.apply(this.proxyItem, arguments);
    },
    load: function() {
        return this.proxyItem.load.apply(this.proxyItem, arguments);
    },
    reload: function() {
        return this.proxyItem.reload.apply(this.proxyItem, arguments);
    },
    getSelectedId: function() {
        return this.proxyItem.getSelectedId.apply(this.proxyItem, arguments);
    },
    selectId: function(id) {
        return this.proxyItem.selectId.apply(this.proxyItem, arguments);
    },
    isDirty: function() {
        return this.proxyItem.isDirty.apply(this.proxyItem, arguments);
    },
    applyBaseParams: function() {
        return this.proxyItem.applyBaseParams.apply(this.proxyItem, arguments);
    },
    setBaseParams : function(baseParams) {
        return this.proxyItem.setBaseParams.apply(this.proxyItem, arguments);
    },
    getBaseParams : function() {
        return this.proxyItem.getBaseParams.apply(this.proxyItem, arguments);
    },
    hasBaseParams: function() {
        return this.proxyItem.hasBaseParams.apply(this.proxyItem, arguments);
    }
});
