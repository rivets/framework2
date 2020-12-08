/**  Toast
 * methods:
    * show
 * message
 * options: (as object)
    * edge (default: right)
    * timer (default: 5000)
    * backgroundcolor (default: #00447b)
    * color (default: white)
 * **/
function Toast() {
    this.pos = false;
}
Toast.prototype = {
    show: function (message, options) {
        const that = this;
        options = options ? options : {};
        message = message ? message : "";
        options.edge = options.edge ? options.edge : "right";
        var timer = options.timer ? options.timer : 5000;
        timer = timer < 5000 ? 5001 : timer;
        var backgroundcolor = options.backgroundColor ? options.backgroundColor : "#00447b";
        var color = options.color ? options.color : "white";
        var toast_div = document.createElement('div'),
            c=document.getElementsByClassName("tesodev_toaster");
        toast_div.id = "div_" + Math.random().toString(36).substr(2, 16);
        var text = document.createElement("div");
        text.innerHTML = message;
        text.className = "tesodev_toaster_text";
        text.style.color = color;
        toast_div.appendChild(text);
        toast_div.className = "tesodev_toaster";
        toast_div.style.backgroundColor = backgroundcolor;
        toast_div.style.cursor = "pointer";
        if(options.edge === "left"){
            toast_div.style.left = "30px";
        }
        else if(options.edge === "right"){
            toast_div.style.right = "30px";
        }
        fadeIn(toast_div);
        document.getElementsByTagName('body')[0].appendChild(toast_div);
        if(this.pos){
            c[c.length - 1].style.top = this.pos+"px";
        }
        this.pos = c[c.length-1].offsetHeight + c[c.length-1].offsetTop + 10;
        var interv = setInterval(function(){
            clearInterval(interv);
            fadeOut(toast_div);
        }, timer-5000);
        toast_div.onclick = function (ev) {
            clearInterval(interv);
            var h = toast_div.offsetHeight + 10;
            var top = toast_div.offsetTop;
            toast_div.remove();
            c=document.getElementsByClassName("tesodev_toaster");
            for(var i = 0; i<c.length; i += 1)
            {
                c[i].style.top = top < parseInt(c[i].style.top) ? parseInt(c[i].style.top) - h+"px" : c[i].style.top;
            }
            that.pos=document.getElementsByClassName("tesodev_toaster").length ? that.pos - h : false;
        };
        toast_div.onmouseover = function (ev) {
            toast_div.style.opacity = 0.7;
        };
        toast_div.onmouseout = function (ev) {
            toast_div.style.opacity = 1;
        };
        function fadeOut(el){
            el.style.opacity = 1;
            (function fade() {
                if ((el.style.opacity-= 0.01) < 0) {
                    var h = toast_div.getBoundingClientRect().height;
                    toast_div.remove();
                    c=document.getElementsByClassName("tesodev_toaster");
                    for(var i = 0; i<c.length; i += 1)
                    {
                        c[i].style.top = (parseInt(c[i].getBoundingClientRect().top) - h)+"px";
                    }
                    that.pos=document.getElementsByClassName("tesodev_toaster").length ? that.pos - h : false;
                } else {
                    setTimeout(function(){
                        fade();
                    }, 50);
                }
            })();
        }
        function fadeIn(el, display){
            el.style.opacity = 0;
            el.style.display = display || "block";
            (function fade() {
                var val = parseFloat(el.style.opacity) + 0.1;
                if (val < 1) {
                    el.style.opacity = val;
                    setTimeout(function(){
                        fade();
                    }, 50);
                }
            })();
        }
    }
};
var tesodev_toast = new Toast();