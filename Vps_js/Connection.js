Vps.Connection = Ext.extend(Ext.data.Connection, {
    request: function(options)
    {
        Vps.Connection.runningRequests++;
        if (options.url.match(/[\/a-zA-Z0-9]*\/json[a-zA-Z0-9\-]+(\/|\?|)/)) {

            if (options.mask) {
                if (Vps.Connection.masks == 0) {
                    if (Ext.get('loading')) {
                        Ext.getBody().mask();
                    } else {
                        Ext.getBody().mask(trlVps('Loading...'));
                    }
                }
                Vps.Connection.masks++;
            }
            options.vpsCallback = {
                success: options.success,
                failure: options.failure,
                callback: options.callback,
                scope: options.scope
            };
            options.success = this.vpsSuccess;
            options.failure = this.vpsFailure;
            options.callback = this.vpsCallback;
            options.scope = this;
        }
        if (!options.params) options.params = {};
        options.params.application_version = Vps.application.version;
        if (!options.url.match(':\/\/')) {
            //absolute url incl. http:// erstellen
            //wird benötigt wenn fkt über mozrepl aufgerufen wird
            var u = location.protocol + '/'+'/' + location.host;
            if (options.url.substr(0, 1) == '/') {
                options.url = u + options.url;
            } else {
                options.url = u + '/' + options.url;
            }
        }
        Vps.Connection.superclass.request.call(this, options);
    },
    repeatRequest: function(options) {
        delete options.vpsIsSuccess;
        Vps.Connection.superclass.request.call(this, options);
    },
    vpsSuccess: function(response, options)
    {
        options.vpsIsSuccess = false;
        options.vpsLogin = false;

        var errorMsg = false;

        var encParams;
        if (typeof options.params == "string") {
            encParams = options.params;
        } else {
            encParams = Ext.urlEncode(options.params);
        }
        try {
            if (!response.responseText) {
                errorMsg = 'response is empty';
            } else {
                var r = Ext.decode(response.responseText);
            }
        } catch(e) {
            errorMsg = e.toString()+': <br />'+response.responseText;
            var errorMsgTitle = 'Javascript Parse Exception';
        }
        if (Vps.Debug.querylog && r && r.requestNum) {
            var rm = location.protocol + '/'+'/' + location.host;
            var url = options.url;
            if (url.substr(0, rm.length) == rm) {
                url = url.substr(rm.length);
            }
            var data = [[new Date(), url, encParams, r.requestNum]];
            Vps.Debug.requestsStore.loadData(data, true);
        }
        if (!errorMsg && r.exception) {
            var p;
            if (typeof options.params == "string") {
                p = options.params;
            } else {
                p = Ext.urlEncode(options.params);
            }
            errorMsg = r.exception;
            var errorMsgTitle = 'PHP Exception';
        }
        if (errorMsg) {
            errorMsg = '<a href="'+options.url+'?'+encParams+'">request-url</a><br />' + errorMsg;
            var sendMail = !r || !r.exception;
			Vps.handleError({
			    message: errorMsg,
				title: errorMsgTitle,
				mail: sendMail,
				retry: function() {
					this.connection.repeatRequest(this.options);
				},
				abort: function() {
                    Ext.callback(this.options.vpsCallback.failure, this.options.vpsCallback.scope, [this.response, this.options]);
				},
				scope: { connection: this, options: options, response: response }
			});
            return;
        }

        if (!r.success) {
            if (r.wrongversion) {
                Ext.Msg.alert(trlVps('Error - wrong version'),
                trlVps('Because of an application update the application has to be reloaded.'),
                function(){
                    location.reload();
                });
                Ext.callback(options.vpsCallback.failure, options.vpsCallback.scope, [response, options]);
                return;
            }
            if (r.login) {
                options.vpsLogin = true;
                var dlg = new Vps.User.Login.Dialog({
                    message: r.message,
                    success: function() {
                        //redo action...
                        this.repeatRequest(options);
                    },
                    scope: this
                });
                Ext.getBody().unmask();
                dlg.showLogin();
                return;
            }
            if (r.error) {
                Ext.Msg.alert(trlVps('Error'), r.error);
            } else {
                Ext.Msg.alert(trlVps('Error'), trlVps("A Server failure occured."));
            }
            Ext.callback(options.vpsCallback.failure, options.vpsCallback.scope, [response, options]);
            return;
        }
        options.vpsIsSuccess = true;

        Vps.callWithErrorHandler(function() {
            Ext.callback(options.vpsCallback.success, options.vpsCallback.scope, [response, options, r]);
        });
    },

    vpsFailure: function(response, options)
    {
        options.vpsIsSuccess = false;
        var debugString = '';
        for (var dbg in options.params) {
            debugString += '<br />params.' + dbg + ' = ' + options.params[dbg];
        }

		errorMsgTitle = trlVps('Error');
        errorMsg = trlVps("A connection problem occured.")+"<br /><br /><b>"+trlVps("Debug info")+":</b><br />"
            + "url: " + options.url + debugString;

		Vps.handleError({
            message: errorMsg,
            title: errorMsgTitle,
            mail: false,
            retry: function() {
                this.repeatRequest(options);
            },
            abort: function() {
                Ext.callback(options.vpsCallback.failure, options.vpsCallback.scope, [response, options]);
            },
            scope: this
        });
        Ext.callback(options.vpsCallback.failure, options.vpsCallback.scope, [response, options]);
        return;
    },

    vpsCallback: function(options, success, response)
    {
        //wenn login-fenster angezeigt wird keinen callback aufrufen - weil der request
        //wird ja erneut gesendet und da dann der callback aufgerufen.
        if (options.vpsLogin) return;

        if (options.mask) {
            Vps.Connection.masks--;
            if (Vps.Connection.masks == 0) {
                Ext.getBody().unmask();
                if (Ext.get('loading')) {
                    Ext.get('loading').fadeOut({remove: true});
                }
            }
        }

        if(success && !options.vpsIsSuccess) {
            success = false;
        }
        Ext.callback(options.vpsCallback.callback, options.vpsCallback.scope, [options, success, response]);
        Vps.Connection.runningRequests--;
    }
});
Vps.Connection.masks = 0; //static var that hols number of masked requests
Vps.Connection.runningRequests = 0;

Ext.Ajax = new Vps.Connection({
    /**
     * The timeout in milliseconds to be used for requests. (defaults
     * to 30000)
     * @type Number
     * @property  timeout
     */
    autoAbort : false,

    /**
     * Serialize the passed form into a url encoded string
     * @return {String}
     */
    serializeForm : function(form){
        return Ext.lib.Ajax.serializeForm(form);
    }
});

