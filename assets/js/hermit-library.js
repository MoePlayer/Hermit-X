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

        lrcSrc = $("#hermit-lrc-template").html(),
        lrcTmpl = Handlebars.compile(lrcSrc),

        catmovSrc = $("#hermit-move-cat-template").html(),
        catmovTmpl = Handlebars.compile(catmovSrc),

        manageSrc = $("#hermit-manage-cat-template").html(),
        manageTmpl = Handlebars.compile(manageSrc),

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

    Handlebars.registerHelper('catCover', function (cover_url, name) {
        var html;

        if (cover_url != "")html = '<img class="cover" src="' + cover_url +'" title="' + name +'" alt="图片加载失败">';
        else html = '<p>没有封面图</p>'

        return html;
    });

    Handlebars.registerHelper('catLrc', function (index) {
        var html;

        html = '<a href="javascript:" class="hermit-show-lrc" data-index="' + index +'">显示歌词</a>';

        return html;
    });

    Handlebars.registerHelper('catAction', function (id) {
        var html = '';

        if (id !== '1') {
            html = '<a href="javascript:;" class="hermit-cat-edit" data-id="' + id + '">编辑</a> | <a href="javascript:;" class="hermit-cat-delete" data-id="' + id + '">删除</a>';
        } else {
            html = '<p>默认分类禁止编辑/删除</p>'
        }

        return html;
    });

    //上传mp3
    $('body').on('click', '#hermit-form-song_url-upload', function(){
        /**
         * 采用 3.5之后的新上传图片方法
         * 不再支持3.5以下 Wordpress 版本
         */

        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: '上传本地音乐（推荐 mp3 格式，尽量用英文名称）',
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#hermit-form-song_url').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });

    //上传封面图片
    $('body').on('click', '#hermit-form-song_cover-upload', function(){
        /**
         * 采用 3.5之后的新上传图片方法
         * 不再支持3.5以下 Wordpress 版本
         */

            // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
                title: '上传本地图片（尽量用英文名称）',
                multiple: false  // Set to true to allow multiple files to be selected
            });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $('#hermit-form-song_cover').val(attachment.url);
        });

        // Finally, open the modal
        file_frame.open();
    });

    //新建音乐
    $('.add-new-h2').click(function () {
        var sobj = {
            id: 0,
            catList: hermit.catList
        };

        form(sobj)
    });

    //显示歌词
    $('.hermit-list-table').on('click', '.hermit-show-lrc', function () {
        var $this = $(this),
            index = $this.attr('data-index'),
            sobj = hermit.data[index],
            main_html = lrcTmpl(sobj);

        $.mxlayer({
            title: sobj["song_name"],
            main: main_html,
            button: "关闭",
            width: 720,
            height: 720,
            confirm: function (that) {
                that.fireEvent();
            }
        })
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
    $('.hermit-selector-button').click(function() {
        if ($(this).prev('.hermit-action-selector').val() == 'trash') {
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
        else if ($(this).prev('.hermit-action-selector').val() == 'movecat') {
            var arr = [];

            $('.cb-select-th').each(function () {
                var $this = $(this);

                if ($this.prop("checked")) {
                    arr.push($this.val())
                }
            });

            arr = arr.join(',');
            move_cat(arr)
        }
    });

    //管理分类
    $('.hermit-list-table').on('click', '.hermit-manage-nav', function () {
        $.mxlayer({
            title: '分类管理',
            main: manageTmpl(hermit),
            button: '关闭',
            width: 720,
            height: 720,
            cancel: function () {
            },
            confirm: function (that) {
                that.fireEvent()
            }
        })
    });

    //新建分类
    $('body').on('click', '.hermit-new-nav', function () {
        var title = window.prompt("新建分类","");

        if( title ){
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
                    list({
                        page: 1,
                        catid: hermit.currentCatId
                    }, function () {
                        initView();
                        $('.mxlayer-main-body').html(manageTmpl(hermit));
                    });
                },
                error: function () {
                    $bodyLoader.showError('分类已存在');
                }
            });
        }
    });

    //删除分类
    $('body').on('click', '.hermit-cat-delete', function () {
        var $this = $(this),
            id = $this.attr('data-id');

        if ( id === '1' )return;

        var cofim = window.confirm('确认删除?');

        if (cofim) {
            $bodyLoader.showProgress('删除分类中');

            $.ajax({
                url: hermit.adminUrl,
                type: 'post',
                data: {
                    type: 'catdel',
                    id: id
                },
                success: function (result) {
                    hermit.catList = result;
                    $bodyLoader.showSuccess('删除分类成功');
                    list({
                        page: 1,
                        catid: hermit.currentCatId
                    }, function () {
                        initView();
                        $('.mxlayer-main-body').html(manageTmpl(hermit));
                    });
                },
                error: function () {
                    $bodyLoader.showProgress('删除失败，请稍后重试。');
                }
            })
        }
    });

    //编辑分类
    $('body').on('click', '.hermit-cat-edit', function () {
        var title = window.prompt("重命名分类",""),
            $this = $(this),
            id = $this.attr('data-id');

        if ( id === '1' )return;

        if( title ){
            $bodyLoader.showProgress('重命名分类中');

            $.ajax({
                url: hermit.adminUrl,
                data: {
                    type: 'catupd',
                    title: title,
                    id: id
                },
                type: 'post',
                success: function (data) {
                    hermit.catList = data;
                    $bodyLoader.showSuccess('重命名分类成功');
                    list({
                        page: 1,
                        catid: hermit.currentCatId
                    }, function () {
                        initView();
                        $('.mxlayer-main-body').html(manageTmpl(hermit));
                    });
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

    window.showTableByCat = function(catid){
        hermit.currentCatId = catid;
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
                                        page: index,
                                        catid: hermit.currentCatId
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
            height: 720,
            cancel: function () {
            },
            confirm: function (that) {
                var formKey = ['song_name', 'song_author', 'song_url', 'song_cat', 'song_cover', 'song_lrc'],
                    formObj = {};

                $bodyLoader.showProgress('数据上传中');

                for (var i = 0; i < formKey.length; i++) {
                    var _id = formKey[i],
                        $elem = $('#hermit-form-' + _id),
                        val = $elem.val();

                    if (isEmpty(val)) {
                        if (i < 4) {
                            $bodyLoader.showError('请输入正确的信息。');
                            return false;
                        } else {
                            val = '';
                        }
                    }

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
                    hermit.catList = result.catList;

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

    function move_cat(ids) {
        $.mxlayer({
            title: '选择目标分类',
            main: catmovTmpl(hermit),
            button: '提交',
            width: 720,
            height: 220,
            cancel: function () {
            },
            confirm: function (that) {
                $bodyLoader.showProgress('移动音乐中');
                var data = {
                    type: 'move',
                    catid: $('#hermit-move-song_cat').val(),
                    ids: ids
                };
                $.ajax({
                    url: hermit.adminUrl,
                    data: data,
                    type: 'post',
                    success: function (data) {
                        $bodyLoader.showSuccess('移动成功');

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

    function isEmpty(str) {
        if (str == null || str == undefined) {
            return true;
        }

        return str.replace(/(^\s*)|(\s*$)/g, "").length == 0
    }
});