webpackJsonp(["web/course-chapter-manage/index"],[function(e,t){"use strict";$("#course-chapter-btn").on("click",function(){var e=$(this),t=$("#course-chapter-form");t.validate({rules:{title:"required"},ajax:!0,currentDom:e,submitSuccess:function(t){e.closest(".modal").modal("hide"),$("#sortable-list").append(t)}})})}]);