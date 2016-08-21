/**
 * @name MXLoader.js
 * @version 0.0.1
 * @create 2015-09-16
 * @lastmodified 2015-09-16 17:36
 * @description jQuery loader plugin
 * @author MuFeng (http://mufeng.me)
 * @url http://mufeng.me
 **/
;(function ($) {
    var MXLoader = function (element) {
        this.parent = element ? $(element) : $('body');
        this.element = null;
        this.timer = null;

        this.init();
    };

    MXLoader.prototype = {
        init: function () {
            this.element = $('<div class="mxloader-container"></div>');
            this.parent.append(this.element);

            return this;
        },

        show: function(type, message, after){//console.log('2.', after);
            clearTimeout(this.timer);
            this.timer = null;
            this.element.empty().show();

            var $container = this.element;

            if(type=='progress'){
                $container.append('<div class="mxloader-body"><div class="mxloader-icon-loader"><div class="cycle"><div class="cycle-icon"></div></div></div><div class="mxloader-text">'+message+'</div></div>')
                $container.show();
                after && after.call(this);
            }else{
                $container.append('<div class="mxloader-body"><div class="mxloader-icon-'+type+'"></div><div class="mxloader-text">'+message+'</div></div>');

                var that = this;
                $container.show();//console.log('3.', after);
                that.timer = setTimeout(function(){//console.log('4.', after);
                    that.dismiss();
                    after && after.call(that);
                }, 600);
            }
        },

        showProgress: function(message){
            this.show('progress', message);
        },

        showSuccess: function(message, after){
            //console.log('1.', after);
            this.show('success', message, after);
        },

        showError: function(message, after){
            this.show('error', message, after);
        },

        showInfo: function(message, after){
            this.show('info', message, after);
        },

        dismiss: function () {
            var that = this;

            clearTimeout(that.timer);
            that.timer = null;
            that.element.hide().empty();
        }
    };

    $.mxloader = function (element) {
        return new MXLoader(element);
    };
})(jQuery);
