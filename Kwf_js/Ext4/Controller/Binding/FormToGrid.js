Ext4.define('Kwf.Ext4.Controller.Binding.FormToGrid', {
    mixins: {
        observable: 'Ext.util.Observable'
    },
    focusOnAddSelector: 'field',
    constructor: function(config) {
        this.mixins.observable.constructor.call(this, config);
        this.init();
    },

    init: function()
    {
        var grid = this.source;
        var form = this.form;
        form.disable();

        if (!this.formSaveButton) this.formSaveButton = form.down('button#save');
        if (!this.formDeleteButton) this.formDeleteButton = form.down('button#delete');
        if (!this.gridAddButton) this.gridAddButton = grid.down('button#add');

        grid.on('selectionchange', function(model, rows) {
            if (rows[0]) {
                var row = rows[0];
                form.getForm().loadRecord(row);
                form.enable();
            } else {
                form.disable();
            }
        }, this);
        grid.on('beforedeselect', function(sm, record) {
            if (!form.getForm().isValid()) {
                return false;
            }
        }, this);

        if (this.updateOnChange) {
            Ext4.each(form.query('field'), function(i) {
                i.on('change', function() {
                    this.form.updateRecord();
                }, this);
            }, this);
        }

        if (this.formSaveButton) {
            this.formSaveButton.on('click', function() {
                var row = form.getRecord();
                form.updateRecord(row);
                grid.getStore().sync();
            }, this);
        }
        if (this.gridAddButton) {
            this.gridAddButton.on('click', function() {
                if (!form.getForm().isValid()) {
                    return false;
                }
                var s = grid.getStore();
                var row = s.model.create();
                s.add(row);
                grid.getSelectionModel().select(row);

                form.down(this.focusOnAddSelector).focus();
                this.fireEvent('add');
            }, this);
        }
        if (this.formDeleteButton) {
            this.formDeleteButton.on('click', function() {
                Ext4.Msg.show({
                    title: trlKwf('Delete'),
                    msg: trlKwf('Do you really wish to remove this entry?'),
                    buttons: Ext4.Msg.YESNO,
                    scope: this,
                    fn: function(button) {
                        if (button == 'yes') {
                            grid.getStore().remove(form.getRecord());
                            grid.getStore().sync();
                        }
                    }
                });

            }, this);
        }
    }
});