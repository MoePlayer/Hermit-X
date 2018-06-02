/**
 * @name Hermit
 * @version 1.9
 * @create 2014-02-07
 * @lastmodified 2015-09-15 17:36
 * @description Hermit Plugin
 * @author MuFeng (http://mufeng.me)
 * @url http://mufeng.me/hermit-for-wordpress.html
 **/

jQuery(document).ready(function ($) {
    var formSrc = $("#hermit-form-template").html(),
        formTmpl = Handlebars.compile(formSrc),

        navSrc = $("#hermit-nav-template").html(),
        navTmpl = Handlebars.compile(navSrc),

        navigationSrc = $("#hermit-navigation-template").html(),
        navigationTmpl = Handlebars.compile(navigationSrc),

        tableSrc = $("#hermit-table-template").html(),
        tableTmpl = Handlebars.compile(tableSrc),

        $bodyLoader = $.mxloader('#wpwrap', true);

    Handlebars.registerHelper('catName', function (catid) {
        var html;

        for (var i = 0; i < hermit.catList.length; i++) {
            if (catid == hermit.catList[i].id) {
                html = '<a href="javascript:showTableByCat(' + catid + ')">' + hermit.catList[i].title + '</a>';
                break;
            }
        }

        return html;
    });

    Handlebars.registerHelper('catNav', function (arr, count) {
        var html,
            _class;

        _class = hermit.currentCatId == 0 ? ' class="current"' : '';
        html = '<li><a href="javascript:showTableByCat(0)"' + _class + '>全部<span class="count">（' + count + '）</span></a></li>';
        _class = '';

        $.each(arr, function (idx, val) {
            _class = val.id == hermit.currentCatId ? ' class="current"' : '';
            html += '<li> |<a href="javascript:showTableByCat(' + val.id + ')"' + _class + '>' + val.title + '<span class="count">（' + val.count + '）</span></a></li>';
        });

        return html;
    });

    Handlebars.registerHelper('catOption', function (arr, song_cat) {
        var html = '';

        $.each(arr, function (idx, val) {
            var _checked = val.id == song_cat ? ' selected="selected"' : '';
            html += '<option value="' + val.id + '"' + _checked + '>' + val.title + '</option>';
        });

        return html;
    });

    /*CYP的代码*/
    $('body').on('click', '.netease-confirm-button', function () {
        $.getJSON("http://" + window.location.host + "/wp-content/plugins/Hermit-X-master/include/netease.php", {
                id: $('#hermit-form-netease_id').val(),
                site: "netease"
            },
            function (data, status) {

                $('#hermit-form-song_name').val(function (n, c) {
                    if (c == '') {
                        return data.name;
                    } else {
                        return c;
                    }
                });
                $('#hermit-form-song_author').val(function (n, c) {
                    if (c == '') {
                        return data.artists[0].name;
                    } else {
                        return c;
                    }
                });
                $('#hermit-form-song_lrc').val(function (n, c) {
                    if (c == '') {
                        return 'http://www.godmusik.com/wp-json/hermit/v1/hermitlist/neteaseid/lrc/' + $('#hermit-form-netease_id').val();
                    } else {
                        return c;
                    }
                });
                $('#hermit-form-cover_url').val(function (n, c) {
                    if (c == '') {
                        return data.album.picUrl;
                    } else {
                        return c;
                    }
                });
                $('#hermit-form-song_url').val(function (n, c) {
                    if (c == '') {
                        return data.mp3Url.replace("http://m", "http://p");
                    } else {
                        return c;
                    }
                });
                $('#hermit-form-song_album').val(function (n, c) {
                    if (c == '') {
                        return data.album.name;
                    } else {
                        return c;
                    }
                });
                $('.song_name_search').val(function (n, c) {
                    if (c == '') {
                        return data.name + ' ' + data.artists[0].name;
                    } else {
                        return c;
                    }
                });
            });
    });
    var downcode = '';

    function browserRedirect() {

        $.get("http://" + window.location.host + "/wp-admin/upload.php?page=add-from-server").done(function (data) {
            downcode = $(data).find('#_wpnonce').val();
        });
    }

    browserRedirect();



    $('body').on('click', '#get-cover-url', function () {

        $('#get-cover-url').next('.loadingimg').fadeIn();
        //获取网易云音乐歌曲数据
        $.getJSON("http://" + window.location.host + "/wp-content/plugins/Hermit-X-master/include/netease.php", {
                id: $('#hermit-form-netease_id').val(),
                site: "netease"
            },
            function (data, status) {
                var filename = $('#hermit-form-song_album').val().replace(/\//g, "|");
                //下载的文件是否成功返回值
                $.getJSON("http://" + window.location.host + "/wp-content/plugins/add-from-server/down1.php", {
                        url: $('#hermit-form-cover_url').val(),
                        password: "28365411cypcpycy",
                        filename: filename + '.jpg',
                        submit: '确认下载'
                    },
                    function (data1, status) {
                        console.log(data1);
                        if (data1.status == 'seccessful') {
                            console.log(downcode);
                            //把下载以后的文件加入媒体库并得到文件地址
                            $.post("http://" + window.location.host + "/wp-admin/upload.php?page=add-from-server", {
                                    'files[]': [filename + '.jpg'],
                                    'import-date': "current",
                                    '_wpnonce': downcode,
                                    '_wp_http_referer': '/wp-admin/upload.php?page=add-from-server',
                                    'cwd': '/var/wwwroot/' + window.location.host + '/wp-content/plugins/add-from-server/temp',
                                    'import': 'Import'
                                },
                                function (data2) {

                                    if (data2.indexOf('has been added to Media library') > 0) {
                                        console.log('has been added to Media library');
                                        //获取文件地址
                                        $.getJSON("http://" + window.location.host + "/wp-json/wp/v2/media?orderby=id", function (data3) {
                                            console.log(data3[0].source_url);
                                            $('#hermit-form-cover_url').val(data3[0].source_url);
                                            $('#get-cover-url').next('.loadingimg').fadeOut();
                                        });
                                    }
                                });
                        }
                    });

            });


    });

    function getsongurl() {
        console.log($songurl);
        $('#get-songs-url1').next().fadeIn();
        $.getJSON("http://" + window.location.host + "/wp-content/plugins/add-from-server/down1.php", {
                url: $songurl,
                password: "28365411cypcpycy",
                filename: $('#hermit-form-song_name').val() + '.mp3',
                submit: '确认下载'
            },
            function (data, status) {
                console.log(data.status);
                if (data.status == 'seccessful') {
                    console.log(downcode);
                    //把下载以后的文件加入媒体库并得到文件地址
                    $.post("http://" + window.location.host + "/wp-admin/upload.php?page=add-from-server", {
                            'files[]': [$('#hermit-form-song_name').val() + '.mp3'],
                            'import-date': "current",
                            '_wpnonce': downcode,
                            '_wp_http_referer': '/wp-admin/upload.php?page=add-from-server',
                            'cwd': '/var/wwwroot/' + window.location.host + '/wp-content/plugins/add-from-server/temp',
                            'import': 'Import'
                        },
                        function (data2) {
                            if (data2.indexOf('has been added to Media library') > 0) {
                                console.log('has been added to Media library');
                                //获取文件地址
                                $.getJSON("http://" + window.location.host + "/wp-json/wp/v2/media?orderby=id", function (data3) {
                                    console.log(data3[0].source_url);
                                    $('#hermit-form-song_url').val(data3[0].source_url);
                                    $('#get-songs-url1').next().fadeOut();
                                });

                            }
                        });
                }
            });
    }


    //获取歌词
    $('body').on('click', '#get-song_lrc_detail', function () {
        $('#get-song_lrc_detail').next().fadeIn();

        var url = 'http://' + window.location.host + '/wp-content/plugins/Hermit-X-master/get_data.php?callback=?';
        $.get(url, {
            url: "http://music.163.com/api/song/lyric?id=" + $('#hermit-form-netease_id').val() + "&os=pc&lv=-1&kv=-1&tv=-1"
        }, function (data, stute) {
            data = eval("(" + data + ")");
            var lrca = data.lrc.lyric.indexOf('\\') < 0 ? data.lrc.lyric : data.lrc.lyric.replace(/\\/g, '');

            if (data.tlyric.lyric != null) {


                //data = data.replace(/\\/g,'');
                var tlrca = data.tlyric.lyric.indexOf('\\') < 0 ? data.tlyric.lyric.split('[') : data.tlyric.lyric.replace(/\\/g, '').split('[');


                $.each(tlrca, function (index, value) {
                    //var panduan = (value.search(/^(\d{2})\:(\d{2})/) > 0 || value.search(/^(\d{2})\:(\d{2})/) == 0);


                    //console.log(value);
                    if (value.indexOf(']') > 0 && (value.search(/^(\d{2})\:(\d{2})/) > 0 || value.search(/^(\d{2})\:(\d{2})/) == 0)) {


                        var thstrr = lrca.split(value.split(']')[0] + ']')[1].split('[')[0];
                        var thstrr1 = JSON.stringify(thstrr);
                        var valstr = JSON.stringify(value.split(']')[1]);
                        var value1 = JSON.parse(thstrr1.indexOf('\\n') < 0 ? thstrr1 : thstrr1.replace(/\\n/g, ''));
                        var value2 = valstr.indexOf('\\n') == 1 || valstr.indexOf('\\n') == '-1' ? value.split(']')[1] : "(" + JSON.parse(valstr.replace(/\\n/g, ')\\n'));
                        lrca = lrca.replace(thstrr, value1 + value2);
                    } else if (value != '' && value != ' ') {
                        console.log(value);
                        lrca = lrca + '[' + value;
                    }
                    if (index === tlrca.length - 1) {
                        //console.log(index);
                        $('#hermit-form-song_lrc_detail').val(lrca);
                    }

                });

            } else if (data.tlyric.lyric == null) {

                $('#hermit-form-song_lrc_detail').val(lrca);
            }
            if (stute == 'success') {
                $('#get-song_lrc_detail').next().fadeOut();
            }
        });
        /*$.getJSON($('#hermit-form-song_lrc').val(), function (data, stute) {
            
            $('#hermit-form-song_lrc_detail').val(data.lrc.lyric);
            if (stute == 'success') {
                $('#get-song_lrc_detail').next().fadeOut();
            }
        });*/
        $('#hermit-form-song_lrc').val('http://' + window.location.host + '/wp-json/hermit/v1/hermitlist/neteaseid/lrc/' + $('#hermit-form-netease_id').val());
    });

    //获取歌曲地址
    $('body').on('click', '#get-songs-url1', function () {
        $songurl = 'https://api.lwl12.com/music/netease/song?id=' + $('#hermit-form-netease_id').val();
        getsongurl();

    });

    $('body').on('click', '#get-songs-url', function () {

        if ($('#hermit-form-song_url').val() != 'http://p2.music.126.net/hmZoNQaqzZALvVp0rE7faA==/0.mp3') {
            $songurl = $('#hermit-form-song_url').val();
        } else {
            $songurl = 'https://api.lwl12.com/music/netease/song?id=' + $('#hermit-form-netease_id').val();
        }

        getsongurl();

    });
    //搜索音乐
    $('body').on('click', '.song_search', function () {
        $.get('http://www.godmusik.com/wp-content/plugins/Hermit-X-master/get_data.php?url=http://musicmini.baidu.com/app/search/searchList.php?qword=' + $('.song_name_search').val(), function (data, stute) {
            $('.list_music').html($(data).find('#sc-table'));
        });
    });
    //获取音乐
    $('body').on('click', '.get_search_song_url', function () {

        $('input[class="sCheckBox"]:checked').each(function () {
            $.getJSON('http://music.baidu.com/data/music/fmlink?callback=?', {
                songIds: $(this).attr('id')
            }, function (data, stute) {
                $('#hermit-form-song_url').val(data.data.songList[0].songLink);
                //console.log(data.data.songList[0].songLink);
            })
        });
        $('.list_music').empty();
    });



    /*CYP的代码*/

    //上传mp3
    $('body').on('click', '#hermit-form-song_url-upload', function () {
        /**
         * 采用 3.5之后的新上传图片方法
         * 不再支持3.5以下 Wordpress 版本
         */

        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: '上传本地音乐（推荐 mp3 格式，尽量用英文名称）',
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#hermit-form-song_url').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });
    /*CYP的代码*/
    //上传jpg
    $('body').on('click', '#hermit-form-cover_url-upload', function () {
        /**
         * 采用 3.5之后的新上传图片方法
         * 不再支持3.5以下 Wordpress 版本
         */

        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: '上传本地图片（推荐 jpg 格式，尽量用英文名称）',
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#hermit-form-cover_url').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });
    /*CYP的代码*/
    //新建音乐
    $('.add-new-h2').click(function () {
        var sobj = {
            id: 0,
            catList: hermit.catList
        };

        form(sobj)
    });

    //编辑
    $('.hermit-list-table').on('click', '.hermit-edit', function () {
        var $this = $(this),
            index = $this.attr('data-index'),
            sobj = hermit.data[index];

        sobj.catList = hermit.catList;

        form(sobj)
    });

    //单个删除
    $('.hermit-list-table').on('click', '.hermit-delete', function () {
        var $this = $(this),
            ids = $this.attr('data-id');

        dele(ids)
    });

    //选中删除
    $('.hermit-delete-all').click(function () {
        if ($(this).prev('.hermit-action-selector') == 'trash') {
            var arr = [];

            $('.cb-select-th').each(function () {
                var $this = $(this);

                if ($this.prop("checked")) {
                    arr.push($this.val())
                }
            });

            arr = arr.join(',');
            dele(arr)
        }
    });

    //新建分类
    $('.hermit-list-table').on('click', '.hermit-new-nav', function () {
        var title = window.prompt("新建分类", "");

        if (title) {
            $bodyLoader.showProgress('新建分类中');

            $.ajax({
                url: hermit.adminUrl,
                data: {
                    type: 'catnew',
                    title: title
                },
                type: 'post',
                success: function (data) {
                    hermit.catList = data;
                    $bodyLoader.showSuccess('新建分类成功');
                },
                error: function () {
                    $bodyLoader.showError('分类已存在');
                }
            });
        }
    });

    //初始化
    initView();

    //监测总数
    watch(hermit, 'count', function () {
        initCatNav();
        initNavigation();
    });

    //监测菜单
    watch(hermit, 'currentCatId', function () {
        list({
            page: 1,
            catid: hermit.currentCatId
        }, function () {
            initView();
        });
    });

    //监测曲库
    watch(hermit, 'data', function () {
        initTable()
    });

    //检测分类
    watch(hermit, 'catList', function () {
        initCatNav();
    });

    window.showTableByCat = function (catid) {


        /*CYP的代码*/
        //hermit.currentCatId = catid;
        window.location.href = location.pathname + '?page=hermit&catid=' + catid;

        /*CYP的代码*/
    };

    function initView() {
        initCatNav();
        initNavigation();
        initTable();
    }

    function initCatNav() {
        $('.subsubsub').html(navTmpl(hermit));
    }

    function initNavigation() {
        $('.tablenav-pages').html(navigationTmpl(hermit))
            //分页
            .mxpage({
                perPage: 5,
                currentPage: 1, //当前页数
                maxPage: hermit.maxPage, //最大页数
                previousText: '‹', //上一页标题
                nextText: '›', //下一页标题
                frontPageText: '«', //最前页标题
                lastPageText: '»', //最后页标题
                click: function (index) {
                    list({
                        page: index
                    })
                }
            });
    }

    function initTable() {
        $('.wp-list-table tbody').html(tableTmpl(hermit));
    }

    function form(sobj) {
        var main_html = formTmpl(sobj),
            msg = {};

        if (sobj.id > 0) {
            msg.title = '更新音乐';
            msg.success = '更新成功';
            msg.error = '更新失败，请稍后重试。';
        } else {
            msg.title = '新建音乐';
            msg.success = '新建成功';
            msg.error = '新建失败，请稍后重试。';
        }

        $.mxlayer({
            title: msg.title,
            main: main_html,
            button: msg.title,
            width: 720,
            height: 540,
            cancel: function () {},
            confirm: function (that) {
                /*CYP的代码*/
                var formKey = ['netease_id', 'song_name', 'song_author', 'song_album', 'song_url', 'song_cat', 'song_lrc', 'song_lrc_detail', 'cover_url'],
                    formObj = {};
                /*CYP的代码*/

                $bodyLoader.showProgress('数据上传中');

                for (var i = 0; i < formKey.length; i++) {
                    var _id = formKey[i],
                        $elem = $('#hermit-form-' + _id),
                        val = $elem.val();

                    /*CYP的代码*/
                    if (isEmpty(val) && i != 4 && i != 5) {

                        $bodyLoader.showError('请输入正确的信息。');
                        return false;
                    }
                    /*CYP的代码*/

                    formObj[_id] = val
                }

                if (sobj.id > 0) {
                    formObj.id = sobj.id;
                    formObj.type = 'update';
                } else {
                    formObj.type = 'new';
                }

                $.ajax({
                    url: hermit.adminUrl,
                    data: formObj,
                    type: 'post',
                    success: function (data) {
                        $bodyLoader.showSuccess(msg.success);

                        that.fireEvent();

                        list({
                            page: 1,
                            catid: hermit.currentCatId
                        }, function () {
                            initView();
                        });
                    },
                    error: function () {
                        $bodyLoader.showError(msg.error);
                    }
                });
            }
        })
    }

    function list(lobj, callback) {
        $bodyLoader.showProgress('音乐加载中');

        if (lobj.catid > 0) {
            lobj = {
                type: 'list',
                paged: lobj.page,
                catid: lobj.catid
            }
        } else {
            lobj = {
                type: 'list',
                paged: lobj.page
            }
        }

        $.ajax({
            url: hermit.adminUrl,
            data: lobj,
            success: function (result) {
                $bodyLoader.dismiss();

                hermit.data = result.data;

                if (callback) {
                    hermit.count = result.count;
                    hermit.maxPage = result.maxPage;

                    callback();
                }
            },
            error: function () {
                $bodyLoader.showError('加载失败');
            }
        })
    }

    function dele(ids) {
        var cofim = window.confirm('确认删除?');

        if (cofim) {
            $bodyLoader.showProgress('删除音乐中');

            $.ajax({
                url: hermit.adminUrl,
                type: 'post',
                data: {
                    type: 'delete',
                    ids: ids
                },
                success: function (result) {
                    hermit.catList = result;
                    list({
                        page: 1,
                        catid: hermit.currentCatId
                    }, function () {
                        initView();
                    });
                },
                error: function () {
                    $bodyLoader.showProgress('删除失败，请稍后重试。');
                }
            })
        }
    }

    function isEmpty(str) {
        if (str == null || str == undefined) {
            return true;
        }

        return str.replace(/(^\s*)|(\s*$)/g, "").length == 0
    }
});
