/**
 * @name MXLayer.js
 * @version 0.0.1
 * @create 2015-09-15
 * @lastmodified 2015-09-15 17:36
 * @description jQuery popup layer plugin
 * @author MuFeng (http://mufeng.me)
 * @url http://mufeng.me
 **/
;(function ($) {
    var MXLayer = function (x) {
        this.settings = x; //console.log(this.settings);
        this.layout();
    };

    MXLayer.prototype = {
        layout: function () {
            var $element = $(this.html());
            $('body').append($element);

            this.element = $element;

            if(this.settings.sidebar){
                this.element.addClass('sidebar');
                this.element.find('.mxlayer-sidebar-body').html(this.settings.sidebar)
            }

            this.element.find('.mxlayer-main-header h1').text(this.settings.title);
            this.element.find('.mxlayer-confirm-button').text(this.settings.button);
            this.element.find('.mxlayer-main-body').html(this.settings.main);

            if(this.settings.width && this.settings.height){
                this.element.find('.mxlayer-content').css({
                    width: this.settings.width,
                    height: this.settings.height,
                    left: '50%',
                    top: '50%',
                    marginLeft: -this.settings.width/2,
                    marginTop: -this.settings.height/2
                })
            }

            this.addEvent();
        },

        addEvent: function(){
            var that = this,
                closeElement = that.element.find('.mxlayer-close'),
                confirmElement = that.element.find('.mxlayer-confirm-button');

            closeElement.on('click', function(event){
                console.log($(this));
                that.fireEvent();

                that.settings.cancel.call(null, that)
            });

            confirmElement.on('click', function(event){
                that.settings.confirm.call(null, that)
            });
        },

        fireEvent: function()
        {
            var closeElement = this.element.find('.mxlayer-close'),
                confirmElement = this.element.find('.mxlayer-confirm-button');
                
            closeElement.off('click');
            confirmElement.off('click');
            this.element.remove();
            this.element = null;
        },

        html: function(){
            return  '<div class="mxlayer-container">\
                        <div class="mxlayer-content">\
                            <div class="mxlayer-sidebar">\
                                <div class="mxlayer-sidebar-body"></div>\
                            </div>\
                            <div class="mxlayer-main-header">\
                                <h1></h1>\
                                <a href="javascript:;" class="mxlayer-close"></a>\
                            </div>\
                            <div class="mxlayer-main-body"></div>\
                            <div class="mxlayer-main-footer">\
                                <a href="javascript:;" class="mxlayer-confirm-button"></a>\
                            </div>\
                        </div>\
                        <div class="mxlayer-backdrop">\
                        </div>\
                    </div>'
        }
    };

    $.mxlayer = function (x) {
        //参数合并
        x = $.extend({
            title: 'Title',
            sidebar: false,
            main: false,
            button: 'Confirm',
            width: 0,
            height: 0,
            cancel: function(){},
            confirm: function(){}
        }, x);

        return new MXLayer(x)
    };
})(jQuery);
