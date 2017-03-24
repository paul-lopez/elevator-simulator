var timeouts = [];//timeouts array for stop
var delay = 0;//timeouts delay acumulator
var queue = [];//store each movement in array
var Elevator;
var INTERVAL = 500;
var nPendingRequest = 0;
function enableButtons() {
    //Reset data in server
    jQuery(document).on('click', '#btnResetServerData', function () {
        setResetSession();
    });
    //Reset data in log file
    jQuery(document).on('click', '#btnResetLog', function () {
        setResetLog();
    });
    //Stop timeouts
    jQuery(document).on('click', '#btnStop', function () {
        stopAnimation();
    });
    //each button in floors to up or down request
    jQuery(document).on('click', '.btnRequest', function () {
        var floor = jQuery(this).attr('data-id');
        var direction = jQuery(this).attr('data-direction');
        if (direction == 'maintenance') {
            setMaintenance(floor.split(','));//setFloorInMaintenance

        } else {
            setPressButton(floor, direction);//AddRequest
        }
    });
}

function setSignal(signal) {
    d = getApi({"action": "setSignal", "val": signal});
    return d.success;
}
function setCurrentFloor(floor) {
    d = getApi({"action": "setCurrentFloor", "val": floor});
    return d.success;
}
function setTotalFloors(nTotalFloors) {
    d = getApi({"action": "setTotalFloors", "val": Elevator.total_floors});
    return d.success;
}
function setDirection(direction) {
    d = getApi({"action": "setDirection", "val": direction});
    return d.success;
}
function setMaintenance(arrFloors) {
    d = getApi({"action": "setMaintenance", "val": arrFloors.join(',')}).done(function(){
        getStatusFromApi();
    });
    return d.success;
}
function setQueueUp(arrFloors) {
    d = getApi({"action": "setQueueUp", "val": arrFloors.join(',')});
    return d.success;
}
function setQueueDown(arrFloors) {
    d = getApi({"action": "setQueueDown", "val": arrFloors.join(',')});
    return d.success;
}

function setPressButton(floor, direction) {
    if(direction=='down'){
        Elevator.queue_down.push(floor);
        jQuery('#local_queueDown').text(Elevator.queue_down.join(','));
    }else{
        Elevator.queue_up.push(floor);
        jQuery('#local_queueUp').text(Elevator.queue_up.join(','));
    }
    d = getApi({"action": "setPressButton", "floor": floor, "direction": direction}).done(function(){
        getStatusFromApi().done(function(){
            if (nPendingRequest == 0) {
                delay = 0;
                getNextFloor();
            }
        });
    });
    return d.success;
}
function setResetSession() {
    stopAnimation();
    d = getApi({"action": "setResetSession"}).done(function(){
        getStatusFromApi().done(function(){
            etd = jQuery("td.door[data-id='" + Elevator.current_floor + "']");
            setItemColor(etd,'warning');
            Elevator.current_floor = Elevator.current_floor;
            jQuery("#local_current_floor").text(Elevator.current_floor);
            jQuery("#local_tmp_floor").text(Elevator.tmp_floor);
            jQuery("#local_direction").text(Elevator.direction);
            jQuery("#local_signal").text(Elevator.signal);
        });
    });
    return d.success;
}
function setResetLog() {
    d = getApi({"action": "setResetLog"});
    return d.success;
}

function getStatusFromApi() {
    try {
        var d, response;
        var dfd = jQuery.Deferred();
        dfd = getApi({'action': 'getStatusFromSession'}).done(function (r) {
            if (r.data) {
                e = r.data;
                Elevator.current_floor = e.current_floor;
                Elevator.direction = e.direction;
                Elevator.maintenance = e.maintenance;
                Elevator.queue_down = e.queue_down;
                Elevator.queue_up = e.queue_up;
                Elevator.signal = e.signal;
                Elevator.total_floors = e.total_floors;

                jQuery('#direction').text(e.direction);
                jQuery('#signal').text(e.signal);
                jQuery('#queueDown').text(e.queue_down.join(','));
                jQuery('#queueUp').text(e.queue_up.join(','));
                jQuery('#maintenance').text(e.maintenance.join(','));
                jQuery('#current_floor').text(e.current_floor);
                jQuery('#tmp_floor').text(e.tmp_floor);
                drawFloorsInMaintenance();//change color to red
            }
        });
        return dfd;
    } catch (err) {
        console.error('Error' + err);
    }
}
//Api Request
function getApi(data) {
    try {
        var action = data.action;
        var dfd = jQuery.Deferred();
        dfd = jQuery.getJSON('api.php', data, function (r, status) {
            if (status == "success") {
                if (r.success) {
                    //response = r.data;
                    return r.data;
                } else {
                    throw r.message;
                }
            }
        });
        return dfd;
    } catch (err) {
        return {'Error': err};
    }
}
//Show in screen the values in the Elevator object
function refreshElevatorStatus(e) {
    id = etd.attr('data-id');
    jQuery('#local_current_floor').text(id);
    jQuery('#local_tmp_floor').text(Elevator.tmp_floor);
    jQuery('#local_direction').text(Elevator.direction);
    //jQuery('#local_signal').text(Elevator.signal);
    jQuery('#local_queueDown').text(Elevator.queue_down.join(','));
    jQuery('#local_queueUp').text(Elevator.queue_up.join(','));
    jQuery('#local_maintenance').text(Elevator.maintenance.join(','));
}
//Get the next floor from Api
function getNextFloor() {
    try {
        getStatusFromApi().done(function(){
            if ((Elevator.queue_up.length + Elevator.queue_down.length) == 0) {
                return Elevator.current_floor;
            }
            var d, response;
            return getApi({'action': 'getNextFloor'}).done(function (r) {
                if (r.data) {
                    if (typeof r.data.current_floor == 'undefined') {
                        return Elevator.current_floor;
                    }
                    var nextFloor = parseInt(r.data.current_floor);
                    if (nextFloor == 0) {
                        return Elevator.current_floor;
                    }
                    queue.push(nextFloor);
                    animateFloors(Elevator.tmp_floor, nextFloor);
                    if (Elevator.tmp_floor != nextFloor) {
                        Elevator.tmp_floor = nextFloor;
                        return nextFloor;
                    }
                    return Elevator.current_floor;
                }
            });
        });
    } catch (err) {
        console.error('Error' + err);
    }
}
/**
 * Draw in the screen the floors who are in maintenance
 * Reset all floors to numbers, remove icons
 */
function drawFloorsInMaintenance() {
    //what floors are in maintenance?
    for (i = 1; i <= Elevator.total_floors; i++) {
        etd = jQuery("td.door[data-id='" + i + "']");
        if (isInMaintenance(i)) {
            //The maintenance floors in Red
            setItemColor(etd, 'danger');
            //Disable buttonsRequest()
            jQuery(".btnRequest[data-id='" + i + "']").prop('disabled', true);
        } else {
            jQuery(".btnRequest[data-id='" + i + "']").prop('disabled', false);
            resetItemClass(etd);
        }
    }

}
//Set color to door,floor
function setItemColor(etd, color) {
    id = etd.attr('data-id');
    resetItemClass(etd);
    etd.addClass(color);
    return etd;

};
//Remove all css classes including icons of door,floor
function resetItemClass(etd) {
    var status,i;
    etd.removeAttr('class').addClass('door');
    i = etd.attr('data-id');
    status = etd.find('.status_floor');
    status.text(i);
    status.removeAttr('class');
    status.addClass('status_floor').text(i);
    if(isInMaintenance(i)){
        etd.addClass('danger');
    }
    return etd;
}

//show icon in floor
function setIcon(etd, icon) {
    icon = selectIcon(icon);
    id = etd.attr('data-id');
    //resetItemClass(etd);
    etd.find('.status_floor').addClass('glyphicon glyphicon-' + icon).text("");
    jQuery("#local_current_floor").text(id);
    return etd;
}
//if the floor is in maintenance
function isInMaintenance(floor) {
    if (Elevator.maintenance.indexOf(parseInt(floor),0) >= 0) {
        return true;
    }
    return false;
};
//animateFloorsController
function animateFloors(start, end) {
    stopAnimation();//stop preview animations
    delay = INTERVAL;//restart delay
    //delay += INTERVAL;
    jQuery('#tmp_floor').text(Elevator.tmp_floor);
    jQuery('#local_tmp_floor').text(end);
    if (start < end) {
        jQuery('#local_direction').text('up');
        for (i = start; i <= end; i++) {
            animate(i, end, 'up');
        }
    } else {
        jQuery('#local_direction').text('down');
        for (i = start; i >= end; i--) {
            animate(i, end, 'down');
        }
    }
}
//just one floor to animate
function animate(i, end, dir) {
    nPendingRequest++;
    var etd = jQuery("td.door[data-id='" + i + "']");
    delay += INTERVAL;
    if (!isInMaintenance(i)) {
        if (i == end) {
            //finish
            timeouts.push(window.setTimeout(animateFloorEnd(etd, dir), delay));
        } else {
            timeouts.push(window.setTimeout(animateFloorStart(etd, dir), delay));
        }
    } else {
        timeouts.push(window.setTimeout(function () {
            setIcon(etd, 'maintenance');
        }, delay));
        delay += INTERVAL;
        timeouts.push(window.setTimeout(function () {
            resetItemClass(etd);
        }, delay));
    }
}
//when the elevator its moving
function animateFloorStart(e, dir) {
    id = e.attr('data-id');
    //add Green
    timeouts.push(window.setTimeout(function () {
        refreshElevatorStatus(e);
        setItemColor(e, 'success');
        setIcon(e, dir);
    }, delay));
    //Remove Green
    delay += INTERVAL;
    timeouts.push(window.setTimeout(function () {
        resetItemClass(e);
        nPendingRequest--;
    }, delay));
}
//when the elevator stop
function animateFloorEnd(e, dir) {
    id = e.attr('data-id');
    timeouts.push(window.setTimeout(function () {
        refreshElevatorStatus(e);
        setItemColor(e,'success');
        setIcon(e, dir);
    }, delay));
    delay += INTERVAL;
    timeouts.push(window.setTimeout(function () {
        setItemColor(e,'warning');
        setIcon(e, 'stand');
        jQuery('#local_direction').text('stand');
    }, delay));
    delay += INTERVAL * 3;
    timeouts.push(window.setTimeout(function () {
        setItemColor(e,'info');
        setIcon(e, 'open');
        jQuery('#local_direction').text();
        jQuery('#local_signal').text('door_open');
    }, delay));
    delay += INTERVAL;
    timeouts.push(window.setTimeout(function () {
        resetItemClass(e);
        jQuery('#local_signal').text('door_close');
        setItemColor(e,'warning');
        nPendingRequest--;
        if (nPendingRequest == 0) {
            delay = 0;
            getNextFloor();
        }else{
            getStatusFromApi();
        }
        refreshElevatorStatus(e);
    }, delay));

}
//Stop animation, remove all timeouts and refresh data
function stopAnimation() {
    for (var k in timeouts) {
        clearTimeout(timeouts[k]);
    }
    //getStatusFromApi();
}
//list of icons
function selectIcon(icon) {
    switch (icon) {
        case 'up':
            return 'menu-up';
            break;
        case 'down':
            return 'menu-down';
            break;
        case 'stand':
            return 'time';
            break;
        case 'alarm':
            return 'bell';
            break;
        case 'open':
            return 'user';
            break;
        case 'maintenance':
            return 'warning-sign';
            break;
        default:
            return 'time';
    }
}
jQuery(document).ready(function () {
    Elevator = {
        total_floors: 7,
        current_floor: 1,
        tmp_floor: 1,
        direction: 'up',
        queue_up: [],
        queue_down: [],
        maintenance: [],
        signal: 'door_close'
    };
    enableButtons();//buttons on UI
    getStatusFromApi().done(function(){
        getNextFloor();//move
    });
    //window.setInterval(function(){
    //    //getStatusFromApi().done(function() {
    //        if (nPendingRequest == 0) {
    //            delay = 0;
    //            var totalRequest = Elevator.queue_down.length + Elevator.queue_up.length;
    //            if (totalRequest >= 1) {
    //                getNextFloor();//move
    //            }
    //        }
    //    //});
    //}, 3000);
});