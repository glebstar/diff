$(document).ready(function(){
    $('#content .diff').hover(
        function(event){
            change($(this));
        },
        function(event){
            change($(this));
        }
    );
});

function change(obj) {
    var oldData = obj.attr('data-old');
    var tmp = obj.html();
    obj.attr('data-old', tmp);
    obj.html(oldData);
}