$(document).ready(function() {
    function genPass(len = 32) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var gen = '';
      
        for (let i = 0; i < len; i++) {
          gen += chars.charAt(Math.floor(Math.random() * chars.length));
        }
      
        return gen;
    }
      
    $(".genPass").on("click", function(e) {
        e.preventDefault();
        var outputTarget = $(this).data('output');
        var gen = genPass();
        // console.log("Generated password "+gen+" - outputting to "+outputTarget);
        $(outputTarget).prop("type", "text");
        $(outputTarget).val(genPass());
    });
    
    function copyTC(elementID) {
        let element = document.getElementById(elementID); //select the element
        let elementText = element.textContent; //get the text content from the element
        copyText(elementText); //use the copyText function below
    }
      
    //If you only want to put some Text in the Clipboard just use this function
    // and pass the string to copied as the argument.
    function copyText(text) {
        navigator.clipboard.writeText(text);
        $('#liveToast').toast('show');
    }
      
    function reveal(id) {
        o = "#"+id;
        isVisible = $(o).is(":visible");
        $(o+"-h").toggle();
        $(o).toggle();
        if (!isVisible) {
          $(o+"-eye").css({filter : "invert(100%)"});
        } else {
          $(o+"-eye").css({filter : "invert(0%)"});
        }
    }
});