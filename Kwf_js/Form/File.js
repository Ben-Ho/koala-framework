Kwf.Form.File = Ext.extend(Ext.form.Field, {
    allowOnlyImages: false,
    fileSizeLimit: 0,
    showPreview: true,
    previewUrl: '/kwf/media/upload/preview?',
    previewSize: 40,
    showDeleteButton: true,
    infoPosition: 'south',
    imageData: null,
    defaultAutoCreate : {
        tag: 'div',
        style: 'width: 320px; height: 53px'
    },
    fileIcons: {
        'application/pdf': 'page_white_acrobat',
        'application/x-zip': 'page_white_compressed',
        'application/msexcel': 'page_white_excel',
        'application/msword': 'page_white_word',
        'application/mspowerpoint': 'page_white_powerpoint',
        'default': 'page_white'
    },
    previewTpl: ['<a href="{href}" target="_blank" class="previewImage" ',
                 'style="width: {previewSize}px; height: {previewSize}px; display: block; background-repeat: no-repeat; background-position: center; background-image: url({preview});"></a>'],
    // also usable in infoTpl: {href}
    infoTpl: ['<div class="filedescription"><div class="filename">{filename}.{extension}</div>',
              '<div class="filesize">{fileSize:fileSize}',
              '<tpl if="image">, {imageWidth}x{imageHeight}px</tpl></div></div>'],
    emptyTpl: ['<div class="empty" style="height: {previewSize}px; width: {previewSize}px; text-align: center;line-height:{previewSize}px">('+trlKwf('empty')+')</div>'],

    initComponent: function() {
        this.addEvents(['uploaded']);

        if (this.showPreview) {
            if (!(this.previewTpl instanceof Ext.XTemplate)) {
                this.previewTpl = new Ext.XTemplate(this.previewTpl);
            }
            this.previewTpl.compile();

            if (!(this.emptyTpl instanceof Ext.XTemplate)) {
                this.emptyTpl = new Ext.XTemplate(this.emptyTpl);
            }
            this.emptyTpl.compile();
        }

        if (!(this.infoTpl instanceof Ext.XTemplate)) {
            this.infoTpl = new Ext.XTemplate(this.infoTpl);
        }
        this.infoTpl.compile();

        Kwf.Form.File.superclass.initComponent.call(this);

    },
    afterRender: function() {
        if (Kwf.Utils.Upload.supportsHtml5Upload()) {
            this.el.on('dragenter', function(e) {
                e.browserEvent.stopPropagation();
                e.browserEvent.preventDefault();
            }, this);
            this.el.on('dragover', function(e) {
                e.browserEvent.stopPropagation();
                e.browserEvent.preventDefault();
            }, this);
            this.el.on('drop', function(e) {
                e.browserEvent.stopPropagation();
                e.browserEvent.preventDefault();

                if (e.browserEvent.dataTransfer) {
                    var dt = e.browserEvent.dataTransfer;
                    var files = dt.files;
                    if (files.length) {
                        this.html5UploadFile(files[0]);
                    }
                }
            }, this);
        }

        if (this.showPreview) {
            this.previewImage = this.el.createChild({
                cls: 'box',
                style: 'margin-right: 10px; padding: 5px; float: left; border: 1px solid #b5b8c8;'+
                    'background-color: white;'
            });

            this.emptyTpl.overwrite(this.previewImage, { //bild wird in der richtigen größe angezeigt
                previewSize: this.previewSize
            });
        }

        if (this.infoPosition == 'west') this.createInfoContainer();

        this.uploadButtonContainer = this.el.createChild({
                                         style: 'float:left; margin-right: 5px; position: relative; '
                                     });

        this.createUploadButton();
        if (this.showDeleteButton) {
            this.deleteButton = new Ext.Button({
                text: trlKwf('Delete File'),
                cls: 'x-btn-text-icon',
                icon: '/assets/silkicons/delete.png',
                renderTo: this.el.createChild({}),
                scope: this,
                handler: function() {
                    this.setValue('');
                }
            });
        }
        Kwf.Form.File.superclass.afterRender.call(this);

        if (this.infoPosition == 'south') this.createInfoContainer();

        if (!Kwf.Utils.Upload.supportsHtml5Upload()) {
            this.swfu = new Kwf.Utils.SwfUpload({
                fileSizeLimit: this.fileSizeLimit,
                allowOnlyImages: this.allowOnlyImages,
                buttonPlaceholderId: this.uploadButtonContainer.id,
                postParams: {
                    maxResolution: this.maxResolution
                }
            });
            this.swfu.on('fileQueued', function(file) {
                this.progress = Ext.MessageBox.show({
                    title : trlKwf('Upload'),
                    msg : trlKwf('Uploading file'),
                    buttons: false,
                    progress:true,
                    closable:false,
                    minWidth: 250,
                    buttons: Ext.MessageBox.CANCEL,
                    scope: this,
                    fn: function(button) {
                        this.swfu.cancelUpload(file.id);
                    }
                });
                this.swfu.startUpload(file.id);
            }, this);
            this.swfu.on('uploadProgress', function(file, done, total) {
                this.progress.updateProgress(done/total);
            }, this);
            this.swfu.on('uploadSuccess', function(file, response) {
                this.progress.hide();
                this.setValue(response.value);
                this.fireEvent('uploaded', this, response.value);
            }, this);
            this.swfu.on('uploadError', function(file, errorCode, errorMessage) {
                this.progress.hide();
            }, this);
            this.swfu.on('flashLoadError', function() {
                this.createUploadButton();
            }, this);
        }
    },

    alignHelpAndComment: function() {
        if (this.helpEl) {
            this.helpEl.anchorTo(this.deleteButton.el, 'r', [10, -8]);
        }
    },

    onDestroy: function() {
        if (this.swfu) this.swfu.destroy();
    },

    createInfoContainer: function() {
        this.infoContainer = this.el.createChild({
            style: 'margin-top: 3px;'+((this.infoPosition == 'west') ? ' float: left; margin-right: 3px;' : '')
        });
    },

    createUploadButton : function () {
        if (this.swfu) this.swfu.destroy();
        this.swfu = null;
        while (this.uploadButtonContainer.last()) {
            this.uploadButtonContainer.last().remove();
        }
        this.uploadButton = new Ext.Button({
            text: trlKwf('Upload File'),
            cls: 'x-btn-text-icon',
            icon: '/assets/silkicons/add.png',
            renderTo: this.uploadButtonContainer,
            scope: this,
            handler: function() {
                var win = new Kwf.Form.FileUploadWindow({
                    maxResolution: this.maxResolution
                });
                win.on('uploaded', function(win, result) {
                    this.setValue(result.value);
                    this.fireEvent('uploaded', this, result.value);
                }, this);
                win.show();
            }
        });

        if (!Kwf.Utils.Upload.supportsHtml5Upload()) return;

        var w = this.uploadButton.el.getWidth();
        var h = this.uploadButton.el.getHeight();
        if (!w) w = 131; //might be 0 if element is hidden
        if (!h) h = 21;
        var fileInputContainer = this.uploadButtonContainer.createChild({
            style: 'width: '+w+'px; height: '+h+'px; top: 0; position: absolute; overflow: hidden;'
        });

        var fileInput = fileInputContainer.createChild({
            tag: 'input',
            type: 'file',
            style: 'opacity: 0; cursor: pointer; '
        });
        fileInput.on('change', function(ev, dom) {
            if (dom.files) {
                if (dom.files.length) {
                    var file = dom.files[0];
                    dom.value = ''; //leeren, damit gleiche datei nochmal gewählt werden kann
                    this.html5UploadFile(file);
                }
            }
        }, this);
    },

    html5UploadFile: function(file)
    {
        this.progress = Ext.MessageBox.show({
            title : trlKwf('Upload'),
            msg : trlKwf('Uploading file'),
            buttons: false,
            progress:true,
            closable:false,
            minWidth: 250,
            buttons: Ext.MessageBox.CANCEL,
            scope: this,
            fn: function(button) {
                xhr.abort();
            }
        });

        var xhr = Kwf.Utils.Upload.uploadFile({
            maxResolution: this.maxResolution,
            file: file,
            success: function(r) {
                this.progress.hide();
                this.setValue(r.value);
                this.fireEvent('uploaded', this, r.value);
            },
            failure: function() {
                this.progress.hide();
            },
            progress: function(e) {
                this.progress.updateProgress(e.loaded / e.total);
            },
            scope: this
        });

    },

    // überschrieben weil ext implementation auf this.el.dom.value zugreift was wir nicht haben
    initValue : function() {
        if(this.value !== undefined){
            this.setValue(this.value);
        }
    },

    setValue: function(value)
    {
        var v = '';
        if (value.uploadId) {
            v = value.uploadId;
        }
        if (v != this.value) {
            this.imageData = value;
            this.fireEvent('change', this, value, this.value);
            var icon = false;
            var href = '/kwf/media/upload/download?uploadId='+value.uploadId+'&hashKey='+value.hashKey;
            if (value.mimeType) {
                if (this.showPreview) {
                    if (value.mimeType.match(/(^image\/)/)) {
                        icon = this._generatePreviewUrl(this.previewUrl);
                    } else {
                        icon = this.fileIcons[value.mimeType] || this.fileIcons['default'];
                        icon = '/assets/silkicons/' + icon + '.png';
                    }
                    this.previewTpl.overwrite(this.previewImage, {
                        preview: icon,
                        href: href,
                        previewSize: this.previewSize
                    });
                }

                var infoVars = Kwf.clone(value);
                infoVars.href = href;
                this.infoTpl.overwrite(this.infoContainer, infoVars);
            } else {
                if (this.showPreview) {
                    this.emptyTpl.overwrite(this.previewImage, {
                        previewSize: this.previewSize
                    });
                }
                this.infoContainer.update('');
            }
        }
        Kwf.Form.File.superclass.setValue.call(this, value.uploadId);
    },

    _generatePreviewUrl: function (previewUrl) {
        return previewUrl+'uploadId='+this.imageData.uploadId
        +'&hashKey='+this.imageData.hashKey;
    },

    setPreviewUrl: function (previewUrl) {
        this.previewUrl = previewUrl;
        if (this.getEl().child('.previewImage') && this.getValue()) {
            this.getEl().child('.previewImage')
                .setStyle('background-image', 'url('+this._generatePreviewUrl(previewUrl)+')');
        }
    },

    validateValue : function(value){
        if(!value){ // if it's blank
             if(this.allowBlank){
                 this.clearInvalid();
                 return true;
             }else{
                 this.markInvalid(trlKwf('This field is required'));
                 return false;
             }
        }
        return true;
    }

});



Ext.reg('kwf.file', Kwf.Form.File);




