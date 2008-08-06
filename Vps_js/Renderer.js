Ext.util.Format['boolean'] = function(v, p, record) {
    p.css += ' x-grid3-check-col-td';
    return '<div class="x-grid3-check-col'+(v?'-on':'')+'">&#160;</div>';
};
Ext.util.Format.booleanTickCross = function(v, p, record) {
    p.css += ' x-grid3-check-col-td';
    return '<div class="x-grid3-check-col vps-check-tick-cross-col'+(v?'-on':'')+'">&#160;</div>';
};

Ext.util.Format.password = function(value)
{
    return value||true ? '******' : '';
};

Ext.util.Format.euroMoney = function(v, p)
{
    return Ext.util.Format.decimal(v,p) + " €";
};

Ext.util.Format.decimal = function(v, p)
{
    if (p) p.css = 'vps-renderer-euro-money';
    if (v === null || v == undefined) return "";
    v = v.toString().replace(",", ".");
    v = (Math.round((v-0)*100))/100;
    v = (v == Math.floor(v)) ? v + ".00" : ((v*10 == Math.floor(v*10)) ? v + "0" : v);
    v = v.toString().replace(".", ",");
    return v;
};

Ext.util.Format.percent = function(v)
{
    return v + "%";
};

Ext.util.Format.showField = function(fieldName) {
    return function(value, p, record) {
        return record.data[fieldName];
    };
};

//eigene implementation durch ext eigene ersetzt
//wieder alte version
Ext.util.Format.nl2Br = function(v) {
    return v.replace(/\n/g, "<br />");
};

Ext.util.Format.AutoNl2Br = function(v) {
	//span wird in v gesetzt, da er sonst wieder überschrieben wird
    return "<span class=\'vps-renderer-linebreak\'>"+Ext.util.Format.nl2Br(v)+"</span>";
};

Ext.util.Format.component = function(v, f) {
    f.css += 'content';
    f.attr += 'style="overflow: visible; white-space: normal;"';
    return v;
};

//date-funktion überschreiben, damit Y-m-d als eingabeformat verwendet werden kann
Ext.util.Format.date = function(v, format) {
	if(!v){
        return '';
    }
    if(!(v instanceof Date)){
        var tmpv = new Date(Date.parseDate(v, 'Y-m-d'));
        if (isNaN(tmpv.getYear())) {
            tmpv = new Date(Date.parseDate(v, 'Y-m-d H:i:s'));
        }
        v = tmpv;
    }
    if(isNaN(v.getYear())){
        return '';
    }
    return v.dateFormat(format || trlVps('Y-m-d'));
};


Ext.util.Format.localizedDate = Ext.util.Format.dateRenderer(trlVps('Y-m-d'));
Ext.util.Format.germanDate = Ext.util.Format.dateRenderer('d.m.Y');
Ext.util.Format.germanDay = function(value, p) {
    p.css += 'vps-renderer-bright';
    return Ext.util.Format.date(value, 'd.m.');
}
Ext.util.Format.time = Ext.util.Format.dateRenderer('H:i');

Ext.util.Format.mouseoverPic = function(v, p, record){
    if (!v) return '';
    p.css += 'vps-cell-button';
    p.attr += 'style="background-image:url('+escape(v)+');"';
    p.attr += ' ext:qtip="&lt;img src=\''+record.data.pic_large+'\' /&gt;"';
    return '';
};

Ext.util.Format.cellButton = function(value, p, record, rowIndex, colIndex, store, column) {
    p.css += 'vps-cell-button';
    if (column && column.buttonIcon) {
        p.attr += 'style="background-image:url('+column.buttonIcon+');" ';
    }
    if (column && column.tooltip) {
        p.attr += ' ext:qtip="'+column.tooltip+'"';
    }
    return '';
};

Ext.util.Format.genderIcon = function(value, p, record, rowIndex, colIndex, store, column) {
    p.css += 'vps-cell-button';
    if (value == 'male') {
        p.attr += 'style="background-image:url(/assets/silkicons/male.png); cursor: auto;"" ';
    } else if (value == 'female') {
        p.attr += 'style="background-image:url(/assets/silkicons/female.png); cursor: auto;"" ';
    } else {
        p.attr += 'style="background-image:none; cursor: auto;"" ';
    }
    if (column && column.tooltip) {
        p.attr += ' ext:qtip="'+column.tooltip+'"';
    }
    return '';
};

Ext.util.Format.fileSize = function(size) {
    var unit;
    if (!parseInt(size) && size !== 0) return '';
    if(size < 1024) {
        unit = 'bytes';
    } else if(size < 1048576) {
        unit = 'KB';
        size = (Math.round(((size*10) / 1024))/10);
    } else {
        unit = 'MB';
        size = (Math.round(((size*10) / 1048576))/10);
    }
    return size.toString().replace(".", ",") + ' ' + unit;
};

