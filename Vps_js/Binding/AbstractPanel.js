Vps.Binding.AbstractPanel = function(config) {
    if (!config.actions) this.actions = {}; //muss hier sein
    Vps.Binding.AbstractPanel.superclass.constructor.apply(this, arguments);
};
Ext.extend(Vps.Binding.AbstractPanel, Ext.Panel,
{
    checkDirty: true,

    initComponent: function() {
        this.activeId = null;

        this.addEvents(
            /**
            * @event datachange
            * Daten wurden geändert, zB um grid neu zu laden
            * @param {Object} result from server
            */
            'datachange',
            'selectionchange',
            'beforeselectionchange',
            'loaded'
        );
        var binds = this.bindings;
        this.bindings = new Ext.util.MixedCollection();
        if (binds) {
            this.addBinding.apply(this, binds);
        }
        this.on('selectionchange', function() {
            var id = this.getSelectedId();
            if (id) {
                this.activeId = id;
                this.bindings.each(function(b) {
                    b.item.enable();
                    if (b.item.ownerCt instanceof Ext.TabPanel) {
                        if (b.item.ownerCt.getActiveTab() != b.item) {
                            //dieses binding überspringen, liegt in einem
                            //tab der nicht aktiv ist
                            return;
                        }
                    }
                    this._loadBinding(b);
                }, this);
            }
        }, this, {buffer: 500});
        this.on('beforeselectionchange', function(id) {
            var ret = true;
            this.bindings.each(function(b) {
                if (!b.item.mabySubmit({
                    callback: function() {
                        b.item.reset();
                        this.selectId(id);
                    },
                    scope: this
                })) {
                    ret = false;
                    return false; //break each
                }
            }, this)
            return ret;
        }, this);

        if (!this.baseParams) {
            this.baseParams = {};
        }

        Vps.Binding.AbstractPanel.superclass.initComponent.call(this);
    },

    //private
    _loadBinding: function(b)
    {
        var params = {};
        if (b.componentIdSuffix) {
            params.componentId =
                this.getBaseParams()['componentId'] +
                String.format(b.componentIdSuffix, this.activeId);
        } else if (b.componentId) {
            params.componentId =
                String.format(b.componentId, this.activeId);
        } else {
			//this.getGrid().getSelectionModel().getSelections()[0].data[b.valueParam];
            params[b.queryParam] = this.activeId;
        }
        if (!b.item.hasBaseParams(params)) {
            b.item.applyBaseParams(params);
            b.item.load();
        }
    },
    addBinding: function() {
        for(var i = 0; i < arguments.length; i++){
            var b = arguments[i];
            if (b instanceof Vps.Binding.AbstractPanel) {
                b = {item: b};
            }
            if (!b.queryParam) b.queryParam = 'id';
            b.item.disable();
            b.item.setAutoLoad(false);
            this.bindings.add(b);

            b.item.on('datachange', function(result)
            {
                if (result && result.data && result.data.addedId) {

                    //nachdem ein neuer eintrag hinzugefügt wurde die anderen reloaden
                    this.activeId = result.data.addedId;

                    //neuladen und wenn geladen den neuen auswählen
                    this.reload({
                        callback: function() {
                            this.selectId(this.activeId);
                        },
                        scope: this
                    });

                    //die anderen auch neu laden
                    this.bindings.each(function(b) {
                        b.item.enable();
                        if (b.item.ownerCt instanceof Ext.TabPanel) {
                            if (b.item.ownerCt.getActiveTab() != b.item) {
                                //dieses binding überspringen, liegt in einem
                                //tab der nicht aktiv ist
                                return;
                            }
                        }
                        this._loadBinding(b);
                    }, this);
                } else {
                    this.reload();
                }
            }, this);

            if (b.item instanceof Vps.Auto.FormPanel) {
                b.item.on('addaction', function(form) {
                    this.activeId = 0;
                    this.selectId(0);
                    //bei addaction die anderen disablen
                    this.bindings.each(function(i) {
                        if (i.item != form) {
                            i.item.disable();
                        }
                    }, this);
                }, this);

                b.item.on('deleteaction', function(form) {
                    this.activeId = null;
                    this.selectId(null);
                    //wenn gelöscht alle anderen disablen
                    this.bindings.each(function(i) {
                        i.item.disable();
                    }, this);
                }, this);
            }
            b.item.on('activate', function(item) {
                this.bindings.each(function(i) {
                    if (i.item == item) {
                        if (!item.disabled) {
                            this._loadBinding(i);
                        }
                        return false;
                    }
                }, this);
            }, this);
        }
    },
    removeBinding: function(autoPanel) {
        //todo
    },

    getAction : function(type)
    {
        if (this.actions[type]) return this.actions[type];
    },

    //deprecated
    mabySave : function(callback, callCallbackIfNotDirty)
    {
        var ret = this.mabySubmit(callback.callback, callback.scope || this);
        if(typeof callCallbackIfNotDirty == 'undefined') callCallbackIfNotDirty = true;
        if (ret && callCallbackIfNotDirty) {
            callback.callback.call(callback.scope);
        }
        return ret;
    },

    mabySubmit : function(cb, options)
    {
        if (this.checkDirty && !this.disabled && this.isDirty()) {
            Ext.Msg.show({
            title:trlVps('Save'),
            msg: trlVps('Do you want to save the changes?'),
            buttons: Ext.Msg.YESNOCANCEL,
            scope: this,
            fn: function(button) {
                if (button == 'yes') {
                    if (!options) options = {};
                    options.success = cb.callback;
                    options.scope = cb.scope;
                    this.submit(options);
                } else if (button == 'no') {
                    cb.callback.call(cb.scope || this);
                } else if (button == 'cancel') {
                    //nothing to do, action allread canceled
                }
            }});
            return false;
        }
        return true;
    },

    submit: function(options) {
    },
    reset: function() {
    },
    load: function(params, options) {
    },
    reload : function(options) {
        this.load(null, options);
    },
    getSelectedId: function() {
    },
    selectId: function(id) {
    },
    isDirty: function() {
        return false;
    },
    setBaseParams : function(baseParams) {
        this.baseParams = {};
        this.applyBaseParams(baseParams);
    },
    applyBaseParams : function(baseParams) {
        if (!this.baseParams) { this.baseParams = {}; }
        Ext.apply(this.baseParams, baseParams);
    },
    getBaseParams : function() {
        return this.baseParams || {};
    },

    //um herauszufinden ob params neue baseParams sind
    //wird zB in ComponentPanel überschrieben
    //getBaseParams von aussen holen und mit den neuen vergleichen geht im ComponentPanel
    //nicht, da diese anders gespeichert werden
    hasBaseParams : function(params) {
        var baseParams = this.getBaseParams();
        for (var i in params) {
            if (params[i] != baseParams[i]) return false;
        }
        return true;
    },

    setAutoLoad: function(v) {
        this.autoLoad = v;
    },
    getAutoLoad: function() {
        return this.autoLoad;
    }
});
