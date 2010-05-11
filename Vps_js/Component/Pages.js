Ext.namespace('Vps.Component');
Vps.Component.Pages = Ext.extend(Ext.Panel, {
    initComponent : function()
    {
        this.actions = {};
        this.componentConfigs = {};

        this.pageButtonMenu = new Ext.menu.Menu({
            items    : [
                '-',
                this.getAction('properties'),
                this.getAction('add'),
                this.getAction('delete'),
                this.getAction('visible'),
                this.getAction('makeHome'),
                this.getAction('preview')
            ]
        });
        this.pageButton = new Ext.Toolbar.Button({
            cls     : 'x-btn-text-icon bmenu',
            text    : trlVps('Page'),
            menu    : this.pageButtonMenu,
            icon    : '/assets/silkicons/page.png',
            disabled: true
        });

        this.treePanel = new Vps.Auto.TreePanel({
            controllerUrl: '/admin/component/pages',
            title       : trlVps('Seitenbaum'),
            region      : 'west',
            split       : true,
            width       : 300,
            collapsible : true,
            minSize     : 200,
            maxSize     : 600,
            autoScroll: true,
            tbar: [this.pageButton, '->']
        });
        this.treePanel.getTopToolbar().add(this.treePanel.getAction('reload'));
        this.treePanel.onMoved = function (response) {
            this.tree.getRootNode().reload();
        };

        this.contentTabPanel = new Ext.TabPanel({
            region      : 'center'
        });
        this.contentTabPanel.on('render', function() {
            this.contentTabPanel.strip.setHeight(24);
        }, this);

        this.layout = 'border';
        this.items = [this.treePanel, this.contentTabPanel];

        Vps.Component.Pages.superclass.initComponent.call(this);

        this.treePanel.on('loaded', this.onTreePanelLoaded, this);
        this.setupEditform();

        this.editActions = {};
    },
    setupEditform : function ()
    {
       this.editDialog = new Vps.Auto.Form.Window({
            width: 400,
            height: 400,
            controllerUrl: ' '
        });
        this.editDialog.on('datachange', function(test) {
            this.treePanel.tree.root.reload();
        }, this);
    },

    onTreePanelLoaded : function(tree)
    {
        tree.loader.on('load', function(tree, node, response) {
            var result = Ext.decode(response.responseText);
            Ext.applyIf(this.componentConfigs, result.componentConfigs);
        }, this);

        this.contextMenu = new Ext.menu.Menu({
             items: [
                '-',
                this.getAction('properties'),
                this.getAction('add'),
                this.getAction('delete'),
                this.getAction('visible'),
                this.getAction('makeHome'),
                this.getAction('preview'),
                '-',
                this.getAction('expand'),
                this.getAction('collapse')
            ]
        });

        tree.on('click', this.treeSelectionchange, this);
        tree.on('contextmenu', function (node) {
            node.select();
            this.treeSelectionchange(node);
            this.contextMenu.show(node.ui.getAnchor());
        }, this);

        tree.on('dblclick', function (o, e) {
            var action;
            for (var i in this.editActions) {
                if (!this.editActions[i].isHidden()) {
                    action = this.editActions[i];
                }
            }
            if (action) {
                action.execute(action.initialConfig);
            }
        }, this);

    },

    treeSelectionchange : function (node) {
        if (!node) return;
        var data = node.attributes;
        
        this.editDialog.getAutoForm().editControllerUrl = data.editControllerUrl;
        this.editDialog.getAutoForm().addControllerUrl = data.addControllerUrl;
        
        if (data.disabled) {
            this.pageButton.disable();
        } else {
            this.pageButton.enable();
        }
        
        for (var action in data.actions) {
        	if (data.actions[action]) {
                this.getAction(action).enable();
        	} else {
                this.getAction(action).disable();
        	}
        }

        for (var i in this.editActions) {
            this.editActions[i].hide();
        }
        data.editComponents.each(function(editComponent) {
            var actionKey = editComponent.componentClass+'-'+editComponent.type;
            if (!this.editActions[actionKey]) {
                this.editActions[actionKey] = new Ext.Action({
                    text    : this.componentConfigs[actionKey].title,
                    handler : function (o, e) {
                        var node = this.treePanel.tree.getSelectionModel().getSelectedNode();
                        node.attributes.editComponents.each(function(editComponent) {
                            if (editComponent.componentClass+'-'+editComponent.type == o.actionKey) {
                                this.loadComponent({
                                    id: editComponent.componentId,
                                    componentClass: editComponent.componentClass,
                                    type: editComponent.type,
                                    text: node.text,
                                    icon: node.attributes.bIcon,
                                    editComponents: node.attributes.editComponents,
                                    pageId: node.attributes.id
                                });
                                return false;
                            }
                        }, this);
                    },
                    icon    : this.componentConfigs[actionKey].icon,
                    cls     : 'x-btn-text-icon',
                    scope   : this,
                    disabled : data.disabled,
                    actionKey: actionKey
                });
                this.contextMenu.insert(0, new Ext.menu.Item(this.editActions[actionKey]));
                this.pageButtonMenu.insert(0, new Ext.menu.Item(this.editActions[actionKey]));
            }
            this.editActions[actionKey].show();
        }, this);
    },

    loadComponent: function(data)
    {
        var panel = this.contentTabPanel.getItem('page'+data.pageId);
        if (!panel) {
            panel = this.createComponentPanel(data);
            this.contentTabPanel.add(panel);
        }
        this.contentTabPanel.setActiveTab(panel);

        panel.applyBaseParams({
            id: data.id
        });
        panel.mainComponentText = data.text;
        panel.mainComponentIcon = data.icon;
        panel.load({
            componentClass: data.componentClass,
            type: data.type,
            editComponents: data.editComponents
        });
    },

    createComponentPanel: function(data)
    {
        var panel = new Vps.Component.ComponentPanel({
            id          : 'page'+data.pageId,
            title       : data.text,
            closable    : true,
            autoLoad    : false,
            componentConfigs : this.componentConfigs
        });
        return panel;
    },

    getAction : function(type)
    {
        if (this.actions[type]) return this.actions[type];

        if (type == 'properties') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Properties of selected Page'),
                handler : function () {
                    var form = this.editDialog.getAutoForm();
                    form.controllerUrl = form.editControllerUrl;
                    if (form.formPanel) {
                        form.remove(form.formPanel, true);
                        this.editDialog.getAutoForm().formPanel = null;
                    }
                    var node = this.treePanel.tree.selModel.selNode;
                    this.editDialog.getAutoForm().setBaseParams({
                        componentId: node.attributes.editControllerComponentId
                    });
                    this.editDialog.showEdit(node.id);
                },
                icon    : '/assets/silkicons/page_gear.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'add') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Add new Subpage'),
                handler : function () {
            		var form = this.editDialog.getAutoForm();
                    form.controllerUrl = form.addControllerUrl;
	                if (form.formPanel) {
	                    form.remove(form.formPanel, true);
	                    this.editDialog.getAutoForm().formPanel = null;
	                }
                    var node = this.treePanel.tree.selModel.selNode;
                    this.editDialog.getAutoForm().setBaseParams({
                        componentId: node.attributes.editControllerComponentId,
                        parent_id: node.id
                    });
                    this.editDialog.showAdd();
                },
                icon    : '/assets/silkicons/page_add.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'delete') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Delete selected Page'),
                handler : function() {
                    this.treePanel.onDelete();
                },
                icon    : '/assets/silkicons/page_delete.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'visible') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Toggle Visibility of selected Page'),
                handler : function() {
                    this.treePanel.onVisible();
                },
                icon    : '/assets/fx_invisible/silkicons/page.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'makeHome') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Make selected Page Homepage'),
                handler : function (o, e) {
                    Ext.Ajax.request({
                        url: '/admin/component/pages/json-make-home',
                        success: function(r) {
                            response = Ext.decode(r.responseText);
                            var oldhome = this.treePanel.tree.getNodeById(response.oldhome);
                            oldhome.attributes.visible = response.oldhomeVisible;
                            this.treePanel.setVisible(oldhome);
                            var home = this.treePanel.tree.getNodeById(response.home);
                            home.ui.iconNode.style.backgroundImage = 'url(/assets/silkicons/application_home.png)';
                        },
                        params: {id: this.treePanel.tree.getSelectionModel().getSelectedNode().id },
                        scope: this
                    });
                },
                icon    : '/assets/silkicons/application_home.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'expand') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Expand here'),
                handler : function () { this.treePanel.tree.getSelectionModel().getSelectedNode().expand(true); },
                icon    : '/assets/silkicons/bullet_add.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'collapse') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Collapse here'),
                handler : function () { this.treePanel.tree.getSelectionModel().getSelectedNode().collapse(true); },
                icon    : '/assets/silkicons/bullet_delete.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else if (type == 'preview') {
            this.actions[type] = new Ext.Action({
                text    : trlVps('Open Preview'),
                handler : function () {
                    window.open('/admin/component/pages/open-preview?page_id='+
                                this.treePanel.getSelectedId());
                },
                icon    : '/assets/silkicons/page_white_magnify.png',
                cls     : 'x-btn-text-icon',
                scope   : this
            });
        } else {
            throw 'unknown action-type: ' + type;
        }
        return this.actions[type];
    }

});

Ext.reg('vps.component.pages', Vps.Component.Pages);
