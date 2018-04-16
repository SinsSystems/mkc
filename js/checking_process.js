function checkedArea(a) {
    $('#selectAreaButtonsDiv').empty();
    $('<div class="btn-group btn-group-toggle" data-toggle="buttons">' +
        '<label class="btn btn-info optionsAreaBtn">' +
        '<input type="radio" class="optionsArea" name="optionsArea" id="option1" autocomplete="off"> А' +
        '</label>' +
        '<label class="btn btn-info optionsAreaBtn">' +
        '<input type="radio" class="optionsArea" name="optionsArea" id="option2" autocomplete="off"> Б' +
        '</label>' +
        '<label class="btn btn-info optionsAreaBtn">' +
        '<input type="radio" class="optionsArea" name="optionsArea" id="option3" autocomplete="off"> В' +
        '</label>' +
        '</div>').appendTo('#selectAreaButtonsDiv');

    $('input[name="optionsArea"]').attr('data-id', 'area_' + a);
    getOcupiedRowsBlocksArea();
    $('#blockSelect').fadeIn(500);
    $('.seatChartsContainer').empty();
    $('.optionsAreaBtn').removeClass('active btn-success').addClass('btn-info');
    $('.rowSelect').fadeOut(500);
    $('.seatSelect').fadeOut(500);
    $('#selectSeatSmallDev').fadeOut(500);
    $('#goToPayingSection').fadeOut(500);
}

function selectSeat(id) {

    var getParams = id.split("_");
    var reon = 'Партер';
    var area = '';

    var data = JSON.stringify({
        sub_id: getParams[0],
        token: eventObject.token,
        level_1: getParams[1],
        level_2: getParams[2],
        level_3: getParams[3],
        level_4: getParams[4]
    });

    var selectSeatE = $.ajax({
        type: "POST",
        url: "select_seat.php",
        data: data
    });

    $.when(selectSeatE).done(function (data) {
        if (!data) {
            $('#selectErrorDialog').modal('show');
            $('#selectErrorDialog').on('hidden.bs.modal', function () {
                location.reload();
            });
        } else {
            var d = JSON.parse(data);
            if (d.res === true && d.selected === true) {
                eventObject.token = d.token;
                tickets.selectedSeats.push(id);
                tickets.nr += 1;
                tickets.price += Number(eventObject.price);
                $('.totalTicketNum').text(tickets.nr);
                $('.totalSum').text(tickets.price);

                $('#' + id).removeClass('btn-info active').addClass('btn-success');
                $('option#seat_' + id).hide();
                $('#displayAvSeats').prop("selectedIndex", 0);

                if (eventObject.place == '2') {
                    switch (getParams[1]) {
                        case '1':
                            area = 'А'
                            break;
                        case '2':
                            area = 'Б'
                            break;
                        case '3':
                            area = 'В'
                            break;

                        default:
                            break;
                    }

                    switch (getParams[2]) {
                        case '1':
                            reon = 'Партер'
                            break;
                        case '2':
                            reon = 'Балкон'
                            break;

                        default:
                            break;
                    }
                } else if (eventObject.place == '1') {
                    switch (getParams[1]) {
                        case '1':
                            area = 'А'
                            break;
                        case '2':
                            area = 'Б'
                            break;
                        case '3':
                            area = 'Ц'
                            break;
                        case '4':
                            area = 'Д'
                            break;
                        case '5':
                            area = 'Е'
                            break;

                        default:
                            break;
                    }
                }


                var sdate = $('#eventDates option:selected').text();
                var stime = $('#eventTime option:selected').text();
                if (!$('.dateTimeDiv').hasClass(getParams[0])) {
                    $('<div class="dateTimeDiv ' + getParams[0] + '"><p class="text-success mb-0 mt-1">' + sdate + ' | ' + stime + '</p></div>').appendTo('.orderedTickets');
                }

                $('<li class="selectedItem' + getParams[0] + '" data-id="' + id + '"><span class="font-weight-bold">' + reon + '</span> | Блок: <span class="font-weight-bold">' + area + '</span> | Ред: <span class="font-weight-bold">' + getParams[3] + '</span> | Седиште: <span class="font-weight-bold">' + getParams[4] + '</span> | <a href="#/" class="text-danger" onclick="deleteSeat(\'' + id + '\')">Откажи</a></li>').appendTo('.dateTimeDiv.' + getParams[0]);

                if (mm.matches) {
                    $('.selectedSeatsBuyBtn').fadeIn(500);
                } else {
                    $('.selectedSeatsPreviewCOntainer').fadeIn(500);
                }

            } else if (d.res === true && d.selected === false) {
                eventObject.token = d.token;
                $('#faildSelectModal').modal('show');
                $('label#' + id).removeClass('btn-info btn-success').addClass('btn-danger disabled').css('pointer-events', 'none');
                $('option#seat_' + id).hide();
                $('#displayAvSeats').prop("selectedIndex", 0);
                getOcupiedSeats(eventObject.subId);
            } else {
                eventObject.token = d.token;
                $('#selectErrorDialog').modal('show');
            }

        }
    }).fail(function (data) {
        eventObject.token = d.token;
        $('#selectErrorDialog').modal('show');
    });

}

function deleteSeat(id) {
    var getParams = id.split("_");

    var data = JSON.stringify({
        sub_id: getParams[0],
        token: eventObject.token,
        level_1: getParams[1],
        level_2: getParams[2],
        level_3: getParams[3],
        level_4: getParams[4]
    });

    var deleteSeatE = $.ajax({
        type: "post",
        url: "delete_seat.php",
        data: data
    });

    $.when(deleteSeatE).done(function (data) {
        if (!data) {
            $('#selectErrorDialog').modal('show');
            $('#selectErrorDialog').on('hidden.bs.modal', function () {
                location.reload();
            });
        } else {
            var d = JSON.parse(data);
            if (d.res === true) {
                eventObject.token = d.token;
                tickets.selectedSeats.splice(tickets.selectedSeats.indexOf(id));
                tickets.nr -= 1;
                tickets.price -= Number(eventObject.price);
                $('.totalTicketNum').text(tickets.nr);
                $('.totalSum').text(tickets.price);

                $('#' + id).removeClass('btn-success active').addClass('btn-info');
                $('option#seat_' + id).show();
                $('li[data-id="' + id + '"]').remove();

                if (tickets.nr < 1) {
                    $('.selectedSeatsPreviewCOntainer').fadeOut(500);
                    $('#selectSeatSmallDev').fadeOut(500);
                    $('#goToPayingSection').fadeOut(500);
                    $("#cancelPaying").click();
                    $('#largescreenShoppingModal').modal('hide');
                }

                var selectedForDate = document.getElementsByClassName('selectedItem' + getParams[0]);

                if (selectedForDate.length === 0) {
                    $('.dateTimeDiv.' + getParams[0]).fadeOut(500).remove();
                }

            } else {
                $('#selectErrorDialog').modal('show');
                eventObject.token = d.token;
            }
        }
    }).fail(function (data) {
        $('#selectErrorDialog').modal('show');
        eventObject.token = d.token;
    });

}

$(document).on('change', '#eventDates', function () {
    $('#eventTime').empty();
    eventObject.subVal.forEach(element => {
        if (element.date == $(this).val()) {
            $('<option data-id="' + element.sub_id + '">' + element.time.slice(0, 5) + '</option>').appendTo('#eventTime');
        }
        eventObject.subId = $('#eventTime option:first').attr('data-id');
    });

    $('#selectBlockSection').empty();
    $('<div class="btn-group btn-group-toggle" data-toggle="buttons">' +
        '<label class="btn btn-info optionsGAreaBtn" id="balkonBB">' +
        '<input type="radio" name="options" id="option1" autocomplete="off" onchange="checkedArea(1)"> Балкон' +
        '</label>' +
        '<label class="btn btn-info optionsGAreaBtn" id="parterBB">' +
        '<input type="radio" name="options" id="option2" autocomplete="off" onchange="checkedArea(0)"> Партер' +
        '</label>' +
        '</div>').appendTo('#selectBlockSection');

    getOcupiedRowsBlocksArea();
    $('#blockSelect').fadeOut(500);
    $('.seatChartsContainer').empty();
    $('.seatChartsContainer').hide();
    $('#displayAvRows, #displayAvSeats').empty();
    $('.rowSelect').fadeOut(500);
    $('.seatSelect').fadeOut(500);
    $('#selectSeatSmallDev').fadeOut(500);
    $('#goToPayingSection').fadeOut(500);

    $('.optionsAreaBtn').removeClass('btn-success active').addClass('btn-info');

    getOcupiedSeats(eventObject.subId);
});

$(document).on('change', '#eventTime', function () {
    eventObject.subId = $(this).attr('data-id');
    $('#eventTime option:selected').attr('data-id', $(this).attr('data-id'));

    $('#selectBlockSection').empty();
    $('<div class="btn-group btn-group-toggle" data-toggle="buttons">' +
        '<label class="btn btn-info optionsGAreaBtn" id="balkonBB">' +
        '<input type="radio" name="options" id="option1" autocomplete="off" onchange="checkedArea(1)"> Балкон' +
        '</label>' +
        '<label class="btn btn-info optionsGAreaBtn" id="parterBB">' +
        '<input type="radio" name="options" id="option2" autocomplete="off" onchange="checkedArea(0)"> Партер' +
        '</label>' +
        '</div>').appendTo('#selectBlockSection');

    getOcupiedRowsBlocksArea();
    $('#blockSelect').fadeOut(500);
    $('.seatChartsContainer').empty();
    $('.seatChartsContainer').hide();
    $('#displayAvRows, #displayAvSeats').empty();
    $('.rowSelect').fadeOut(500);
    $('.seatSelect').fadeOut(500);
    $('#selectSeatSmallDev').fadeOut(500);
    $('#goToPayingSection').fadeOut(500);

    $('.optionsAreaBtn').removeClass('btn-success active').addClass('btn-info');

    getOcupiedSeats(eventObject.subId);
});

$(document).on('click', '.chartButton', function () {
    if ($(this).hasClass('btn-info')) {
        selectSeat(this.id);
    } else if ($(this).hasClass('btn-success')) {
        deleteSeat(this.id);
    }
});

$(document).on('click', '.optionsAreaBtn, .optionsGAreaBtn', function () {
    $(this).removeClass('btn-info').addClass('btn-success').siblings().removeClass('btn-success').addClass('btn-info');
});

$(document).on('change', '.optionsArea', function () {
    var id = this.id;
    var dataId = $(this).attr('data-id');
    var subId = eventObject.subId;
    var optionName = this.name;
    $('.seatChartsContainer').empty();
    $('.seatChartsContainer').hide();
    $('#displayAvRows, #displayAvSeats').empty();
    $('.rowSelect').fadeOut(500);
    $('.seatSelect').fadeOut(500);
    $('#selectSeatSmallDev').fadeOut(500);
    $('#goToPayingSection').fadeOut(500);

    $('<option> - </option>').appendTo('#displayAvRows');

    if (id == 'option1' && dataId == 'area_1') {
        if (mm.matches) {
            createRowSelects(balkon_a(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(balkon_a(subId));
        }
    }
    if (id == 'option2' && dataId == 'area_1') {
        if (mm.matches) {
            createRowSelects(balkon_b(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(balkon_b(subId));
        }
    }
    if (id == 'option3' && dataId == 'area_1') {
        if (mm.matches) {
            createRowSelects(balkon_v(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(balkon_v(subId));
        }
    }
    if (id == 'option1' && dataId == 'area_0') {
        if (mm.matches) {
            createRowSelects(parter_a(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(parter_a(subId));
        }
    }
    if (id == 'option2' && dataId == 'area_0') {
        if (mm.matches) {
            createRowSelects(parter_b(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(parter_b(subId));
        }
    }
    if (id == 'option3' && dataId == 'area_0') {
        if (mm.matches) {
            createRowSelects(parter_v(subId));
            $('.rowSelect').fadeIn(500);
        } else {
            createSeatChart(parter_v(subId));
        }
    }

    if (id == 'option1' && optionName == 'optionsAreaM') {
        if (mm.matches) {
            createRowSelects(milenium_a(subId));
            $('.rowSelect').fadeIn(500);
            getOcupiedRowsBlocksArea();
        } else {
            createSeatChart(milenium_a(subId), 'l');
        }

    }
    if (id == 'option2' && optionName == 'optionsAreaM') {
        if (mm.matches) {
            createRowSelects(milenium_b(subId));
            $('.rowSelect').fadeIn(500);
            getOcupiedRowsBlocksArea();
        } else {
            createSeatChart(milenium_b(subId));
        }
    }
    if (id == 'option3' && optionName == 'optionsAreaM') {
        if (mm.matches) {
            createRowSelects(milenium_c(subId));
            $('.rowSelect').fadeIn(500);
            getOcupiedRowsBlocksArea();
        } else {
            createSeatChart(milenium_c(subId), 'r');
        }
    }
    if (id == 'option4' && optionName == 'optionsAreaM') {
        if (mm.matches) {
            createRowSelects(milenium_d(subId));
            $('.rowSelect').fadeIn(500);
            getOcupiedRowsBlocksArea();
        } else {
            createSeatChart(milenium_d(subId), 'l', true);
        }
    }
    if (id == 'option5' && optionName == 'optionsAreaM') {
        if (mm.matches) {
            createRowSelects(milenium_e(subId));
            $('.rowSelect').fadeIn(500);
            getOcupiedRowsBlocksArea();
        } else {
            createSeatChart(milenium_e(subId), 'r', true);
        }
    }

});


$(document).on('change', '#displayAvRows', function () {
    var area = $('#displayAvRows option:selected').attr('id').split('_')[3];
    var block = $('#displayAvRows option:selected').attr('id').split('_')[2];
    var row = $('#displayAvRows option:selected').attr('id').split('_')[4];
    var subId = eventObject.subId;

    $('#displayAvSeats').empty();

    $('<option> - </option>').appendTo('#displayAvSeats');

    if (area == 2 && block == 1) {
        createSeatSelects(balkon_a(subId), row);
    }
    if (area == 2 && block == 2) {
        createSeatSelects(balkon_b(subId), row);
    }
    if (area == 2 && block == 3) {
        createSeatSelects(balkon_v(subId), row);
    }
    if (area == 1 && block == 1) {
        createSeatSelects(parter_a(subId), row);
    }
    if (area == 1 && block == 2) {
        createSeatSelects(parter_b(subId), row);
    }
    if (area == 1 && block == 3) {
        createSeatSelects(parter_v(subId), row);
    }
    if (area == 0 && block == 1) {
        createSeatSelects(milenium_a(subId), row);
    }
    if (area == 0 && block == 2) {
        createSeatSelects(milenium_b(subId), row);
    }
    if (area == 0 && block == 3) {
        createSeatSelects(milenium_c(subId), row);
    }
    if (area == 0 && block == 4) {
        createSeatSelects(milenium_d(subId), row);
    }
    if (area == 0 && block == 5) {
        createSeatSelects(milenium_e(subId), row);
    }

    getOcupiedSeats(eventObject.subId, 's');
});

$(document).on('change', '#displayAvSeats', function () {
    $('#selectSeatSmallDev').fadeIn(500);
});

function byNonNumberedTickets() {
    var ticketNumber = $('#selectTicketNr option:selected').val();
    tickets.nr = ticketNumber;
    tickets.price = ticketNumber * eventObject.price;

    if (mm.matches) {
        $("#selectTicketsCard").hide("slide", { direction: "left" }, 1000);
        $("#payTicketsCard").show("slide", { direction: "right" }, 1000);
        $("#selectTicketsCard").insertBefore($("#payTicketsCard"));
        $('.totalTicketNum').text(tickets.nr);
        $('.totalSum').text(tickets.price);
    } else {
        $('.hiddenWhenNoSeats').hide();
        $('.totalTicketNum').text(tickets.nr);
        $('.totalSum').text(tickets.price);
        $('#largescreenShoppingModal').modal('show');
    }
}


