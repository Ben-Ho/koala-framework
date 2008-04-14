Vps.Form.ShowField = Ext.extend(Ext.form.Field,
{
    defaultAutoCreate : {tag: 'div', cls: 'vps-form-show-field'},
    /**
     * {value} wenn kein objekt �bergeben, sonst index aus objekt
     */
    tpl: '{value}',

    initValue : function(){
        if(this.value !== undefined){
            this.setValue(this.value);
        }
    },
    afterRender : function(){
        Vps.Form.ShowField.superclass.afterRender.call(this);
        if (typeof this.tpl == 'string') this.tpl = new Ext.XTemplate(this.tpl);
        this.tpl.compile();
    },
    getName: function(){
        return this.name;
    },
    setRawValue : function(v){
        return this.el.update(v);
    },
    getRawValue : function(){
        return this.el.dom.innerHTML;
    },
    getValue : function()
    {
        return null;
    },

    setValue : function(value)
    {
        this.value = value;
        if(this.rendered){
            if (!value) {
                this.setRawValue(value);
            } else {
                if (typeof value != 'object') value = { value : value };
                this.tpl.overwrite(this.el, value);
            }
        }
    }
});
Ext.reg('showfield', Vps.Form.ShowField);
