Vps.Form.StaticField = Ext.extend(Ext.BoxComponent, {
    autoEl: {tag: 'div', cls:'vps-form-static-field'},
    isFormField : true,
    initComponent: function() {
        Vps.Form.StaticField.superclass.initComponent.call(this);
    },
    afterRender: function() {
        Vps.Form.StaticField.superclass.afterRender.call(this);
        this.el.update(this.text);
    },
    getName: function() {
        return null;
    },
    getValue: function() {
        return null;
    },
    clearInvalid: function() {},
    reset: function() {},
    setValue: function() {},
    resetDirty: function() {},
    clearValue: function() {},
    validate: function() { return true; }
});
Ext.reg('staticfield', Vps.Form.StaticField);
