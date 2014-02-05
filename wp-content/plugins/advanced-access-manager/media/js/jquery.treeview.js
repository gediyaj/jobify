/*
 * Treeview 1.5pre - jQuery plugin to hide and show branches of a tree
 *
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 * http://docs.jquery.com/Plugins/Treeview
 *
 * Copyright (c) 2007 Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.treeview.js 5759 2008-07-01 07:50:28Z joern.zaefferer $
 *
 */

(function(b){b.extend(b.fn,{swapClass:function(c,a){var b=this.filter("."+c);this.filter("."+a).removeClass(a).addClass(c);b.removeClass(c).addClass(a);return this},replaceClass:function(a,b){return this.filter("."+a).removeClass(a).addClass(b).end()},hoverClass:function(a){a=a||"hover";return this.hover(function(){b(this).addClass(a)},function(){b(this).removeClass(a)})},heightToggle:function(a,b){a?this.animate({height:"toggle"},a,b):this.each(function(){jQuery(this)[jQuery(this).is(":hidden")? "show":"hide"]();b&&b.apply(this,arguments)})},heightHide:function(a,b){a?this.animate({height:"hide"},a,b):(this.hide(),b&&this.each(b))},prepareBranches:function(b){b.prerendered||(this.filter(":last-child:not(ul)").addClass(a.last),this.filter((b.collapsed?"":"."+a.closed)+":not(."+a.open+")").find(">ul").hide());return this.filter(":has(>ul)")},applyClasses:function(c,g){this.filter(":has(>ul):not(:has(>a))").find(">span").unbind("click.treeview").bind("click.treeview",function(a){this==a.target&& g.apply(b(this).next())}).add(b("a",this)).hoverClass();if(!c.prerendered){this.filter(":has(>ul:hidden)").addClass(a.expandable).replaceClass(a.last,a.lastExpandable);this.not(":has(>ul:hidden)").addClass(a.collapsable).replaceClass(a.last,a.lastCollapsable);var d=this.find("div."+a.hitarea);d.length||(d=this.prepend('<div class="'+a.hitarea+'"/>').find("div."+a.hitarea));d.removeClass().addClass(a.hitarea).each(function(){var a="";b.each(b(this).parent().attr("class").split(" "),function(){a+=this+ "-hitarea "});b(this).addClass(a)})}this.find("div."+a.hitarea).click(g)},treeview:function(c){function g(c,e){function h(e){return function(){d.apply(b("div."+a.hitarea,c).filter(function(){return e?b(this).parent("."+e).length:!0}));return!1}}b("a:eq(0)",e).click(h(a.collapsable));b("a:eq(1)",e).click(h(a.expandable));b("a:eq(2)",e).click(h())}function d(){b(this).parent().find(">.hitarea").swapClass(a.collapsableHitarea,a.expandableHitarea).swapClass(a.lastCollapsableHitarea,a.lastExpandableHitarea).end().swapClass(a.collapsable, a.expandable).swapClass(a.lastCollapsable,a.lastExpandable).find(">ul").heightToggle(c.animated,c.toggle);c.unique&&b(this).parent().siblings().find(">.hitarea").replaceClass(a.collapsableHitarea,a.expandableHitarea).replaceClass(a.lastCollapsableHitarea,a.lastExpandableHitarea).end().replaceClass(a.collapsable,a.expandable).replaceClass(a.lastCollapsable,a.lastExpandable).find(">ul").heightHide(c.animated,c.toggle)}function k(){var a=[];i.each(function(c,d){a[c]=b(d).is(":has(>ul:visible)")?1:0}); b.cookie(c.cookieId,a.join(""),c.cookieOptions)}function l(){var a=b.cookie(c.cookieId);if(a){var d=a.split("");i.each(function(a,c){b(c).find(">ul")[parseInt(d[a])?"show":"hide"]()})}}c=b.extend({cookieId:"treeview"},c);if(c.toggle){var m=c.toggle;c.toggle=function(){return m.apply(b(this).parent()[0],arguments)}}this.data("toggler",d);this.addClass("treeview");var i=this.find("li").prepareBranches(c);switch(c.persist){case "cookie":var j=c.toggle;c.toggle=function(){k();j&&j.apply(this,arguments)}; l();break;case "location":var f=this.find("a").filter(function(){return this.href.toLowerCase()==location.href.toLowerCase()});f.length&&(f=f.addClass("selected").parents("ul, li").add(f.next()).show(),c.prerendered&&f.filter("li").swapClass(a.collapsable,a.expandable).swapClass(a.lastCollapsable,a.lastExpandable).find(">.hitarea").swapClass(a.collapsableHitarea,a.expandableHitarea).swapClass(a.lastCollapsableHitarea,a.lastExpandableHitarea))}i.applyClasses(c,d);c.control&&(g(this,c.control),b(c.control).show()); return this}});b.treeview={};var a=b.treeview.classes={open:"open",closed:"closed",expandable:"expandable",expandableHitarea:"expandable-hitarea",lastExpandableHitarea:"lastExpandable-hitarea",collapsable:"collapsable",collapsableHitarea:"collapsable-hitarea",lastCollapsableHitarea:"lastCollapsable-hitarea",lastCollapsable:"lastCollapsable",lastExpandable:"lastExpandable",last:"last",hitarea:"hitarea"}})(jQuery);
        
(function($) {
    var CLASSES = $.treeview.classes;
    var proxied = $.fn.treeview;
    $.fn.treeview = function(settings) {
        settings = $.extend({}, settings);
        if (settings.add) {
            return this.trigger("add", [settings.add]);
        }
        if (settings.remove) {
            return this.trigger("remove", [settings.remove]);
        }
        return proxied.apply(this, arguments).bind("add", function(event, branches) {
            $(branches).prev()
            .removeClass(CLASSES.last)
            .removeClass(CLASSES.lastCollapsable)
            .removeClass(CLASSES.lastExpandable)
            .find(">.hitarea")
            .removeClass(CLASSES.lastCollapsableHitarea)
            .removeClass(CLASSES.lastExpandableHitarea);
            $(branches).find("li").andSelf().prepareBranches(settings).applyClasses(settings, $(this).data("toggler"));
        }).bind("remove", function(event, branches) {
            var prev = $(branches).prev();
            var parent = $(branches).parent();
            $(branches).remove();
            prev.filter(":last-child").addClass(CLASSES.last)
            .filter("." + CLASSES.expandable).replaceClass(CLASSES.last, CLASSES.lastExpandable).end()
            .find(">.hitarea").replaceClass(CLASSES.expandableHitarea, CLASSES.lastExpandableHitarea).end()
            .filter("." + CLASSES.collapsable).replaceClass(CLASSES.last, CLASSES.lastCollapsable).end()
            .find(">.hitarea").replaceClass(CLASSES.collapsableHitarea, CLASSES.lastCollapsableHitarea);
            if (parent.is(":not(:has(>))") && parent[0] != this) {
                parent.parent().removeClass(CLASSES.collapsable).removeClass(CLASSES.expandable)
                parent.siblings(".hitarea").andSelf().remove();
            }
        });
    };
	
})(jQuery);

(function($) {

    function load(settings, root, child, container) {
        function createNode(parent) {
            var current = $("<li/>").attr("id", this.id || "").html("<span>" + this.text + "</span>").appendTo(parent);
            if (this.classes) {
                current.children("span").addClass(this.classes);
            }
            if (this.expanded) {
                current.addClass("open");
            }
            if (this.hasChildren || this.children && this.children.length) {
                var branch = $("<ul/>").appendTo(current);
                if (this.hasChildren) {
                    current.addClass("hasChildren");
                    createNode.call({
                        classes: "placeholder",
                        text: "&nbsp;",
                        children:[]
                    }, branch);
                }
                if (this.children && this.children.length) {
                    $.each(this.children, createNode, [branch])
                }
            }
        }
        $.ajax($.extend(true, {
            url: settings.url,
            dataType: "json",
            data: {
                root: root
            },
            success: function(response) {
                child.empty();
                $.each(response, createNode, [child]);
                $(container).treeview({
                    add: child
                });
            }
        }, settings.ajax));
    }

    var proxied = $.fn.treeview;
    $.fn.treeview = function(settings) {
        if (!settings.url) {
            return proxied.apply(this, arguments);
        }
        var container = this;
        if (!container.children().size())
            load(settings, "source", this, container);
        var userToggle = settings.toggle;
        return proxied.call(this, $.extend({}, settings, {
            collapsed: true,
            toggle: function() {
                var $this = $(this);
                if ($this.hasClass("hasChildren")) {
                    var childList = $this.removeClass("hasChildren").find("ul");
                    load(settings, this.id, childList, container);
                }
                if (userToggle) {
                    userToggle.apply(this, arguments);
                }
            }
        }));
    };

})(jQuery);