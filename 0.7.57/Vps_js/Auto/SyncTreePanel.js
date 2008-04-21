Vps.Auto.SyncTreePanel = Ext.extend(Vps.Binding.AbstractPanel, {

    layout: 'fit',

    initComponent : function()
    {
        this.addEvents(
            'selectionchange',
            'editaction',
            'addaction',
            'loaded'
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
            icon    : '/assets/silkicons/bullet_star.png',
            cls     : 'x-btn-icon',
            scope   : this
        });

        Ext.Ajax.request({
            mask: true,
            url: this.controllerUrl + '/jsonMeta',
            params: this.baseParams,
            success: this.onMetaChange,
            scope: this
        });
        Vps.Auto.SyncTreePanel.superclass.initComponent.call(this);
    },

    onMetaChange: function(response) {
        var meta = Ext.decode(response.responseText);
        this.icons = meta.icons;
        for (var i in this.icons) {
            if (i in this.actions) {
                this.actions[i].initialConfig.icon = this.icons[i];
            }
        }

        // Toolbar
        if (meta.buttons.each == undefined) { // Abfrage n�tig, falls keine Buttons geliefert
            var tbar = [];
            for (var button in meta.buttons) {
                tbar.add(this.getAction(button));
            }
        }
        // Tree
        baseParams = this.baseParams != undefined ? this.baseParams : {};
        if (this.openedId != undefined) { baseParams.openedId = this.openedId; }
        this.tree = new Ext.tree.TreePanel({
            border      : false,
//            animate     : true,
            loader      : new Ext.tree.TreeLoader({
                baseParams  : baseParams,
                dataUrl     : this.controllerUrl + '/jsonData'
            }),
            enableDD    : meta.enableDD,
            autoScroll: true,
            rootVisible : meta.rootVisible,
            tbar        : tbar
        });

        this.tree.setRootNode(
            new Ext.tree.AsyncTreeNode({
                text: meta.rootText,
                id: '0',
                allowDrag: false
            })
        );

        this.tree.getRootNode().expand();

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

        if (meta.rootVisible) {
            this.tree.getRootNode().ui.iconNode.style.backgroundImage = 'url(' + meta.icons.root + ')';
            this.tree.getRootNode().select();
        }
        //this.tree.getRootNode().expand();

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
        if (this.editDialog != undefined) {
            node = this.tree.getSelectionModel().getSelectedNode();
            this.editDialog.showEdit(node.id);
        } else {
            this.fireEvent('editaction', this.tree.getSelectionModel().getSelectedNode());
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
            url: this.controllerUrl + '/jsonNodeData',
            params: { node: id },
            success: function(r) {
                this.onSaved(Ext.decode(r.responseText).data);
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
                        url: this.controllerUrl + '/jsonDelete',
                        params: {
                            id: this.tree.getSelectionModel().getSelectedNode().id
                        },
                        success: function(r) {
                            this.onDeleted(Ext.decode(r.responseText));
                        },
                        scope: this
                    });
                }
            },
            this
        );
    },

    onMove : function(e){
        Ext.Ajax.request({
            url: this.controllerUrl + '/jsonMove',
            params: {
                source: e.dropNode.id,
                target: e.target.id,
                point: e.point
            },
            failure: function(r) {
                this.tree.getRootNode().reload();
            },
            scope: this
        })
        return true;
    },

    onCollapseNode : function(node) {
        Ext.Ajax.request({
            url: this.controllerUrl + '/jsonCollapse',
            params: {id: node.id}
        });
    },

    onExpandNode : function(node) {
        if (node.attributes.children && node.attributes.children.length > 0) {
            Ext.Ajax.request({
                url: this.controllerUrl + '/jsonExpand',
                params: {id: node.id}
            });
        }
    },

    onVisible : function (o, e) {
        Ext.Ajax.request({
            url: this.controllerUrl + '/jsonVisible',
            params: {
                id: this.tree.getSelectionModel().getSelectedNode().id
            },
            success: function(r) {
                response = Ext.decode(r.responseText);
                node = this.tree.getNodeById(response.id);
                node.attributes.visible = response.visible;
                this.setVisible(node);
            },
            scope: this
        })
    },

    setVisible : function (node) {
        if (node.attributes.visible) {
            node.ui.iconNode.style.backgroundImage = 'url(' + this.icons['default'] + ')';
        } else {
            node.ui.iconNode.style.backgroundImage = 'url(' + this.icons['invisible'] + ')';
        }
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

Vps.Auto.TreeNode = Ext.extend(Ext.tree.TreeNodeUI, {
    initEvents : function(){
        Vps.Auto.TreeNode.superclass.initEvents.call(this);
        this.node.ui.iconNode.style.backgroundImage = 'url(' + this.node.attributes.bIcon + ')';
    },
    onDblClick : function(e){
        e.preventDefault();
        this.fireEvent("dblclick", this.node, e);
    }
});

Ext.reg('vps.autotreesync', Vps.Auto.SyncTreePanel);
