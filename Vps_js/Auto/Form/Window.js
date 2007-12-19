Ext.namespace('Vps.Auto.Form');

Vps.Auto.Form.Window = Ext.extend(Ext.Window, {
    initComponent : function()
    {
        if (!this.editTitle) {
            this.editTitle = 'Edit';
            if (this.title) this.editTitle = this.editTitle + ' ' + this.title;
        }
        if (!this.addTitle) {
            this.addTitle = 'Add';
            if (this.title) this.addTitle = this.addTitle + ' ' + this.title;
        }
        if (!this.autoForm) {
            if (!this.formConfig) this.formConfig = {};

            Ext.applyIf(this.formConfig, {
                baseCls: 'x-plain',
                controllerUrl: this.controllerUrl,
                autoload: false,
                checkDirty: false
            });
            this.autoForm = new Vps.Auto.FormPanel(this.formConfig);
        } else if (typeof this.autoForm == 'string') {
            try {
                var d = eval(this.autoForm);
            } catch (e) {
                throw new Error("Invalid autoForm \'"+this.autoForm+"': "+e);
            }
            this.autoForm = new d({ baseCls: 'x-plain' });
        }

        var onRender = function() {
            this.getForm().waitMsgTarget = this.el;
            this.getForm().loadAfterSave = false; //dialog wird geschlossen nach speichern, ist also nicht nötig
        };
        if (!this.autoForm.rendered) {
            this.on('renderform', onRender, this);
        } else {
            onRender();
        }

        Ext.applyIf(this, {
            width: 400,
            height: 300,
            layout: 'fit',
            bodyStyle:'padding:5px;',
            plain: true,
            modal: true,
            baseCls: 'x-plain',
            buttons: [this.getAction('cancel'), this.getAction('save')]
        });
        this.closeAction = 'hide';

        this.items = [this.autoForm];

        this.relayEvents(this.autoForm, ['renderform', 'datachange', 'loadform', 'addaction']);

        Vps.Auto.Form.Window.superclass.initComponent.call(this);
    },

    getAction : function(type)
    {
        if (!this.actions) { this.actions = {}; }
        if (this.actions[type]) return this.actions[type];

        if (type == 'save') {
            this.actions[type] = new Ext.Action({
                text    : 'Save',
                handler : function() {
                    this.getAutoForm().submit({
                        success: function() {
                            this.hide();
                        },
                        scope: this
                    });
                },
                scope   : this
            });
        } else if (type == 'cancel') {
            this.actions[type] = new Ext.Action({
                text    : 'Cancel',
                handler : function() {
                    this.hide();
                },
                scope   : this
            });
        } else {
            throw 'unknown action-type: ' + type;
        }
        return this.actions[type];
    },

    showAdd : function()
    {
        this.setTitle(this.addTitle);
        this.show();
        this.getAutoForm().onAdd();
    },

    showEdit : function(id, options)
    {
        this.setTitle(this.editTitle);
        this.show();
        if (id) {
            this.getAutoForm().load(id, options);
        }
    },

    getAutoForm : function()
    {
        return this.autoForm;
    },

    getForm : function()
    {
        return this.getAutoForm().getForm();
    },

    findField: function(f)
    {
        return this.getAutoForm().findField(f);
    },

    getBaseParams: function()
    {
        return this.getAutoForm().getBaseParams();
    }
});
