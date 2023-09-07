$(function(){
  $('img').click(function(){
    $('body').append("<div id='bg'>").append("<div id='photo'>");
    $('#bg, #photo').hide();
    $('#photo').html('<img>');
    $('#photo img').attr('src', $(this).attr('src')); 


    $('#bg, #photo').fadeIn();
    var height = Number($('#photo img').css('height').slice(0,-2));
    var max_height = Number($(window).height().slice(0,-2));
    // とりあえず縦が画面に収まるようにする
    $('#photo img').css('width', 'auto').css('height', max_height * 0.9 + 'px');

    console.log(height, max_height);
    var width = Number($('#photo img').css('width').slice(0,-2));
    var max_width = Number($('body').css('width').slice(0,-2));
    if (width > max_width){
      $('#photo img').css('width', max_width * 0.9 + 'px').css('height','auto');
    }
    $('#photo').css('width', $('#photo img').css('width')).css('height', $('#photo img').css('height'));

    console.log($('#photo').css('width'), $('#photo').css('height'));

    $('#bg').click(function(){
      $(this).fadeOut(function(){
        $(this).remove();
      });
      $('#photo').fadeOut(function(){
        $(this).remove();
      });
    });
    return false;
  });
});
