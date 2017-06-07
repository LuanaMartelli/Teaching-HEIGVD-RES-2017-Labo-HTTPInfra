
$(function() {
  console.log("Your new id is loading...");

  function getNewId() {
    $.getJSON("/api/students/", function( identity ) {
    console.log(identity);
    var name = identity.firstName + " " + identity.lastName;
    $(".intro-text").text(name);
    $(".intro-text2").text(identity.email);
    $(".intro-text3").text(identity.passphrase);
  });

  };

  getNewId();
});


