jQuery(document).ready(function ($) {

    var waasheroCheckStatusInterval = null;
    var pendingBackups = $(".waashero_pending_task");

    if (pendingBackups.length > 0) {

       
        waasheroCheckStatusInterval = setInterval(checkTasks, 30000);
    }


    function checkTasks() {

        var array = [];
        $(".waashero_pending_task").each(function () {
            array.push($(this).data("task-id"));
        });


        if (array.length == 0) {
            clearInterval(waasheroCheckStatusInterval);
            return;
        }

        var waashero_task_id = array[array.length - 1];

        var data = {
            'action': 'waashero_get_task_status',
            'waashero_task_id': waashero_task_id
        };


        $.ajax({
            type: 'POST',
            url: ajaxurl,
            async: false,
            data: data,
            success: function (response) {
                if (response.success) {

                    if (response.task.end_date != null) {

                        if (response.task.success) {
                            $('.waashero_pending_task[data-task-id="' + waashero_task_id + '"]').replaceWith('<span>Finished</span>');
                        } else {
                            $('.waashero_pending_task[data-task-id="' + waashero_task_id + '"]').replaceWith('<span>Failed</span>');
                        }


                    }

                }
            },
            error: function (request, status, error) {
                clearInterval(waasheroCheckStatusInterval);
            }
        });


       
    }

    $(document).on("click", "#waashero_clear_cdn_cache", function () {


        $(this).replaceWith("<img src='/wp-admin/images/loading.gif'>");

        var data = {
            'action': 'waashero_cdn_invalidation',
        };

        jQuery.post(ajaxurl, data, function (response) {
           
             if (response.success) {
                 location.reload();
             } 

        });
    });

  
});