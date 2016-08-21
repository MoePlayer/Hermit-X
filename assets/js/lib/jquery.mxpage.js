/**
 * @name MXPage.js
 * @version 0.0.2
 * @create 2015-09-15
 * @lastmodified 2015-09-15 17:36
 * @description jQuery page navigation plugin
 * @author MuFeng (http://mufeng.me)
 * @url http://mufeng.me
 **/
;(function ($) {
    var array, MXPage = function (x, y) {
        this.settings = x;
        this.element = $(y);

        this.layout();
    };

    MXPage.prototype = {
        layout: function () {
            if (this.settings.maxPage <= 1) return;

            this.fireEvent();

            //最前页
            this.build(1, 'mxpage-front', this.settings.frontPageText);

            //上一页
            this.build(Math.max(1, this.settings.currentPage - 1), 'mxpage-previous', this.settings.previousText);

            //中间
            this.element.children('.pagination-links').append('<span class="paging-input"> '+this.settings.currentPage+'/'+this.settings.maxPage+' </span></span>');

            //下一页
            this.build(Math.min(this.settings.maxPage, this.settings.currentPage + 1), 'mxpage-next', this.settings.nextText);

            //最后页
            this.build(this.settings.maxPage, 'mxpage-last', this.settings.lastPageText);

            //绑定点击事件
            this.addEvent();
        },

        build: function (index, className, title) {
            title = title || index;

            var html,
                that = this;

            if (className == 'mxpage-default' && this.settings.currentPage == index) {
                html = $('<span class="tablenav-pages-navspan">' + index + '</span>')
            }else{
                html = $('<a href="javascript:;" class="mxpage pagination-links ' + className + '" data-page="' + index + '">' + title + '</a>')
            }

            that.element.children('.pagination-links').append(html);
        },

        addEvent: function () {
            var that = this;

            that.element.on('click', '.mxpage', function (event) {
                var $this = $(this),
                    index = parseInt($this.attr('data-page'));

                if (that.settings.currentPage == index) return;

                that.settings.currentPage = index;
                that.layout();

                //回调
                that.settings.click(index, $this);
            });
        },

        fireEvent: function () {
            if (this.element.children('.pagination-links')) {
                this.element.children('.pagination-links').remove();
                this.element.off('click');
            }

            this.element.append('<span class="pagination-links"></span>');
        }
    };

    $.mxpage = function (x, y) {
        //参数合并
        x = $.extend({
            perPage: 10,
            currentPage: 1,
            maxPage: 1,
            previousText: 'previous',
            nextText: 'next',
            frontPageText: 'front page',
            lastPageText: 'last page',
            click: function (index, $element) {}
        }, x);

        $.data(y, 'mxpage', new MXPage(x, y));

        return y;
    };

    $.fn.mxpage = function (x) {
        return $.mxpage(x, this);
    };
})(jQuery);