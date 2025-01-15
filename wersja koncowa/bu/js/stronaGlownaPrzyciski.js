$(document).ready(function () {
    $("header nav a").on({
      "mouseover": function () {
        $(this).animate({
          width: "105%",
          backgroundColor: "rgba(255, 0, 0, 0.6)"
        }, 400);
      },
      "mouseout": function () {
        $(this).animate({
          width: "100%",
          backgroundColor: "rgba(0, 0, 0, 0.7)"
        }, 400);
      }
    });
  });