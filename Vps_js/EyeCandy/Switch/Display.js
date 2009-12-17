// um flackern zu unterbinden
document.write('<style type="text/css"> div.vpsSwitchDisplay div.switchContent { display: none; } </style>');

Vps.onContentReady(function() {
    var els = Ext.query('div.vpsSwitchDisplay');
    els.forEach(function(el) {
        el = Ext.get(el);
        el.switchDisplayObject = new Vps.Switch.Display(el);
    });
});

Vps.Switch.Display = function(el) {
    this.addEvents({
        'beforeOpen': true,
        'beforeClose': true,
        'opened': true,
        'closed': true
    });
    this.el = el;
    this.switchLink = Ext.get(Ext.query('a.switchLink', this.el.dom)[0]);
    this.switchContent = Ext.get(Ext.query('div.switchContent', this.el.dom)[0]);

    // durch unterbinden von flackern (ganz oben) muss das auf block
    // gesetzt werden, damit die hoehe gemessen werden kann
    this.switchContent.setStyle('display', 'block');
    this.switchContent.scaleHeight = this.switchContent.getHeight();
    this.switchContent.setHeight(0);
    // und schnell wieder auf 'none' bevors wer merkt :)
    this.switchContent.setStyle('display', 'none');

    if (this.switchLink && this.switchContent) {
        Ext.EventManager.addListener(this.switchLink, 'click', function(e) {
            if (this.switchLink.hasClass('switchLinkOpened')) {
                this.doClose();
            } else {
                this.doOpen();
            }
        }, this, { stopEvent: true });
    }
};

Ext.extend(Vps.Switch.Display, Ext.util.Observable, {
    doClose: function() {
        this.fireEvent('beforeClose', this);
        this.switchContent.scaleHeight = this.switchContent.getHeight();
        this.switchContent.scale(undefined, 0,
            { easing: 'easeOut', duration: .5, afterStyle: "display:none;",
                callback: function() {
                    this.fireEvent('closed', this);
                },
                scope: this
            }
        );
        this.switchLink.removeClass('switchLinkOpened');
    },

    doOpen: function() {
        this.fireEvent('beforeOpen', this);
        this.switchContent.setStyle('display', 'block');
        this.switchContent.scale(undefined, this.switchContent.scaleHeight,
            { easing: 'easeOut', duration: .5, afterStyle: "height:auto;",
                callback: function() {
                    this.fireEvent('opened', this);
                    if (Ext.isIE6) {
                        this.switchContent.setWidth(this.switchContent.getWidth());
                    }
                },
                scope: this
            }
        );
        this.switchLink.addClass('switchLinkOpened');
    }
});