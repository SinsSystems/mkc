$(document).ajaxStart(function () {
    $.blockUI();
});
$(document).ajaxStop(function () {
    $.unblockUI();
});

var mm = window.matchMedia("(max-width: 992px)");

var eventObject = {
    id: 0,
    subId: 0,
    date: [],
    time: [],
    ocupiedSeats: [],
    ocupiedRows: [],
    token: 0,
    place: 0,
    place_name: '',
    price: 0,
    name: '',
    director: '',
    description: '',
    subVal: [],
    image: false
}

var tickets = {
    selectedSeats: [],
    nr: 0,
    price: 0
}

var ocpArr = {};

function getEventInfo() {
    var eventInfo = $.ajax({
        type: "GET",
        url: "event.php",
        data: {
            id: id,
            token: token
        }
    });

    $.when(eventInfo).done(function (data) {

        var d = JSON.parse(data);
        if (d.res === true) {
            eventObject.id = d.id;
            eventObject.token = d.token;
            eventObject.name = d.name;
            eventObject.price = d.price;
            eventObject.director = d.info1;
            eventObject.place = d.id_room;
            eventObject.place_name = d.name_room;

            $('#eventName').text(eventObject.name);
            $('#eventDirector').text(eventObject.director);
            $('.eventPlace').text(eventObject.place_name);
            $('#eventPrice').text(eventObject.price);

            if (eventObject.place == 1) {
                $('#blockMileniumSelect').show();
                $('#mainAreaSelect').hide();
                $('.seatChartPreviewImage').attr('src', './images/skica_m.png');
            } else if (eventObject.place == 2) {
                $('#mainAreaSelect').show();
                $('#blockMileniumSelect').hide();
                $('.seatChartPreviewImage').attr('src', './images/skica.png');
            } else if (eventObject.place == 3) {
                $('#mainAreaSelect').hide();
                $('#blockMileniumSelect').hide();
                $('.trigerCollapse').hide();
                $('.trigerModal').hide();
                $('#noAreaSelect').show();
                $('.card-body').removeClass('byeTicketsCard');
            }

            getEventSubInfo();

        } else {
            $('.errorContainer').show();
        }

    }).fail(function (data) {
        $('.errorContainer').show();
    });
}

function getEventSubInfo() {
    var subInfo = $.ajax({
        type: "GET",
        url: "event_sub_dates.php",
        data: {
            id: eventObject.id,
            token: eventObject.token
        }
    });

    $.when(subInfo).done(function (data) {
        var d = JSON.parse(data);
        if (d.res === true || d.res === null) {

            eventObject.token = d.token;
            eventObject.subVal = d.event_sub;
            eventObject.subId = d.event_sub[0].sub_id;

            var existingdates = [];

            eventObject.subVal.forEach(element => {
                $('#eventDates option').each(function () {
                    existingdates.push(this.value);
                });

                var elementDate = element.date.split('-');
                elementDate = elementDate[2] + '.' + elementDate[1] + '.' + elementDate[0];

                if (existingdates.indexOf(element.date) == -1) {
                    $('<option data-id="' + element.sub_id + '" value="' + element.date + '">' + elementDate + '</option>').appendTo('#eventDates');
                }

                if (element.date == $('#eventDates option:first').val()) {
                    $('<option data-id="' + element.sub_id + '">' + element.time.slice(0, 5) + '</option>').appendTo('#eventTime');
                }
            });

            $('.mainContentContainer').show();

            getOcupiedSeats(eventObject.subId);
        } else {
            $('.errorContainer').show();
        }
    }).fail(function (data) {
        $('.errorContainer').show();
    });
}

function getOcupiedSeats(subid, s) {
    var getOs = $.ajax({
        type: "GET",
        url: "event_seats.php",
        data: {
            id: subid,
            token: eventObject.token
        }
    });

    $.when(getOs).done(function (data) {
        var data = JSON.parse(data);
        if (data.res === true) {
            for (var index = 0; index < data.seats.length; index++) {
                eventObject.ocupiedSeats.push(subid + "_" + data.seats[index].level_01 + "_" + data.seats[index].level_02 + "_" + data.seats[index].level_03 + "_" + data.seats[index].level_04);
            }
            eventObject.token = data.token;

            $('.seatChartsContainer').fadeIn(500);
            if (s == 's')
                $('.seatSelect').fadeIn(500);

            getOcupiedRowsBlocksArea();
        }
    });

}

function getSelectedSeats() {
    tickets.selectedSeats.forEach(e => {
        $('label#' + e).removeClass('btn-info').addClass('btn-success');
        $('option#seat_' + e).hide();
    });
}

function payRequest(s) {
    var forPay, name, tel, mail, mail2, formId;
    if (s === 'L') {
        formId = 'payFormLarge';
        forPay = tickets.price;
        name = $('#imeForma1').val();
        tel = $('#telefonForma1').val();
        mail = $('#emailForma1').val();
        mail2 = $('#emailForma21').val();
    } else if (s === 'S') {
        formId = 'payFormSmall';
        forPay = tickets.price;
        name = $('#imeForma').val();
        tel = $('#telefonForma').val();
        mail = $('#emailForma').val();
        mail2 = $('#emailForma2').val();
    }

    if (name.trim() !== '' && tel.trim() !== '' && mail.trim() !== '' && mail2.trim() !== '') {

        var data = JSON.stringify({
            token: eventObject.token,
            ticket_number: tickets.nr,
            price: forPay,
            name: name,
            telephon: tel,
            email: mail,
            emailCheck: mail2
        })

        $.ajax({
            type: "post",
            url: "process_transaction1.php",
            data: data,
            success: function (data) {
                if (!data) {
                    $('#selectErrorDialog').modal('show');
                    $('#selectErrorDialog').on('hidden.bs.modal', function () {
                        location.reload();
                    });
                } else {
                    var data = JSON.parse(data);

                    $('input[name="oid"]').val(data.order_id);
                    $('input[name="clientid"]').val(data.clientid);
                    $('input[name="amount"]').val(data.amount);
                    // $('#okUrl').val(data.okurl);
                    // $('#failUrl').val(data.failurl);
                    $('input[name="currency"]').val(data.currencyval);
                    $('input[name="storekey"]').val(data.storekey);
                    $('input[name="lang"]').val(data.lang);
                    $('input[name="trantype"]').val(data.transactiontype);
                    $('input[name="rnd"]').val(data.rnd);
                    $('input[name="hash"]').val(data.hash);

                    if (data.res === true) {
                        $('#' + formId).submit();
                        $('#largescreenShoppingModal').modal('hide');
                    } else if (data.res === false) {
                        $('#largescreenShoppingModal').modal('hide');
                        setTimeout(() => {
                            $('#selectErrorDialog').modal('show');
                        }, 400);
                    }
                }
            },
            error: function () {
                $('#largescreenShoppingModal').modal('hide');
                setTimeout(() => {
                    $('#selectErrorDialog').modal('show');
                }, 400);
            }
        })
    }
}

$(document).ready(function () {
    $(function () {
        $('[data-toggle="popover"]').popover({
            container: 'body'
        })
    })
});

function getOcupiedRowsBlocksArea() {
    // var ocpArr = [];
    // eventObject.ocupiedSeats.forEach(e => {
    //     var r = e.split('_');
    //     r = 'row_' + r[0] + '_' + r[1] + '_' + r[2] + '_' + r[3];

    //     if(r in ocpArr){
    //         ocpArr[r] += 1;
    //     } else {
    //         ocpArr[r] = 1;
    //     }
    // });


    var ba1 = 0, ba2 = 0, ba3 = 0,
        bb1 = 0, bb2 = 0, bb3 = 0,
        bv1 = 0, bv2 = 0, bv3 = 0,
        pa1 = 0, pa2 = 0, pa3 = 0, pa4 = 0, pa5 = 0, pa6 = 0,
        pb1 = 0, pb2 = 0, pb3 = 0, pb4 = 0, pb5 = 0, pb6 = 0, pb7 = 0,
        pv1 = 0, pv2 = 0, pv3 = 0, pv4 = 0, pv5 = 0, pv6 = 0
        ma1 = 0, ma2 = 0, ma3 = 0, ma4 = 0, ma5 = 0, ma6 = 0, ma7 = 0, ma8 = 0, ma9 = 0, ma10 = 0,
        mb1 = 0, mb2 = 0, mb3 = 0, mb4 = 0, mb5 = 0, mb6 = 0, mb7 = 0, mb8 = 0, mb9 = 0, mb10 = 0,
        mc1 = 0, mc2 = 0, mc3 = 0, mc4 = 0, mc5 = 0, mc6 = 0, mc7 = 0, mc8 = 0, mc9 = 0, mc10 = 0,
        md1 = 0, md2 = 0, md3 = 0, md4 = 0, md5 = 0, md6 = 0, md7 = 0, md8 = 0, md9 = 0, md10 = 0, md11 = 0, md12 = 0,
        me1 = 0, me2 = 0, me3 = 0, me4 = 0, me5 = 0, me6 = 0, me7 = 0, me8 = 0, me9 = 0, me10 = 0, me11 = 0;

    eventObject.ocupiedSeats.forEach(e => {
        var r = e.split('_');
        r = r[0] + '_' + r[1] + '_' + r[2] + '_' + r[3];
        switch (r) {
            case eventObject.subId + '_1_2_1':
                ba1 += 1;
                break;
            case eventObject.subId + '_1_2_2':
                ba2 += 1;
                break;
            case eventObject.subId + '_1_2_3':
                ba3 += 1;
                break;
            case eventObject.subId + '_2_2_1':
                bb1 += 1;
                break;
            case eventObject.subId + '_2_2_2':
                bb2 += 1;
                break;
            case eventObject.subId + '_2_2_3':
                bb3 += 1;
                break;
            case eventObject.subId + '_3_2_1':
                bv1 += 1;
                break;
            case eventObject.subId + '_3_2_2':
                bv2 += 1;
                break;
            case eventObject.subId + '_3_2_3':
                bv3 += 1;
                break;
            case eventObject.subId + '_1_1_1':
                pa1 += 1;
                break;
            case eventObject.subId + '_1_1_2':
                pa2 += 1;
                break;
            case eventObject.subId + '_1_1_3':
                pa3 += 1;
                break;
            case eventObject.subId + '_1_1_4':
                pa4 += 1;
                break;
            case eventObject.subId + '_1_1_5':
                pa5 += 1;
                break;
            case eventObject.subId + '_1_1_6':
                pa6 += 1;
                break;
            case eventObject.subId + '_2_1_1':
                pb1 += 1;
                break;
            case eventObject.subId + '_2_1_2':
                pb2 += 1;
                break;
            case eventObject.subId + '_2_1_3':
                pb3 += 1;
                break;
            case eventObject.subId + '_2_1_4':
                pb4 += 1;
                break;
            case eventObject.subId + '_2_1_5':
                pb5 += 1;
                break;
            case eventObject.subId + '_2_1_6':
                pb6 += 1;
                break;
            case eventObject.subId + '_2_1_7':
                pb7 += 1;
                break;
            case eventObject.subId + '_3_1_1':
                pv1 += 1;
                break;
            case eventObject.subId + '_3_1_2':
                pv2 += 1;
                break;
            case eventObject.subId + '_3_1_3':
                pv3 += 1;
                break;
            case eventObject.subId + '_3_1_4':
                pv4 += 1;
                break;
            case eventObject.subId + '_3_1_5':
                pv5 += 1;
                break;
            case eventObject.subId + '_3_1_6':
                pv6 += 1;
                break;

            case eventObject.subId + '_1_0_1':
                ma1 += 1;
                break;
            case eventObject.subId + '_1_0_2':
                ma2 += 1;
                break;
            case eventObject.subId + '_1_0_3':
                ma3 += 1;
                break;
            case eventObject.subId + '_1_0_4':
                ma4 += 1;
                break;
            case eventObject.subId + '_1_0_5':
                ma5 += 1;
                break;
            case eventObject.subId + '_1_0_6':
                ma6 += 1;
                break;
            case eventObject.subId + '_1_0_7':
                ma7 += 1;
                break;
            case eventObject.subId + '_1_0_8':
                ma8 += 1;
                break;
            case eventObject.subId + '_1_0_9':
                ma9 += 1;
                break;
            case eventObject.subId + '_1_0_10':
                ma10 += 1;
                break;

            case eventObject.subId + '_2_0_1':
                mb1 += 1;
                break;
            case eventObject.subId + '_2_0_2':
                mb2 += 1;
                break;
            case eventObject.subId + '_2_0_3':
                mb3 += 1;
                break;
            case eventObject.subId + '_2_0_4':
                mb4 += 1;
                break;
            case eventObject.subId + '_2_0_5':
                mb5 += 1;
                break;
            case eventObject.subId + '_2_0_6':
                mb6 += 1;
                break;
            case eventObject.subId + '_2_0_7':
                mb7 += 1;
                break;
            case eventObject.subId + '_2_0_8':
                mb8 += 1;
                break;
            case eventObject.subId + '_2_0_9':
                mb9 += 1;
                break;
            case eventObject.subId + '_2_0_10':
                mb10 += 1;
                break;

            case eventObject.subId + '_3_0_1':
                mc1 += 1;
                break;
            case eventObject.subId + '_3_0_2':
                mc2 += 1;
                break;
            case eventObject.subId + '_3_0_3':
                mc3 += 1;
                break;
            case eventObject.subId + '_3_0_4':
                mc4 += 1;
                break;
            case eventObject.subId + '_3_0_5':
                mc5 += 1;
                break;
            case eventObject.subId + '_3_0_6':
                mc6 += 1;
                break;
            case eventObject.subId + '_3_0_7':
                mc7 += 1;
                break;
            case eventObject.subId + '_3_0_8':
                mc8 += 1;
                break;
            case eventObject.subId + '_3_0_9':
                mc9 += 1;
                break;
            case eventObject.subId + '_3_0_10':
                mc10 += 1;
                break;

            case eventObject.subId + '_4_0_11':
                md1 += 1;
                break;
            case eventObject.subId + '_4_0_12':
                md2 += 1;
                break;
            case eventObject.subId + '_4_0_13':
                md3 += 1;
                break;
            case eventObject.subId + '_4_0_14':
                md4 += 1;
                break;
            case eventObject.subId + '_4_0_15':
                md5 += 1;
                break;
            case eventObject.subId + '_4_0_16':
                md6 += 1;
                break;
            case eventObject.subId + '_4_0_17':
                md7 += 1;
                break;
            case eventObject.subId + '_4_0_18':
                md8 += 1;
                break;
            case eventObject.subId + '_4_0_19':
                md9 += 1;
                break;
            case eventObject.subId + '_4_0_20':
                md10 += 1;
                break;
            case eventObject.subId + '_4_0_21':
                md10 += 1;
                break;
            case eventObject.subId + '_4_0_22':
                md10 += 1;
                break;

            case eventObject.subId + '_5_0_11':
                me1 += 1;
                break;
            case eventObject.subId + '_5_0_12':
                me2 += 1;
                break;
            case eventObject.subId + '_5_0_13':
                me3 += 1;
                break;
            case eventObject.subId + '_5_0_14':
                me4 += 1;
                break;
            case eventObject.subId + '_5_0_15':
                me5 += 1;
                break;
            case eventObject.subId + '_5_0_16':
                me6 += 1;
                break;
            case eventObject.subId + '_5_0_17':
                me7 += 1;
                break;
            case eventObject.subId + '_5_0_18':
                me8 += 1;
                break;
            case eventObject.subId + '_5_0_19':
                me9 += 1;
                break;
            case eventObject.subId + '_5_0_20':
                me10 += 1;
                break;
            case eventObject.subId + '_5_0_21':
                me10 += 1;
                break;

            default:
                break;
        }
    });

    if (ba1 + ba2 + ba3 == 24) {
        $('#option1[data-id="area_1"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (bb1 + bb2 + bb3 == 46) {
        $('#option2[data-id="area_1"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (bv1 + bv2 + bv3 == 24) {
        $('#option3[data-id="area_1"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (pa1 + pa2 + pa3 + pa4 + pa5 + pa6 == 32) {
        $('#option1[data-id="area_0"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (pb1 + pb2 + pb3 + pb4 + pb5 + pb6 + pb7 == 105) {
        $('#option2[data-id="area_0"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (pv1 + pv2 + pv3 + pv4 + pv5 + pv6 == 32) {
        $('#option3[data-id="area_0"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }

    if (ba1 + ba2 + ba3 + bb1 + bb2 + bb3 + bv1 + bv2 + bv3 == 94) {
        $('#balkonBB').removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (pa1 + pa2 + pa3 + pa4 + pa5 + pa6 + pb1 + pb2 + pb3 + pb4 + pb5 + pb6 + pb7 + pv1 + pv2 + pv3 + pv4 + pv5 + pv6 == 169) {
        $('#parterBB').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }

    if (ma1 + ma2 + ma3 + ma4 + ma5 + ma6 + ma7 + ma8 + ma9 + ma10 >= 74) {
        $('#option1[name="optionsAreaM"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (mb1 + mb2 + mb3 + mb4 + mb5 + mb6 + mb7 + mb8 + mb9 + mb10 >= 80) {
        $('#option2[name="optionsAreaM"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (mc1 + mc2 + mc3 + mc4 + mc5 + mc6 + mc7 + mc8 + mc9 + mc10 >= 74) {
        $('#option3[name="optionsAreaM"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (md1 + md2 + md3 + md4 + md5 + md6 + md7 + md8 + md9 + md10 + md11 + md12 >= 144) {
        $('#option4[name="optionsAreaM"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }
    if (me1 + me2 + me3 + me4 + me5 + me6 + me7 + me8 + me9 + me10 + me11 >= 138) {
        $('#option5[name="optionsAreaM"]').parent().removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
    }


    if (ba1 == 8) {
        $('#row_' + eventObject.subId + '_1_2_1').hide();
    }
    if (ba2 == 8) {
        $('#row_' + eventObject.subId + '_1_2_2').hide();
    }
    if (ba3 == 8) {
        $('#row_' + eventObject.subId + '_1_2_3').hide();
    }

    if (bb1 == 18) {
        $('#row_' + eventObject.subId + '_2_2_1').hide();
    }
    if (bb2 == 20) {
        $('#row_' + eventObject.subId + '_2_2_2').hide();
    }
    if (bb3 == 8) {
        $('#row_' + eventObject.subId + '_2_2_3').hide();
    }


    if (bv1 == 8) {
        $('#row_' + eventObject.subId + '_3_2_1').hide();
    }
    if (bv2 == 8) {
        $('#row_' + eventObject.subId + '_3_2_2').hide();
    }
    if (bv3 == 8) {
        $('#row_' + eventObject.subId + '_3_2_3').hide();
    }

    if (pa1 == 3) {
        $('#row_' + eventObject.subId + '_1_1_1').hide();
    }
    if (pa2 == 4) {
        $('#row_' + eventObject.subId + '_1_1_2').hide();
    }
    if (pa3 == 5) {
        $('#row_' + eventObject.subId + '_1_1_3').hide();
    }
    if (pa4 == 6) {
        $('#row_' + eventObject.subId + '_1_1_4').hide();
    }
    if (pa5 == 7) {
        $('#row_' + eventObject.subId + '_1_1_5').hide();
    }
    if (pa6 == 7) {
        $('#row_' + eventObject.subId + '_1_1_6').hide();
    }

    if (pb1 == 9) {
        $('#row_' + eventObject.subId + '_2_1_1').hide();
    }
    if (pb2 == 11) {
        $('#row_' + eventObject.subId + '_2_1_2').hide();
    }
    if (pb3 == 13) {
        $('#row_' + eventObject.subId + '_2_1_3').hide();
    }
    if (pb4 == 15) {
        $('#row_' + eventObject.subId + '_2_1_4').hide();
    }
    if (pb5 == 17) {
        $('#row_' + eventObject.subId + '_2_1_5').hide();
    }
    if (pb6 == 19) {
        $('#row_' + eventObject.subId + '_2_1_6').hide();
    }
    if (pb7 == 21) {
        $('#row_' + eventObject.subId + '_2_1_7').hide();
    }


    if (pv1 == 3) {
        $('#row_' + eventObject.subId + '_3_1_1').hide();
    }
    if (pv2 == 4) {
        $('#row_' + eventObject.subId + '_3_1_2').hide();
    }
    if (pv3 == 5) {
        $('#row_' + eventObject.subId + '_3_1_3').hide();
    }
    if (pv4 == 6) {
        $('#row_' + eventObject.subId + '_3_1_4').hide();
    }
    if (pv5 == 7) {
        $('#row_' + eventObject.subId + '_3_1_5').hide();
    }
    if (pv6 == 7) {
        $('#row_' + eventObject.subId + '_3_1_6').hide();
    }

    if (ma1 >= 5) {
        $('#row_' + eventObject.subId + '_1_0_1').hide();
    }
    if (ma2 >= 6) {
        $('#row_' + eventObject.subId + '_1_0_2').hide();
    }
    if (ma3 >= 7) {
        $('#row_' + eventObject.subId + '_1_0_3').hide();
    }
    if (ma4 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_4').hide();
    }
    if (ma5 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_5').hide();
    }
    if (ma6 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_6').hide();
    }
    if (ma7 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_7').hide();
    }
    if (ma8 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_8').hide();
    }
    if (ma9 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_9').hide();
    }
    if (ma10 >= 8) {
        $('#row_' + eventObject.subId + '_1_0_10').hide();
    }

    if (mb1 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_1').hide();
    }
    if (mb2 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_2').hide();
    }
    if (mb3 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_3').hide();
    }
    if (mb4 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_4').hide();
    }
    if (mb5 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_5').hide();
    }
    if (mb6 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_6').hide();
    }
    if (mb7 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_7').hide();
    }
    if (mb8 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_8').hide();
    }
    if (mb9 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_9').hide();
    }
    if (mb10 >= 8) {
        $('#row_' + eventObject.subId + '_2_0_10').hide();
    }

    if (mc1 >= 5) {
        $('#row_' + eventObject.subId + '_3_0_1').hide();
    }
    if (mc2 >= 6) {
        $('#row_' + eventObject.subId + '_3_0_2').hide();
    }
    if (mc3 >= 7) {
        $('#row_' + eventObject.subId + '_3_0_3').hide();
    }
    if (mc4 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_4').hide();
    }
    if (mc5 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_5').hide();
    }
    if (mc6 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_6').hide();
    }
    if (mc7 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_7').hide();
    }
    if (mc8 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_8').hide();
    }
    if (mc9 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_9').hide();
    }
    if (mc10 >= 8) {
        $('#row_' + eventObject.subId + '_3_0_10').hide();
    }

    if (md1 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_11').hide();
    }
    if (md2 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_12').hide();
    }
    if (md3 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_13').hide();
    }
    if (md4 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_14').hide();
    }
    if (md5 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_15').hide();
    }
    if (md6 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_16').hide();
    }
    if (md7 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_17').hide();
    }
    if (md8 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_18').hide();
    }
    if (md9 >= 13) {
        $('#row_' + eventObject.subId + '_4_0_19').hide();
    }
    if (md10 >= 12) {
        $('#row_' + eventObject.subId + '_4_0_20').hide();
    }
    if (md11 >= 12) {
        $('#row_' + eventObject.subId + '_4_0_21').hide();
    }
    if (md12 >= 3) {
        $('#row_' + eventObject.subId + '_4_0_22').hide();
    }

    if (me1 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_11').hide();
    }
    if (me2 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_12').hide();
    }
    if (me3 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_13').hide();
    }
    if (me4 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_14').hide();
    }
    if (me5 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_15').hide();
    }
    if (me6 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_16').hide();
    }
    if (me7 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_17').hide();
    }
    if (me8 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_18').hide();
    }
    if (me9 >= 13) {
        $('#row_' + eventObject.subId + '_5_0_19').hide();
    }
    if (me10 >= 12) {
        $('#row_' + eventObject.subId + '_5_0_20').hide();
    }
    if (me11 >= 9) {
        $('#row_' + eventObject.subId + '_5_0_21').hide();
    }
}

var setMargin = 0;

$(document).on('click', '#goToPayingSection', function () {
    if (mm.matches) {
        $("#selectTicketsCard").hide("slide", { direction: "left" }, 1000);
        $("#payTicketsCard").show("slide", { direction: "right" }, 1000);
        $("#selectTicketsCard").insertBefore($("#payTicketsCard"));
    }
    $('#displayAvSeats').prop("selectedIndex", 0);
    $('.totalTicketNum').text(tickets.nr);
    $('.totalSum').text(tickets.price);

});

$(document).on('click', '#cancelPaying', function () {
    if (mm.matches) {
        setMargin = Math.round($('#payTicketsCard').height() - $('#selectTicketsCard').height());
        $("#payTicketsCard").hide("slide", { direction: "right" }, 1000);
        $("#selectTicketsCard").show("slide", { direction: "left" }, 1000, function () {
            if (setMargin > 0) {
                $('#selectTicketsCard').css('margin-bottom', (15 + setMargin) + 'px');
            }
        });
        $("#payTicketsCard").insertBefore($("#selectTicketsCard"));
    }
});



getEventInfo();