/* ────────────────────────────────────────────────────────────────────────────────────────────── */
/*                                            FUNCTIONS                                           */
/* ────────────────────────────────────────────────────────────────────────────────────────────── */
function displayError(txt) {
  $('#errors').html("<div class='alert alert-danger'>"+txt+"</div>");
}

function genPass(len = 32) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var gen = '';
  
    for (let i = 0; i < len; i++) {
      gen += chars.charAt(Math.floor(Math.random() * chars.length));
    }
  
    return gen;
}
  
function copyTC(elementID) {
  if (location.protocol !== 'https:') {
    displayError("You are not using HTTPS, copying password to clipboard won't work.");
    return false;
  }
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



/* ────────────────────────────────────────────────────────────────────────────────────────────── */
/*                                         EVENT HANDLERS                                         */
/* ────────────────────────────────────────────────────────────────────────────────────────────── */
$(document).ready(function() {
    
    $(".genPass").on("click", function(e) {
        e.preventDefault();
        var data_output = $(this).data('output');
        if (data_output !== undefined) {
          var outputTarget = $(this).parent().find(data_output);
        } else {
          var outputTarget = $(this);
        }
        var gen = genPass();
        console.log("Generated password "+gen);
        $(outputTarget).prop("type", "text");
        $(outputTarget).val(genPass());
    });

    $(".genInput").each(function() {
      const output = $(this).parent();
      const genBtn = '<button type="button" class="btn btn-default genPass">🎲</button>';
      $(this).parent().prepend(genBtn);
    });

    $(".tfa_switch").on("change", function(e) {
      e.preventDefault();
      var tfa      = $(this).prop("checked"); // Use `prop` instead of `checked` to get the checked state
      var dropdown = $(this).parent().find(".tfa_dropdown");
      if (tfa == false) { // Use `false` instead of `"0"` to compare with the boolean value
        dropdown.hide();
      } else {
        dropdown.show();
      }
      console.log("TFA switch changed to "+tfa);
    });

    // Initial state of `tfa_switch` dropdown
    $(".tfa_dropdown").hide();
    $(".tfa_switch").each(function() {
      var tfa      = $(this).prop("checked"); // Use `prop` instead of `checked` to get the checked state
      var dropdown = $(this).parent().find(".tfa_dropdown");
      if (tfa == false) {
        dropdown.hide();
      } else {
        dropdown.show();
      }
    });
    
});