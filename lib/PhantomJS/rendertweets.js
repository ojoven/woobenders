// Example for generating video from frames
// avconv -r 60 -f image2 -s 1920x1080 -i frames/gzaas%03d.png -vcodec libx264 -crf 15 out.mp4
var start = new Date().getTime();
var page = require('webpage').create();

page.onResourceError = function(resourceError) {
    page.reason = resourceError.errorString;
    page.reason_url = resourceError.url;
};

//page.viewportSize = { width: 600, height: 400 };

var url = "http://twitter.com/escupotwits/status/574685217866170368";

page.open(url, function (status) {
    if (status !== 'success') {
        console.log(
            "Error opening url \"" + page.reason_url
            + "\": " + page.reason
        );
        phantom.exit(1);
    } else {
        window.setTimeout(function () {

            var bb = page.evaluate(function () {
                return document.getElementsByClassName("permalink-tweet")[0].getBoundingClientRect();
            });

            page.clipRect = {
                top:    bb.top,
                left:   bb.left,
                width:  bb.width,
                height: bb.height
            };

            page.render('tweet.png', { format: "png" }); // Phantom creates the images much faster in jpg but avconv creates corrupted video if JPG inputs
            phantom.exit();

        }, 200);
    }
});