Vps.onContentReady(function()
{
    var fadeComponents = Ext.query('div.vpsFadeElements');
    Ext.each(fadeComponents, function(c) {
        var selector = Ext.query('.fadeSelector', c)[0].value;
        var config = Ext.query('.fadeConfig', c)[0]; // optional
        if (config) {
            config = Ext.decode(config.value);
        } else {
            config = { };
        }

        config.selector = selector;
        config.selectorRoot = c;

        var fade = new Vps.Fade.Elements(config);
        fade.start();
    });
});

Ext.namespace("Vps.Fade");

Vps.Fade.Elements = function(cfg) {
    this.selector = cfg.selector;

    this.elementAccessLinks = false; // optional
    this.selectorRoot = document;
    this.fadeDuration = 1.5;
    this.easingFadeOut = 'easeIn';
    this.easingFadeIn = 'easeIn';
    this.fadeEvery = 7;
    this.startRandom = true;

    if (cfg.elementAccessLinks) this.elementAccessLinks = cfg.elementAccessLinks;
    if (cfg.selectorRoot) this.selectorRoot = cfg.selectorRoot;
    if (cfg.fadeDuration) this.fadeDuration = cfg.fadeDuration;
    if (cfg.easingFadeOut) this.easingFadeOut = cfg.easingFadeOut;
    if (cfg.easingFadeIn) this.easingFadeIn = cfg.easingFadeIn;
    if (cfg.fadeEvery) this.fadeEvery = cfg.fadeEvery;
    if (cfg.startRandom) this.startRandom = cfg.startRandom;


    this.fadeElements = Ext.query(this.selector, this.selectorRoot);

    if (this.startRandom) {
        this.active = Math.floor(Math.random() * this.fadeElements.length);
        if (this.active >= this.fadeElements.length) {
            this.active = this.fadeElements.length - 1;
        }

        this.next = this.active + 1;
        if (this.next >= this.fadeElements.length) {
            this.next = 0;
        }
    }

    var i = 0;
    Ext.each(this.fadeElements, function(e) {
        var ee = Ext.get(e);

        ee.addClass('vpsFadeElement');
        if (i != this.active) {
            ee.setStyle('display', 'none');
        } else {
            ee.setStyle('display', 'block');
        }
        i += 1;
    }, this);


    // create the element access link if needed
    if (this.elementAccessLinks && i >= 1) {
        this._createElementAccessLinks(this.active);
    }
};

Vps.Fade.Elements.prototype = {

    active: 0,
    next: 1,
    _firstFaded: false,
    _timeoutId: null,
    _elementAccessLinkEls: [ ],
    _playPause: 'play',
    _playPauseButton: null,

    start: function() {
        if (this.fadeElements.length <= 1) return;
        this._timeoutId = this.doFade.defer(this._getDeferTime(), this);
    },

    doFade: function() {
        if (this.fadeElements.length <= 1) return;

        var activeEl = Ext.get(this.fadeElements[this.active]);
        activeEl.fadeOut({ endOpacity: .0, easing: this.easingFadeOut, duration: this.fadeDuration, useDisplay: true });

        var nextEl = Ext.get(this.fadeElements[this.next]);
        nextEl.fadeIn({ endOpacity: 1.0, easing: this.easingFadeIn, duration: this.fadeDuration, useDisplay: true });

        if (this.elementAccessLinks) {
            if (this._elementAccessLinkEls[this.active].hasClass('elementAccessLinkActive')) {
                this._elementAccessLinkEls[this.active].removeClass('elementAccessLinkActive');
            }
            this._elementAccessLinkEls[this.next].addClass('elementAccessLinkActive');
        }

        this.active = this.next;
        this.next += 1;
        if (typeof this.fadeElements[this.next] == 'undefined') {
            this.next = 0;
        }

        this._timeoutId = this.doFade.defer(this._getDeferTime(), this);
    },

    pause: function() {
        if (this._timeoutId) window.clearTimeout(this._timeoutId);
        if (this._playPauseButton) {
            this._playPauseButton.removeClass('elementAccessPause');
            this._playPauseButton.addClass('elementAccessPlay');
        }
        this._playPause = 'pause';
    },

    play: function() {
        this.doFade();
        if (this._playPauseButton) {
            this._playPauseButton.removeClass('elementAccessPlay');
            this._playPauseButton.addClass('elementAccessPause');
        }
        this._playPause = 'play';
    },

    _getDeferTime: function() {
        if (!this._firstFaded) {
            this._firstFaded = true;
            return Math.ceil(this.fadeEvery * 1000) - Math.ceil(this.fadeDuration * 1000);
        } else {
            return Math.ceil(this.fadeEvery * 1000);
        }
    },

    _createElementAccessLinks: function(activeLinkIndex) {
        var ul = Ext.get(this.selectorRoot).createChild({ tag: 'ul', cls: 'elementAccessLinks' });

        var j = 0;
        Ext.each(this.fadeElements, function(e) {
            var a = ul.createChild({ tag: 'li' })
                .createChild({
                    tag: 'a',
                    cls: 'elementAccessLink'+(activeLinkIndex==j ? ' elementAccessLinkActive' : ''),
                    html: '',
                    href: '#'
                });
            a.on('click', function(ev, el, opt) {
                ev.stopEvent();

                if (this._timeoutId) {
                    window.clearTimeout(this._timeoutId);
                }
                this.next = opt.activateIdx;
                this.doFade();
                this.pause();

            }, this, { activateIdx: j });
            this._elementAccessLinkEls.push(a);
            j += 1;
        }, this);

        // play / pause button if there are at least 2 images
        if (this.fadeElements.length >= 2) {
            this._playPauseButton = ul.createChild({ tag: 'li' })
                .createChild({
                    tag: 'a',
                    cls: 'elementAccessPlayPauseButton elementAccessPause',
                    html: '&nbsp;',
                    href: '#'
                });
            this._playPauseButton.on('click', function(ev, el, opt) {
                ev.stopEvent();

                if (this._playPause == 'play') {
                    this.pause();
                } else if (this._playPause == 'pause') {
                    this.play();
                }
            }, this);
        }
    }
};
