Ext.namespace('Vps.Auto');
Vps.Auto.SyncTreePanel = Ext.extend(Vps.Binding.AbstractPanel, {

    layout: 'fit',

    initComponent : function()
    {
        if (this.autoLoad !== false) {
            this.autoLoad = true;
        } else {
            delete this.autoLoad;
        }

        this.addEvents(
            'selectionchange',
            'editaction',
            'addaction'
        );
        this.actions['delete'] = new Ext.Action({
            text    : trlVps('Delete'),
            handler : this.onDelete,
            cls     : 'x-btn-text-icon',
            disabled: true,
            scope   : this
        });
        this.actions.add = new Ext.Action({
            text    : trlVps('Add'),
            handler : this.onAdd,
            cls     : 'x-btn-text-icon',
            scope   : this
        });
        this.actions.edit = new Ext.Action({
            text    : trlVps('Edit'),
            handler : this.onEdit,
            cls     : 'x-btn-text-icon',
            disabled: true,
            scope   : this
        });
        this.actions.invisible = new Ext.Action({
            text    : trlVps('Toggle Visibility'),
            handler : this.onVisible,
            cls     : 'x-btn-text-icon',
            disabled: true,
            scope   : this
        });
        this.actions.reload = new Ext.Action({
            text    : '',
            handler : function () { this.tree.getRootNode().reload(); },
            icon    : '/assets/silkicons/arrow_rotate_clockwise.png',
            cls     : 'x-btn-icon',
            tooltip : trlVps('Reload'),
            scope   : this
        });

        this.searchField = new Ext.form.TextField({'name':  'searchField'});
        this.searchField.on('render', function() {
            this.searchField.getEl().on('keyup', function(o, e) {
                this.onSearch(this.searchField.getValue(), this.getBaseParams());
            }, this, {buffer: 500});
        }, this);
        
        Vps.Auto.SyncTreePanel.superclass.initComponent.call(this);
    },
    
    doAutoLoad : function()
    {
        //autoLoad kann in der zwischenzeit abgeschaltet werden, zB wenn
        //wir in einem Binding sind
        if (!this.autoLoad) return;
        this.loadMeta();
    },

    loadMeta: function()
    {
        Ext.Ajax.request({
            mask: true,
            url: this.controllerUrl + '/json-meta',
            params: this.baseParams,
            success: this.onMetaChange,
            scope: this
        });
    },

    onMetaChange: function(response, options, meta) {
        this.icons = meta.icons;
        for (var i in this.icons) {
            if (i in this.actions) {
                this.actions[i].initialConfig.icon = this.icons[i];
            }
        }

        // Toolbar
        if (meta.buttons.each == undefined) { // Abfrage nötig, falls keine Buttons geliefert
            var tbar = [];
            for (var button in meta.buttons) {
                tbar.add(this.getAction(button));
            }
            if (meta.search) {
            	tbar.add(trlVps('Search: '));
            	tbar.add(this.searchField);
            }
        }
        
        // Tree
        var baseParams = this.baseParams != undefined ? this.baseParams : {};
        if (this.openedId != undefined) { baseParams.openedId = this.openedId; }
        this.tree = new Ext.tree.TreePanel({
            border      : false,
//            animate     : true,
            loader      : new Ext.tree.TreeLoader({
                baseParams  : baseParams,
                dataUrl     : this.controllerUrl + '/json-data'
            }),
            enableDD    : meta.enableDD,
            autoScroll: true,
            rootVisible : meta.rootVisible,
            tbar        : tbar,
            dropConfig  : meta.dropConfig
        });

        this.tree.setRootNode(
            new Ext.tree.AsyncTreeNode({
                text: meta.rootText,
                id: '0',
                allowDrag: false
            })
        );

        this.tree.getSelectionModel().on('selectionchange', this.onSelectionchange, this);
        this.tree.getSelectionModel().on('beforeselect', function(selModel, newNode, oldNode) {
            return this.fireEvent('beforeselectionchange', newNode.attributes.id);
        }, this);
        this.tree.on('beforenodedrop', this.onMove, this);
        this.tree.on('collapsenode', this.onCollapseNode, this);
        this.tree.on('expandnode', this.onExpandNode, this);

        this.tree.on('load', function(node) {
            if (this.openedId == node.id) {
                node.select();
            }
            return true;
        }, this);

        this.relayEvents(this.tree, ['click', 'dblclick']);

        this.add(this.tree);
        this.doLayout();

        this.tree.getRootNode().expand();
        if (meta.rootVisible) {
            this.tree.getRootNode().ui.iconNode.style.backgroundImage = 'url(' + meta.icons.root + ')';
            this.tree.getRootNode().select();
        }

        if (!this.editDialog && meta.editDialog) {
            this.editDialog = meta.editDialog;
        }
        if (this.editDialog && !(this.editDialog instanceof Vps.Auto.Form.Window)) {
            this.editDialog = new Vps.Auto.Form.Window(meta.editDialog);
        }
        if (this.editDialog) {
            this.editDialog.on('datachange', function(o) {
                if (o.data.addedId != undefined) {
                    id = o.data.addedId;
                } else {
                    id = this.tree.getSelectionModel().getSelectedNode().id;
                }
                this.onSave(id);
            }, this);
            this.tree.on('dblclick', function(grid, rowIndex) {
                this.onEdit();
            }, this);
            if (this.editDialog.applyBaseParams) {
                this.editDialog.applyBaseParams(this.getBaseParams());
            }
        }

        this.fireEvent('loaded', this.tree);
    },

    onEdit : function (o, e) {
        var node = this.tree.getSelectionModel().getSelectedNode();
        if (!node.id || node.id === 0 || node.id === '0') return;
        if (this.editDialog != undefined) {
            this.editDialog.showEdit(node.id);
        } else {
            this.fireEvent('editaction', node);
        }
    },

    onAdd: function (o, e) {
        if (this.editDialog != undefined) {
            this.editDialog.getAutoForm().applyBaseParams({
                parent_id: this.getSelectedId()
            });
            this.editDialog.showAdd();
        } else {
            this.fireEvent('addaction', this.tree.getSelectionModel().getSelectedNode());
        }
    },

    onSave : function (id)
    {
        Ext.Ajax.request({
            url: this.controllerUrl + '/json-node-data',
            params: Ext.apply({node:id}, this.getBaseParams()),
            success: function(response, options, result) {
                this.onSaved(result.data);
            },
            scope: this
        })
    },

    onSelectionchange: function (selModel, node) {
        if (node && node.id != 0) {
            this.getAction('edit').enable();
            this.getAction('invisible').enable();
            this.getAction('delete').enable();
        } else {
            this.getAction('edit').disable();
            this.getAction('invisible').disable();
            this.getAction('delete').disable();
        }
        this.fireEvent('selectionchange', node);
    },

    onDelete: function (o, e) {
        Ext.MessageBox.confirm(trlVps('Delete'), trlVps('Do you really want to delete this entry:\n\n"') + this.tree.getSelectionModel().getSelectedNode().text + '"',
            function  (button) {
                if (button == 'yes') {
                    Ext.Ajax.request({
                        url: this.controllerUrl + '/json-delete',
                        params: Ext.apply({id:this.getSelectedId()}, this.getBaseParams()),
                        success: function(response, options, result) {
                            this.onDeleted(result);
                        },
                        scope: this
                    });
                }
            },
            this
        );
    },

    onMove : function(dropEvent){
        var params = this.getBaseParams()
        params.source = dropEvent.dropNode.id;
        params.target = dropEvent.target.id;
        params.point = dropEvent.point;
        Ext.Ajax.request({
            url: this.controllerUrl + '/json-move',
            params: params,
            success: function(response, options, result) {
            	this.onMoved(result);
            },
            failure: function(r) {
                this.tree.getRootNode().reload();
            },
            scope: this
        });
        dropEvent.dropStatus = true;
        dropEvent.cancel = true;
        return true;
    },

    onCollapseNode : function(node) {
    	if (!node.attributes.search) {
            Ext.Ajax.request({
                url: this.controllerUrl + '/json-collapse',
                params: Ext.apply({id:node.id}, this.getBaseParams())
            });
    	}
    },

    onExpandNode : function(node) {
        if (node.attributes.children && node.attributes.children.length > 0 && !node.attributes.search) {
            Ext.Ajax.request({
                url: this.controllerUrl + '/json-expand',
                params: Ext.apply({id:node.id}, this.getBaseParams())
            });
        }
    },

    onVisible : function (o, e) {
        Ext.Ajax.request({
            url: this.controllerUrl + '/json-visible',
            params: Ext.apply({id:this.getSelectedId()}, this.getBaseParams()),
            success: function(response, options, result) {
                node = this.tree.getNodeById(result.id);
                node.attributes.visible = result.visible;
                node.ui.iconNode.style.backgroundImage = 'url(' + result.icon + ')';
            },
            scope: this
        })
    },

    onSearch : function (o, e) {
    	this.baseParams['searchValue'] = o;
    	this.tree.getRootNode().reload();
    },

    getTree : function() {
        return this.tree;
    },
    getSelectionModel : function() {
        if (!this.getTree()) return null;
        return this.getTree().getSelectionModel();
    },
    getSelectedNode : function() {
        if (!this.getSelectionModel()) return null;
        return this.getSelectionModel().getSelectedNode();
    },

    //für AbstractPanel
    getSelectedId: function() {
        var s = this.getSelectedNode();
        if (s) return s.id;
        return null;
    },

    //für AbstractPanel
    selectId: function(id) {
        if (id) {
            if (this.getTree()) {
                var n = this.getTree().getNodeById(id);
                if (n) {
                    n.select();
                }
            } else {
                this.openedId = id;
            }
        } else {
            var m = this.getSelectionModel();
            if (m) m.clearSelections();
        }
    },

    onMoved : function (response)
    {
        var parent = this.tree.getNodeById(response.parent);
        var node = this.tree.getNodeById(response.node);
        var before = this.tree.getNodeById(response.before);
        parent.insertBefore(node, before);
        parent.expand();
    },

    onSaved : function (response)
    {
        this.tree.getRootNode().reload();
    },

    onDeleted: function (response) {
        this.tree.getRootNode().reload();
    },

    setBaseParams : function(baseParams) {
        Vps.Auto.SyncTreePanel.superclass.setBaseParams.apply(this, arguments);
        if (this.editDialog && this.editDialog.setBaseParams) {
            this.editDialog.setBaseParams(baseParams);
        }
    },
    applyBaseParams : function(baseParams) {
        Vps.Auto.SyncTreePanel.superclass.applyBaseParams.apply(this, arguments);
        if (this.editDialog && this.editDialog.applyBaseParams) {
            this.editDialog.applyBaseParams(baseParams);
        }
    }

});
Ext.reg('vps.autotreesync', Vps.Auto.SyncTreePanel);
