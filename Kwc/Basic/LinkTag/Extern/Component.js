Kwf.onContentReady(function() {
    Kwf.Basic.LinkTag.Extern.processLinks();
});
Kwf.Basic.LinkTag.Extern.processLinks = function(root) {
    // links holen und durchgehen
    var lnks = Ext.query('a', root || document);
    Ext.each(lnks, function(lnk) {
        // rels von link durchgehen
        lnk = Ext.get(lnk);
        if (lnk.hasClass('webLinkPopup')) return; // nur einmal machen
        var rels = lnk.dom.rel.split(' ');
        Ext.each(rels, function(rel) {
            if (rel.match(/^popup/)) {
                var relProperties = rel.split('_');
                lnk.addClass('webLinkPopup');
                lnk.on('click', function(e) {
                    e.stopEvent();
                    if (relProperties[1] == 'blank') {
                        window.open(lnk.dom.href, '_blank');
                    } else {
                        window.open(lnk.dom.href, '_blank', relProperties[1]);
                    }
                });
            }
        });
    });
};
