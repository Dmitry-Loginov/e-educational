$(function() {
    var widthVideoContainer = $(".lesson-text")[3].clientWidth;
    $('iframe').css('width', widthVideoContainer-20+'px');
    window.addEventListener('resize', event => {
        var widthVideoContainer = $(".lesson-text")[3].clientWidth;
        $('iframe').css('width', widthVideoContainer-20+'px');
      }, false);
});

function preview() {
  frame.src = URL.createObjectURL(event.target.files[0]);
}
function clearImage() {
  frame.src = "";
}