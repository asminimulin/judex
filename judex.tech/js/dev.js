var DeveloperTool={
    Init:function(){
        this.headObj =
            document.getElementsByTagName('html')[0].getElementsByTagName('head')[0];
        return this;
    },
    ReloadAllCSS : function(headObj) {
        console.log("DT:ReloadAllCSS");
        var links = headObj.getElementsByTagName('link');
        for (var i=0 ; i < links.length ; i++){
            var link = links[i];
            this.ReloadCSSLink(link);
        }
        return this;
    },
    ReloadCSSLink : function(item) {
        var value = item.getAttribute('href');
        var cutI = value.lastIndexOf('?');
        if (cutI != -1)
            value = value.substring(0, cutI);
        item.setAttribute('href', value + '?t=' + new Date().valueOf());
        return this;
    },
    ReloadAllCSSThisPage : function() {
        this.ReloadAllCSS(this.headObj);
        return this;
    }
};