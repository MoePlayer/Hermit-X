/**
 * @name Hermit X
 * @version 1.9.4
 * @create 2014-02-07
 * @lastmodified 2017-01-14 21:00
 * @description Hermit X Plugin
 * @author MuFeng (http://mufeng.me), liwanglin12 (https://lwl12.com)
 * @url http://mufeng.me/hermit-for-wordpress.html
 **/
jQuery(document).ready(function(b) {
    function n(c, a) {
        if (d.array = "", "xiami" == c) {
            switch (d.type) {
                case "xiami_songlist":
                    (a = a.match(/(http|https):\/\/(www.)?xiami.com\/song\//gi)) && 0 < a.length && (d.array = "Wating Parse...");
                    break;
                case "xiami_album":
                    (a = a.match(/(http|https):\/\/(www.)?xiami.com\/album\//gi)) && 0 < a.length && (d.array = "Wating Parse...");
                    break;
                case "xiami_playlist":
                    (a = a.match(/(http|https):\/\/(www.)?xiami.com\/collect\//gi)) && 0 < a.length && (d.array = "Wating Parse...");
                    break
            }
        }
        if ("netease" == c) {
            switch (d.type) {
                case "netease_songlist":
                    (a = a.match(/(song\?id=(\d+)|\/song\/(\d+))/gi)) && 0 < a.length && (es = [], b.each(a,
                        function(a, c) {
                            -1 === b.inArray(c, e) && es.push(c)
                        }), d.array = es.join(",").replace(/(song\?id=|\/song\/)/gi, ""));
                    break;
                case "netease_songs":
                    (a = a.match(/song\?id=(\d+)/gi)) && 0 < a.length && (e = [], b.each(a,
                        function(a, c) {
                            - 1 === b.inArray(c, e) && e.push(c)
                        }), d.array = e.join(",").replace(/song\?id=/g, ""));
                        break;
                case "netease_album":
                    (a = a.match(/(album\?id=(\d+)|\/album\/(\d+))/i)) && 0 < a.length && (d.array = a[0].replace(/(album\?id=|\/album\/)/gi, ""));
                    break;
                case "netease_playlist":
                    (a = a.match(/(playlist\?id=(\d+)|\/playlist\/(\d+))/i)) && 0 < a.length && (d.array = a[0].replace(/(playlist\?id=|\/playlist\/)/gi, ""))
            }
        }

        if ("tencent" == c) {
            switch (d.type) {
                case "tencent_songlist":
                    (a = a.match(/y\.qq\.com\/n\/yqq\/song\/([A-Za-z0-9]+)/gi)) && 0 < a.length && (e = [], b.each(a,
                    function(a, c) {
                        - 1 === b.inArray(c, e) && e.push(c)
                    }), d.array = e.join(",").replace(/y\.qq\.com\/n\/yqq\/song\//gi, ""));
                    break;
                case "tencent_album":
                    (a = a.match(/y\.qq\.com\/n\/yqq\/album\/([A-Za-z0-9]+)/i)) && 0 < a.length && (d.array = a[0].replace(/y\.qq\.com\/n\/yqq\/album\//gi, ""));
                    break;
                case "tencent_playlist":
                    (a = a.match(/y\.qq\.com\/n\/yqq\/playlist\/(\d+)/i)) && 0 < a.length && (d.array = a[0].replace(/y\.qq\.com\/n\/yqq\/playlist\//gi, ""))
            }
        }
         if ("kugou" == c) {
             switch (d.type) {
                 case "kugou_songlist":
                     (a = a.match(/[A-Za-z0-9]+/gi)) && 0 < a.length && (es = [], b.each(a,
                     function(a, c) {
                         -1 === b.inArray(c, e) && es.push(c)
                     }), d.array = es.join(","));
                     break;
                 case "kugou_album":
                     (a = a.match(/[A-Za-z0-9]+/i)) && 0 < a.length && (d.array = a[0]);
                     break;
                 case "kugou_playlist":
                     (a = a.match(/[A-Za-z0-9]+/i)) && 0 < a.length && (d.array = a[0]);
             }
        }
        if ("baidu" == c) {
            switch (d.type) {
                case "baidu_songlist":
                    (a = a.match(/music\.taihe\.com\/song\/\d+/gi)) && 0 < a.length && (es = [], b.each(a,
                    function(a, c) {
                        -1 === b.inArray(c, e) && es.push(c)
                    }), d.array = es.join(",").replace(/music\.taihe\.com\/song\//gi, ""));
                    break;
                case "baidu_album":
                    (a = a.match(/music\.taihe\.com\/album\/\d+/i)) && 0 < a.length && (d.array = a[0].replace(/music\.taihe\.com\/album\//gi, ""));
                    break;
                case "baidu_playlist":
                    (a = a.match(/music\.taihe\.com\/songlist\/\d+/i)) && 0 < a.length && (d.array = a[0].replace(/music\.taihe\.com\/songlist\//gi, ""))
            }
        }
        "remote" == c && (d.type = "remote", d.array = a);
        d.array ? b("#hermit-shell-insert").removeAttr("disabled") : b("#hermit-shell-insert").attr("disabled", "disabled");
        k(c, d)
    }

    function k(c, a) {
        l = '[hermit autoplay="' + (a.auto ? 'true' : 'false') + '" mode="' + a.mode + '" preload="' + a.preload +'"]' + a.type + "#:" + a.array + "[/hermit]";
        b("#hermit-preview").text(l).addClass("texted")
    }
    void 0 === window.send_to_editor && (window.send_to_editor = function(b) {
        var a, d = "undefined" != typeof tinymce,
            e = "undefined" != typeof QTags;
        if (wpActiveEditor) {
            d && (a = tinymce.get(wpActiveEditor))
        } else {
            if (d && tinymce.activeEditor) {
                a = tinymce.activeEditor,
                    wpActiveEditor = a.id
            } else {
                if (!e) {
                    return !1
                }
            }
        }
        if (a && !a.isHidden() ? a.execCommand("mceInsertContent", !1, b) : e ? QTags.insertContent(b) : document.getElementById(wpActiveEditor).value += b, window.tb_remove) {
            try {
                window.tb_remove()
            } catch (f) {}
        }
    });
    var f, l, h, r = b("#hermit-create"),
        e = b("body"),
        p = b("#hermit-template").html(),
        t = Handlebars.compile(p),
        p = b("#hermit-remote-template").html(),
        q = Handlebars.compile(p),
        d = {
            type: "",
            array: "",
            auto: 0,
            loop: 0,
            unexpand: 0,
            fullheight: 0
        },
        m = 1,
        g = !1;
    r.length ? r.click(function() {
        f = "netease";
        d = {
            type: "",
            array: "",
            auto: 0,
            loop: 0,
            unexpand: 0,
            fullheight: 0
        };
        l = "";
        e.append(t());
        d.mode = 'circulation';
        d.preload = 'auto';
        b("body").addClass("hermit-hidden")
    }) : 1 == hermit.roles.length && "contributor" == hermit.roles[0] && (b("#wp-content-editor-tools").prepend('<div id="wp-content-media-buttons" class="wp-media-buttons"><a id="hermit-create" class="button" href="javascript:;" title="\u6dfb\u52a0\u97f3\u4e50"><img src="' + hermit.plugin_url + '/assets/images/logo@2x.png" width="16" height="16"> \u6dfb\u52a0\u97f3\u4e50</a></div>'), b("#wp-content-editor-tools").on("click", "#hermit-create",
        function() {
            f = "netease";
            d = {
                type: "",
                array: "",
                auto: 0,
                loop: 0,
                unexpand: 0,
                fullheight: 0
            };
            l = "";
            e.append(t());
            b("body").addClass("hermit-hidden")
        }));
    e.on("click", "#hermit-shell-close",
        function() {
            b("#hermit-shell").remove();
            b("body").removeClass("hermit-hidden")
        });
    e.on("click", "#hermit-shell-insert",
        function() {
            if (f == "xiami") {
                id_parse('xiami');
            // } else if (d.type == "tencent_songlist") {
            //     id_parse('tencent');
             } else {
                "disabled" != b(this).attr("disabled") && (send_to_editor(l.replace(/,+$/g, "")), b("#hermit-shell").remove());
                b("body").removeClass("hermit-hidden")
            }
        });
    function id_parse(site) {
        switch (site) {
            case 'xiami':
                b("#hermit-shell-insert").text( "Xiami Music ID Parsing...");
                break;
            // case 'tencent':
            //     b("#hermit-shell-insert").text("Tencent Music ID Parsing...");
        }
        b.ajax({
            url: hermit.ajax_url,
            data: {
                action: "hermit",
                scope: site + "_id_parse",
                src: b(".hermit-li.active .hermit-textarea").val().replace(/\n/g, ","),
            },
            success: function(c) {
                b("#hermit-shell-insert").text("插入至文章");
                "disabled" != b(this).attr("disabled") && (send_to_editor(l.replace("Wating Parse...", c.msg.join(",").replace(/,+$/g, ""))), b("#hermit-shell").remove());
                b("body").removeClass("hermit-hidden")
            },
            error: function() {
                b("#hermit-shell-insert").text("插入至文章");
                alert("\u83b7\u53d6\u5931\u8d25, \u8bf7\u7a0d\u5019\u91cd\u8bd5")
            }
        })
    }
    e.on("click", "#hermit-remote-content ul li",
        function() {
            var c = b(this),
                a = [];
            c.hasClass("selected") ? c.removeClass("selected") : c.addClass("selected");
            b("#hermit-remote-content ul li.selected").each(function() {
                a.push(b(this).attr("data-id"))
            });
            a = a.join(",");
            n(f, a)
        });
    e.on("click", ".media-router a",
        function() {
            var c = b(this),
                a = b(".media-router a").index(c);
            c.hasClass("active") || (b(".media-router a.active,.hermit-li.active").removeClass("active"), c.addClass("active"), b(".hermit-li").eq(a).addClass("active"), f = b(".hermit-li").eq(a).attr("data-type"), "remote" == f && (h ? b("#hermit-remote-content ul").html(q(h)) : b.ajax({
                url: hermit.ajax_url,
                data: {
                    action: "hermit_source",
                    type: "list",
                    paged: 1
                },
                beforeSend: function() {
                    g = !0
                },
                success: function(a) {
                    g = !1;
                    h = a;
                    b("#hermit-remote-content ul").html(q(h));
                    b("#hermit-remote-button").text("\u52a0\u8f7d\u66f4\u591a  (1/" + hermit.max_page + ")").show()
                },
                error: function() {
                    m = 0;
                    g = !1;
                    b("#hermit-remote-button").text("\u52a0\u8f7d\u66f4\u591a  (0/" + hermit.max_page + ")").show();
                    alert("\u83b7\u53d6\u5931\u8d25, \u8bf7\u7a0d\u5019\u91cd\u8bd5")
                }
            })))
        });
    e.on("click", "#hermit-auto",
        function() {
            var c = b(this);
            d.auto = c.prop("checked") ? 1 : 0;
            k(f, d)
        });
    e.on("change", "#hermit-mode",
        function() {
            var c = b(this);
            d.mode = c.val();
            k(f, d)
        });
    e.on("change", "#hermit-preload",
        function() {
            var c = b(this);
            d.preload = c.val();
            k(f, d)
        });
    e.on("change", ".hermit-li.active input",
        function() {
            var c = b(".hermit-li.active .hermit-textarea").val();
            d.type = b(".hermit-li.active input:checked").val();
            n(f, c)
        });
    e.on("focus keyup input paste", ".hermit-textarea",
        function() {
            var c = b(this).val();
            d.type = b(".hermit-li.active input:checked").val();
            n(f, c)
        });
    e.on("click", "#hermit-remote-button",
        function() {
            g || (m >= hermit.max_page ? b(this).text("\u5df2\u662f\u6700\u540e\u4e00\u9875").fadeOut(1500,
                function() {
                    b(this).hide()
                }) : (g = !0, b(this).addClass("loading").text("\u52a0\u8f7d\u4e2d..."), b.ajax({
                url: hermit.ajax_url,
                data: {
                    action: "hermit_source",
                    type: "list",
                    paged: m + 1
                },
                success: function(c) {
                    h.data = h.data.concat(c.data);
                    b("#hermit-remote-content ul").append(q(c));
                    m++;
                    b("#hermit-remote-button").text("\u52a0\u8f7d\u66f4\u591a  (" + m + "/" + hermit.max_page + ")");
                    g = !1
                },
                error: function() {
                    alert("\u83b7\u53d6\u5931\u8d25, \u8bf7\u7a0d\u5019\u91cd\u8bd5");
                    g = !1
                }
            })))
        })
});
