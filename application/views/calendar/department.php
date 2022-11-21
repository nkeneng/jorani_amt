<?php
/**
 * This view displays the leave requests of the collaborators of the connected user (if any).
 * @copyright  Copyright (c) 2014-2019 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */
?>

<div class="row-fluid">
    <div class="span12">

        <h2><?php echo lang('calendar_department_title'); ?> &nbsp;<?php echo $help; ?></h2>

        <?php echo lang('calendar_department_description'); ?>

        <h3><?php echo $department; ?></h3>

        <div class="row-fluid">
            <div class="span3"><span style="border: 3px dashed #858888">🤔 <?php echo lang('Planned'); ?></span></div>
            <div class="span3"><span style="border: 3px solid #c48531">'🙏 <?php echo lang('Requested'); ?></span></div>
            <div class="span3"><span style="border: 3px solid #21482d">✅ <?php echo lang('Accepted'); ?></span></div>
            <div class="span3">&nbsp;</div>
        </div>

        <div id='calendar'></div>

    </div>
</div>

<div class="modal hide" id="frmModalAjaxWait" data-backdrop="static" data-keyboard="false">
    <div class="modal-header">
        <h1><?php echo lang('global_msg_wait'); ?></h1>
    </div>
    <div class="modal-body">
        <img src="<?php echo base_url(); ?>assets/images/loading.gif" align="middle">
    </div>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/consistent_color_generation/hsluv-0.1.0.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/consistent_color_generation/sha1.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/consistent_color_generation/consistent_color_generation.js"></script>

<link href="<?php echo base_url(); ?>assets/fullcalendar-2.8.0/fullcalendar.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo base_url(); ?>assets/fullcalendar-2.8.0/fullcalendar.min.js"></script>
<?php if ($language_code == "en") $language_code = "en-GB" ?>
<?php if ($language_code != 'en') { ?>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/fullcalendar-2.8.0/lang/<?php echo strtolower($language_code); ?>.js"></script>
<?php } ?>
<script src="<?php echo base_url(); ?>assets/js/bootbox.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {

        //Global Ajax error handling mainly used for session expiration
        $(document).ajaxError(function (event, jqXHR, settings, errorThrown) {
            $('#frmModalAjaxWait').modal('hide');
        if (jqXHR.status == 401) {
            bootbox.alert("<?php echo lang('global_ajax_timeout');?>", function () {
                //After the login page, we'll be redirected to the current page
                location.reload();
            });
        } else { //Oups
            bootbox.alert("<?php echo lang('global_ajax_error');?>");
        }
      });

    //Create a calendar and fill it with AJAX events
    $('#calendar').fullCalendar({
        timeFormat: ' ', /*Trick to remove the start time of the event*/
        header: {
            left: "prev,next today",
            center: "title",
            right: ""
        },
        events: '<?php echo base_url();?>leaves/department',
        loading: function (isLoading) {
            if (isLoading) { //Display/Hide a pop-up showing an animated icon during the Ajax query.
                $('#frmModalAjaxWait').modal('show');
            } else {
                $('#frmModalAjaxWait').modal('hide');
            }
        },
        eventRender: function (event, element, view) {
            if (event.imageurl) {
                $(element).find('span:first').prepend('<img src="' + event.imageurl + '" />');
            }
        },
        eventAfterRender: function (event, element, view) {
            //Add tooltip to the element
            $(element).attr('title', event.cause);

            if (typeof event.email === 'string' || event.email instanceof String) {
                $(element).css('background-color', colourize(event.email));
            }

            if (typeof event.status === 'string' || event.status instanceof String) {
                var colour = {
                    '1': /* Planned */ 'dashed #858888' /* grey */,
                    '2': /* Requested */ 'solid #c48531' /* orange? */,
                    '3': /* Accepted */ 'solid #21482d' /* darkgreen */
                }[event.status];
                var emoji = {
                    '1': /* Planned */ '🤔' /* grey */,
                    '2': /* Requested */ '🙏' /* orange? */,
                    '3': /* Accepted */ '✅' /* darkgreen */
                }[event.status];
                if (colour !== undefined) {
                    $(element).css('border', '3px ' + colour).find("span").first().append(emoji);
                }
            }

            if (event.enddatetype == "Morning" || event.startdatetype == "Afternoon") {
                var nb_days = event.end !== null ? event.end.diff(event.start, "days") : null;
                var duration = 0.5;
                var halfday_length = 0;
                var length = 0;
                var width = parseInt(jQuery(element).css('width'));
                if (nb_days > 0) {
                    if (event.enddatetype == "Afternoon") {
                        duration = nb_days + 0.5;
                    } else {
                        duration = nb_days;
                    }
                    nb_days++;
                    halfday_length = Math.round((width / nb_days) / 2);
                    if (event.startdatetype == "Afternoon" && event.enddatetype == "Morning") {
                        length = width - (halfday_length * 2);
                    } else {
                        length = width - halfday_length;
                    }
                } else {
                    halfday_length = Math.round(width / 2);   //Average width of a day divided by 2
                    length = halfday_length;
                }
            }
            $(element).css('width', length + "px");

            //Starting afternoon : shift the position of event to the right
            if (event.startdatetype == "Afternoon") {
                $(element).css('margin-left', halfday_length + "px");
            }
        },
        windowResize: function(view) {
            $('#calendar').fullCalendar( 'rerenderEvents' );
        }
    });
});
</script>

