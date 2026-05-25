jQuery(function ($) {
  $("button[name='startQuiz']").on("click", function () {
    $(".wpProQuiz_content").data("wpProQuizFront").methode.startQuiz();
  });
});
