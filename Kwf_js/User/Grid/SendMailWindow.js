
Kwf.User.Grid.SendMailWindow = Ext2.extend(Ext2.Window,
{
    initComponent: function() {
        this.formPanel = new Ext2.FormPanel({
            labelWidth: 90,
            url: this.controllerUrl+'/json-resend-mail',
            baseParams: this.baseParams,
            layout: 'fit',
            bodyStyle: 'background-color: transparent;',
            border: false,
            buttonAlign: 'right',
            items: {
                xtype: 'fieldset',
                title: trlKwf('Please choose'),
                defaultType: 'radio',
                autoHeight: true,
                items: [{
                    xtype: 'radio',
                    checked: true,
                    fieldLabel: trlKwf('E-Mail type'),
                    boxLabel: trlKwf('Activation'),
                    name: 'mailtype',
                    inputValue: 'activation'
                },{
                    xtype: 'radio',
                    fieldLabel: '',
                    labelSeparator: '',
                    boxLabel: trlKwf('Lost password'),
                    name: 'mailtype',
                    inputValue: 'lost_password'
                }]
            },
            buttons: [
                {
                    text: trlKwf('Send'),
                    handler: function() {
                        this.formPanel.buttons[0].disable();
                        this.formPanel.getForm().submit({
                            success: function() {
                                this.hide();
                            },
                            scope: this
                        });
                    },
                    scope: this
                }, {
                    text: trlKwf('Cancel'),
                    handler: function() {
                        this.hide();
                    },
                    scope: this
                }
            ]
        });
        var infoPanel = new Ext2.Panel({
            bodyCssClass: 'userMailResendInfo',
            border: false,
            html: trlKwf('Please select the E-Mail type you wish to send to the user.')
        });
        this.title = trlKwf('Send a mail to a user');
        this.items = [ infoPanel, this.formPanel ];
        this.width = 450;
        this.height = 300;
        this.bodyStyle = 'padding: 15px;';
        Kwf.User.Grid.SendMailWindow.superclass.initComponent.call(this);
    }
});
