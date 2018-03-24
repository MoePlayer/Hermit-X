function cloneObject(src) {
    if (src == null || typeof src != "object") {
        return src
    }
    if (src instanceof Date) {
        var clone = new Date(src.getDate());
        return clone
    }
    if (src instanceof Array) {
        var clone = [];
        for (var i = 0, len = src.length; i < len; i++) {
            clone[i] = src[i]
        }
        return clone
    }
    if (src instanceof Object) {
        var clone = {};
        for (var key in src) {
            if (src.hasOwnProperty(key)) {
                clone[key] = cloneObject(src[key])
            }
        }
        return clone
    }
}

function hermitInit() {
    var aps = document.getElementsByClassName("aplayer");
    var apnum = 0;
    ap = [];
    var xhr = [];
    var option = [];
    for (let i = 0; i < aps.length; i++) {
        if (aps[i].dataset.songs) {
            option[i] = cloneObject(aps[i].dataset);
            option[i].element = aps[i];
            xhr[i] = new XMLHttpRequest();
            xhr[i].onreadystatechange = function() {
                var index = xhr.indexOf(this);
                var op = option[index];
                op.storageName = "HxAP-Setting";
                if (this.readyState === 4) {
                    if (this.status >= 200 && this.status < 300 || this.status === 304) {
                        var response = JSON.parse(this.responseText);
                        op.music = response.msg.songs;
                        if (op.music === undefined) {
                            console.warn("Hermit-X failed to load " + option[index].songs);
                            return false;
                        }
                        if (op.showlrc === undefined) {
                            if (op.music[0].lrc) {
                                op.lrcType = 3
                            } else {
                                op.lrcType = 0
                            }
                        }
                        if (op.music.length === 1) {
                            op.music = op.music[0];
                        }
                        if (op.autoplay) {
                            op.autoplay = (op.autoplay === "true");
                        }
                        if (op.listfolded) {
                            op.listFolded = (op.listfolded === "true");
                        }
                        if (op.mutex) {
                            op.mutex = (op.mutex === "true");
                        }
                        if (op.narrow) {
                            op.narrow = (op.narrow === "true");
                        }
                        ap[i] = new APlayer(op);
                        ap[i].parseRespons = response;
                        if (window.APlayerCall && window.APlayerCall[i]) window.APlayerCall[i]();
                        if (window.APlayerloadAllCall && aps.length != ap.length) {
                            window.APlayerloadAllCall();
                        }
                    } else {
                        console.error("Request was unsuccessful: " + this.status);
                    }
                }
            };
            var scope = option[i].songs.split("#:");
            apiurl = HermitX.ajaxurl + "?action=hermit&scope=" + option[i].songs.split("#:")[0] + "&id=" + option[i].songs.split("#:")[1] + "&_nonce=" + option[i]._nonce;
            xhr[i].open("get", apiurl, true);
            xhr[i].send(null);
        }
    }
}

function reloadHermit() {
    for (var i = 0; i < ap.length; i++) {
        try {
            ap[i].destroy()
        } catch (e) {}
    }
    hermitInit()
}
hermitInit();
console.log(`\n %c Hermit X Music Helper v` + HermitX.version + ` %c https://lwl.moe/HermitX \n`, `color: #fff; background: #4285f4; padding:5px 0;`, `background: #66CCFF; padding:5px 0;`);
