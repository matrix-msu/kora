var Kora = Kora || {};
Kora.Auth = Kora.Auth || {};

Kora.Auth.Auth = function() {
  
  function setTempLang(selected_lang){        
    console.log("Language change started: "+langURL);
    $.ajax({
      url:langURL,
      method:'POST',
      data: {
        "_token": CSRFToken,
        "templanguage": selected_lang
      },
      success: function(data){
        console.log(data);
        location.reload();
      }
    });
  }
}