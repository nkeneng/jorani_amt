<?php
/**
 * This view allows an employees (or HR
 * admin/Manager) to create a new leave request
 * @copyright  Copyright (c) 2014-2019 Benjamin
 *     BALET
 * @license      http://opensource.org/licenses/AGPL-3.0
 *     AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */
?>

<h2><?php echo lang('leaves_create_title'); ?>
    &nbsp;<?php echo $help; ?></h2>

<div class="row-fluid">
    <div class="span8">

        <?php echo validation_errors(); ?>

        <?php
        $attributes = array('id' => 'frmLeaveForm');
        echo form_open('leaves/createfreeleave', $attributes) ?>

        <label for="type">
            <?php echo lang('leaves_create_field_type'); ?>
            &nbsp;<span class="muted"
                        id="lblCredit"><?php if (!is_null($credit)) { ?>(<?php echo $credit; ?>)<?php } ?></span>
        </label>
        <select class="input-xxlarge" disabled
                name="type" id="type">
            <?php foreach ($types as $typeId => $TypeName): ?>
                <?php if ($typeId == 9) : ?>
                    <option value="<?php echo $typeId; ?>"
                            selected><?php echo $TypeName; ?></option>
                <?php endif; ?>
            <?php endforeach ?>
        </select>

        <label for="viz_startdate"><?php echo lang('leaves_create_field_start'); ?></label>
        <input type="text" class="input-xxlarge"
               name="viz_startdate"
               id="viz_startdate"
               value="<?php echo set_value('startdate'); ?>"
               autocomplete="off"/>
        <input type="hidden" class="input-xxlarge"
               name="startdate"
               id="startdate"/><br/>

        <label for="viz_enddate"><?php echo lang('leaves_create_field_end'); ?></label>
        <input type="text" class="input-xxlarge"
               name="viz_enddate" id="viz_enddate"
               value="<?php echo set_value('enddate'); ?>"
               autocomplete="off"/>
        <input type="hidden" class="input-xxlarge"
               name="enddate" id="enddate"/><br/>

        <?php if ($this->config->item('disable_edit_leave_duration') == TRUE) { ?>
            <input type="text"
                   style="display:none;"
                   class="input-xxlarge"
                   name="duration" id="duration"
                   value="<?php echo set_value('duration'); ?>"
                   readonly/>
        <?php } else { ?>
            <input type="text"
                   style="display:none;"
                   class="input-xxlarge"
                   name="duration" id="duration"
                   value="<?php echo set_value('duration'); ?>"/>
        <?php } ?>
        <label for="dayFree"><?php echo lang('dayFree'); ?>
            <span id="dayFree"></span></label>
        <select class="input-xxlarge"
                name="dayFree" id="dayFree">
            <option value="monday"><?php echo lang('monday'); ?></option>
            <option value="tuesday"><?php echo lang('tuesday'); ?></option>
            <option value="wednesday"><?php echo lang('wednesday'); ?></option>
            <option value="thursday"><?php echo lang('thursday'); ?></option>
            <option value="friday"><?php echo lang('friday'); ?></option>
        </select>
        <span style="margin-left: 2px;position: relative;top: -5px;"
              id="spnDayType"></span>

        <br/><br/>
        <button name="status" value="1"
                type="submit"
                class="btn btn-primary"><i
                    class="mdi mdi-calendar-question"
                    aria-hidden="true"></i>&nbsp; <?php echo lang('Planned'); ?>
        </button>
        &nbsp;&nbsp;
        <button id="btn-request" type="button"
                class="btn btn-primary "><i
                    class="mdi mdi-check"></i>&nbsp; <?php echo lang('Requested'); ?>
        </button>
        <button name="status"
                id="btn-request-valid"
                style="display: none;" value="2"
                type="submit"
                class="btn btn-primary "><i
                    class="mdi mdi-check"></i>&nbsp; <?php echo lang('Requested'); ?>
        </button>
        <br/><br/>
        <a href="<?php echo base_url(); ?>leaves"
           class="btn btn-danger"><i
                    class="mdi mdi-close"></i>&nbsp; <?php echo lang('leaves_create_button_cancel'); ?>
        </a>
        </form>

    </div>
</div>

<div class="modal hide" id="frmModalAjaxWait"
     data-backdrop="static" data-keyboard="false">
    <div class="modal-header">
        <h1><?php echo lang('global_msg_wait'); ?></h1>
    </div>
    <div class="modal-body">
        <img src="<?php echo base_url(); ?>assets/images/loading.gif"
             align="middle">
    </div>
</div>

<link rel="stylesheet"
      href="<?php echo base_url(); ?>assets/css/flick/jquery-ui.custom.min.css">
<script src="<?php echo base_url(); ?>assets/js/jquery-ui.custom.min.js"></script>
<?php if ($language_code == "en") $language_code = "en-GB" ?>
<?php //Prevent HTTP-404 when localization isn't needed
if ($language_code != 'en') { ?>
    <script src="<?php echo base_url(); ?>assets/js/i18n/jquery.ui.datepicker-<?php echo $language_code; ?>.js"></script>
<?php } ?>
<script src="<?php echo base_url(); ?>assets/js/bootbox.min.js"></script>

<?php require_once dirname(BASEPATH) . "/local/triggers/leave_view.php"; ?>
<script>
    $(document).on("click", "#showNoneWorkedDay", function (e) {
        showListDayOffHTML();
    });
</script>
<script type="text/javascript">
    var baseURL = '<?php echo base_url();?>';
    var userId = <?php echo $user_id; ?>;
    var leaveId = null;
    var languageCode = '<?php echo $language_code;?>';
    var dateJsFormat = '<?php echo lang('global_date_js_format');?>';
    var dateMomentJsFormat = '<?php echo lang('global_date_momentjs_format');?>';

    var noContractMsg = "<?php echo lang('leaves_validate_flash_msg_no_contract');?>";
    var noTwoPeriodsMsg = "<?php echo lang('leaves_validate_flash_msg_overlap_period');?>";

    var overlappingWithDayOff = "<?php echo lang('leaves_flash_msg_overlap_dayoff');?>";
    var listOfDaysOffTitle = "<?php echo lang('leaves_flash_spn_list_days_off');?>";

    $(function () {
        $('#btn-request').on('click', function (event) {
            const week = [1, 0];
            if ($('#startdate').val() && $('#enddate').val()) {
                let startDate = new Date($('#startdate').attr("value"))
                let endDate = new Date($('#enddate').attr("value"))
                if (startDate.getDay() == 1 && endDate.getDay() == 0) {
                    $('#btn-request-valid').click()
                } else {
                    new Toast({
                        message: '<?php echo lang('choose_date_interval');?>',
                        type: 'danger'
                    });
                }
            } else {
                new Toast({
                    message: '<?php echo lang('please_select_date');?>',
                    type: 'danger'
                });
            }
        })
    })

    function validate_form() {
        var fieldname = "";

        //Call custom trigger defined into local/triggers/leave.js
        if (typeof triggerValidateCreateForm == 'function') {
            if (triggerValidateCreateForm() == false) return false;
        }

        if ($('#viz_startdate').val() == "") fieldname = "<?php echo lang('leaves_create_field_start');?>";
        if ($('#viz_enddate').val() == "") fieldname = "<?php echo lang('leaves_create_field_end');?>";
        if ($('#duration').val() == "" || $('#duration').val() == 0) fieldname = "<?php echo lang('leaves_create_field_duration');?>";
        if (fieldname == "") {
            return true;
        } else {
            bootbox.alert(<?php echo lang('leaves_validate_mandatory_js_msg');?>);
            return false;
        }
    }

    //Disallow the use of negative symbols (through a whitelist of symbols)
    function keyAllowed(key) {
        var keys = [8, 9, 13, 16, 17, 18, 19, 20, 27, 46, 48, 49, 50,
            51, 52, 53, 54, 55, 56, 57, 91, 92, 93
        ];
        if (key && keys.indexOf(key) === -1)
            return false;
        else
            return true;
    }

    $(function () {
        //Selectize the leave type combo
        $('#type').select2();

        <?php if ($this->config->item('disallow_requests_without_credit') == TRUE) {?>
        var durationField = document.getElementById("duration");
        durationField.setAttribute("min", "0");
        durationField.addEventListener('keypress', function (e) {
            var key = !isNaN(e.charCode) ? e.charCode : e.keyCode;
            if (!keyAllowed(key))
                e.preventDefault();
        }, false);

        // Disable pasting of non-numbers
        durationField.addEventListener('paste', function (e) {
            var pasteData = e.clipboardData.getData('text/plain');
            if (pasteData.match(/[^0-9]/))
                e.preventDefault();
        }, false);
        <?php }?>
    });

    <?php if ($this->config->item('csrf_protection') == TRUE) {?>
    $(function () {
        $.ajaxSetup({
            data: {
        <?php echo $this->security->get_csrf_token_name();?>:
        "<?php echo $this->security->get_csrf_hash();?>",
    }
    })
        ;
    });
    <?php }?>
</script>
<script type="text/javascript">
    /**
     * This Javascript code is used on the create/edit leave request
     * @copyright  Copyright (c) 2014-2019 Benjamin BALET
     * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
     * @link            https://github.com/bbalet/jorani
     * @since         0.3.0
     */

//Try to calculate the length of the leave
    function getLeaveLength(refreshInfos) {
        refreshInfos = typeof refreshInfos !== 'undefined' ? refreshInfos : true;
        var start = moment($('#startdate').val());
        var end = moment($('#enddate').val());
        var startType = $('#startdatetype option:selected').val();
        var endType = $('#enddatetype option:selected').val();

        if (start.isValid() && end.isValid()) {
            if (start.isSame(end)) {
                if (startType == "Morning" && endType == "Morning") {
                    $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_1d_MM.png' />");
                }
                if (startType == "Afternoon" && endType == "Afternoon") {
                    $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_1d_AA.png' />");
                }
                if (startType == "Morning" && endType == "Afternoon") {
                    $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_1d_MA.png' />");
                }
                if (startType == "Afternoon" && endType == "Morning") {
                    $("#spnDayType").html("<img src='" + baseURL + "assets/images/date_error.png' />");
                }
            } else {
                if (start.isBefore(end)) {
                    if (startType == "Morning" && endType == "Morning") {
                        $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_2d_MM.png' />");
                    }
                    if (startType == "Afternoon" && endType == "Afternoon") {
                        $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_2d_AA.png' />");
                    }
                    if (startType == "Morning" && endType == "Afternoon") {
                        $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_2d_MA.png' />");
                    }
                    if (startType == "Afternoon" && endType == "Morning") {
                        $("#spnDayType").html("<img src='" + baseURL + "assets/images/leave_2d_AM.png' />");
                    }
                }
            }
            if (refreshInfos) getLeaveInfos(false);
        }
    }

    //Get the leave credit, duration and detect overlapping cases (Ajax request)
    //Default behavour is to set the duration field. pass false if you want to disable this behaviour
    function getLeaveInfos(preventDefault) {
        $('#frmModalAjaxWait').modal('show');
        var start = moment($('#startdate').val());
        var end = moment($('#enddate').val());
        $.ajax({
            type: "POST",
            url: baseURL + "leaves/validate",
            data: {
                id: userId,
                type: $("#type option:selected").text(),
                startdate: $('#startdate').val(),
                enddate: $('#enddate').val(),
                startdatetype: $('#startdatetype').val(),
                enddatetype: $('#enddatetype').val(),
                leave_id: leaveId
            }
        })
            .done(function (leaveInfo) {
                if (typeof leaveInfo.length !== 'undefined') {
                    var duration = parseFloat(leaveInfo.length);
                    duration = Math.round(duration * 1000) / 1000;  //Round to 3 decimals only if necessary
                    if (!preventDefault) {
                        if (start.isValid() && end.isValid()) {
                            $('#duration').val(duration);
                        }
                    }
                }
                if (typeof leaveInfo.credit !== 'undefined') {
                    var credit = parseFloat(leaveInfo.credit);
                    var duration = parseFloat($("#duration").val());
                    if (duration > credit) {
                        $("#lblCreditAlert").show();
                    } else {
                        $("#lblCreditAlert").hide();
                    }
                    if (leaveInfo.credit != null) {
                        $("#lblCredit").text('(' + leaveInfo.credit + ')');
                    }
                }
                //Check if the current request overlaps with another one
                showOverlappingMessage(leaveInfo);
                //Or overlaps with a non-working day
                showOverlappingDayOffMessage(leaveInfo);
                //Check if the employee has a contract
                if (leaveInfo.hasContract == false) {
                    bootbox.alert(noContractMsg);
                } else {
                    //If the employee has a contract, check if the current leave request is not on two yearly leave periods
                    var periodStartDate = moment(leaveInfo.PeriodStartDate);
                    var periodEndDate = moment(leaveInfo.PeriodEndDate);
                    if (start.isValid() && end.isValid() && periodEndDate.isValid()) {
                        if (start.isBefore(periodEndDate) && periodEndDate.isBefore(end)) {
                            bootbox.alert(noTwoPeriodsMsg);
                        }
                        if (start.isBefore(periodStartDate)) {
                            bootbox.alert(noTwoPeriodsMsg);
                        }
                    }
                }
                showListDayOff(leaveInfo);
                $('#frmModalAjaxWait').modal('hide');
            });
    }

    //When editing/viewing a leave request, refresh the information about overlapping and days off in the period
    function refreshLeaveInfo() {
        $('#frmModalAjaxWait').modal('show');
        var start = moment($('#startdate').val());
        var end = moment($('#enddate').val());
        $.ajax({
            type: "POST",
            url: baseURL + "leaves/validate",
            data: {
                id: userId,
                type: $("#type option:selected").text(),
                startdate: $('#startdate').val(),
                enddate: $('#enddate').val(),
                startdatetype: $('#startdatetype').val(),
                enddatetype: $('#enddatetype').val(),
                leave_id: leaveId
            }
        })
            .done(function (leaveInfo) {
                showOverlappingMessage(leaveInfo);
                showOverlappingDayOffMessage(leaveInfo);
                showListDayOff(leaveInfo);
                $('#frmModalAjaxWait').modal('hide');
            });
    }

    //Display the list of non-working days occuring between the leave request start and end dates
    function showListDayOff(leaveInfo) {
        if (typeof leaveInfo.listDaysOff !== 'undefined') {
            var arrayLength = leaveInfo.listDaysOff.length;
            if (arrayLength > 0) {
                var htmlTable = "<a href='#divDaysOff' data-toggle='collapse'  class='btn btn-primary input-block-level'>";
                htmlTable += listOfDaysOffTitle.replace("%s", leaveInfo.lengthDaysOff);
                htmlTable += "&nbsp;<i class='icon-chevron-down icon-white'></i></a>\n";
                htmlTable += "<div id='divDaysOff' class='collapse'>";
                htmlTable += "<table class='table table-bordered table-hover table-condensed'>\n";
                htmlTable += "<tbody>";
                for (var i = 0; i < arrayLength; i++) {
                    htmlTable += "<tr><td>";
                    htmlTable += moment(leaveInfo.listDaysOff[i].date, 'YYYY-MM-DD').format(dateMomentJsFormat);
                    htmlTable += " / <b>" + leaveInfo.listDaysOff[i].title + "</b></td>";
                    htmlTable += "<td>" + leaveInfo.listDaysOff[i].length + "</td>";
                    htmlTable += "</tr>\n";
                }
                htmlTable += "</tbody></table></div>";
                $("#spnDaysOffList").html(htmlTable);
                var htmlTooltip = "<a href='#' id='showNoneWorkedDay' data-toggle='tooltip' data-toggle='tooltip' data-placement='right' title='";
                htmlTooltip += listOfDaysOffTitle.replace("%s", leaveInfo.lengthDaysOff);
                htmlTooltip += "'><i class='icon-info-sign'></i></a>";
                $("#tooltipDayOff").html(htmlTooltip);
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });

            } else {
                //NOP
            }
        }
    }

    function showListDayOffHTML() {
        $('#frmModalAjaxWait').modal('show');
        var start = moment($('#startdate').val());
        var end = moment($('#enddate').val());
        $.ajax({
            type: "POST",
            url: baseURL + "leaves/validate",
            data: {
                id: userId,
                type: $("#type option:selected").text(),
                startdate: $('#startdate').val(),
                enddate: $('#enddate').val(),
                startdatetype: $('#startdatetype').val(),
                enddatetype: $('#enddatetype').val(),
                leave_id: leaveId
            }
        })
            .done(function (leaveInfo) {
                $('#frmModalAjaxWait').modal('hide');
                if (typeof leaveInfo.listDaysOff !== 'undefined') {
                    var arrayLength = leaveInfo.listDaysOff.length;
                    if (arrayLength > 0) {
                        var htmlTable = "<div id='divDaysOff2'>";
                        htmlTable += "<table class='table table-bordered table-hover table-condensed'>\n";
                        htmlTable += "<thead class='thead-inverse'>";
                        htmlTable += "<tr><th>";
                        htmlTable += listOfDaysOffTitle.replace("%s", leaveInfo.lengthDaysOff);
                        htmlTable += "</th></tr></thead>";
                        htmlTable += "<tbody>";
                        for (var i = 0; i < arrayLength; i++) {
                            htmlTable += "<tr><td>";
                            htmlTable += moment(leaveInfo.listDaysOff[i].date, 'YYYY-MM-DD').format(dateMomentJsFormat);
                            htmlTable += " / <b>" + leaveInfo.listDaysOff[i].title + "</b></td>";
                            htmlTable += "<td>" + leaveInfo.listDaysOff[i].length + "</td>";
                            htmlTable += "</tr>\n";
                        }
                        htmlTable += "</tbody></table></div>";
                        bootbox.alert(htmlTable, function () {
                            console.log("Alert Callback");
                        });
                    } else {
                        //NOP
                    }
                }
            });
    }

    //Display the list of non-working days occuring between the leave request start and end dates
    function showOverlappingMessage(leaveInfo) {
        if (typeof leaveInfo.overlap !== 'undefined') {
            if (Boolean(leaveInfo.overlap)) {
                $("#lblOverlappingAlert").show();
            } else {
                $("#lblOverlappingAlert").hide();
            }
        }
    }

    //Check if the leave request overlaps with a non-working day
    function showOverlappingDayOffMessage(leaveInfo) {
        if (typeof leaveInfo.overlapDayOff !== 'undefined') {
            if (Boolean(leaveInfo.overlapDayOff)) {
                $("#lblOverlappingDayOffAlert").show();
            } else {
                $("#lblOverlappingDayOffAlert").hide();
            }
        }
    }

    $(function () {
        getLeaveLength(false);

        //Init the start and end date picker and link them (end>=date)
        $("#viz_startdate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: dateJsFormat,
            altFormat: "yy-mm-dd",
            altField: "#startdate",
            beforeShowDay: function (date) {
                var day = date.getDay();
                return [day == 1, ""];
            },
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#viz_enddate").datepicker("option", "minDate", selectedDate);
            }
        }, $.datepicker.regional[languageCode]);
        $("#viz_enddate").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: dateJsFormat,
            altFormat: "yy-mm-dd",
            beforeShowDay: function (date) {
                var day = date.getDay();
                return [day == 0, ""];
            },
            altField: "#enddate",
            numberOfMonths: 1,
            onClose: function (selectedDate) {
                $("#viz_startdate").datepicker("option", "maxDate", selectedDate);
            }
        }, $.datepicker.regional[languageCode]);

        //Force decimal separator whatever the locale is
        $("#days").keyup(function () {
            var value = $("#days").val();
            value = value.replace(",", ".");
            $("#days").val(value);
        });

        $('#viz_startdate').change(function () {
            getLeaveLength(true);
        });
        $('#viz_enddate').change(function () {
            getLeaveLength();
        });
        $('#startdatetype').change(function () {
            getLeaveLength();
        });
        $('#enddatetype').change(function () {
            getLeaveLength();
        });
        $('#type').change(function () {
            getLeaveInfos(false);
        });

        //Check if the user has not exceed the number of entitled days
        $("#duration").keyup(function () {
            getLeaveInfos(true);
        });

        $("#frmLeaveForm").submit(function (e) {
            if (validate_form()) {
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
