import $ from 'jquery';
import 'what-input';

// Foundation JS relies on a global varaible. In ES6, all imports are hoisted
// to the top of the file so if we used`import` to import Foundation,
// it would execute earlier than we have assigned the global variable.
// This is why we have to use CommonJS require() here since it doesn't
// have the hoisting behavior.
//window.jQuery = $;
//require('foundation-sites');

// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below
import './lib/foundation-explicit-pieces';

$(document).foundation();

function loadScheduler(startdate) {

    // preperation: set loader and define url
    setLoader();
    var url = "/ajax/scheduler";

    // add startdate if given (by prev or next button)
    if(typeof startdate !== 'undefined') {
        url += "?startdate=" + startdate;

        // destroy modals to recreate them
        //$('#timeModal').foundation('_destroy');
    }

    // ajax call
    $.ajax({
        url: url,
        success: function (data) {
            $('#scheduler').html(data);

            // re-initiate foundation (because new html injected)
            $(document).foundation();

            // behavior of date button clicked
            $('button[data-date]').click(function() {
	            if( $(this).hasClass('disabled') ) {
                    return false;
	            }

                // set date in schedule form
		        var date = $(this).attr('data-date');
                $('#scheduleForm').find('input[name=date]').val(date);
                $('#scheduleDate').text(date);

                // set date in time modal, show time buttons and open it
                $('#timeModal').find('h3').html(date);
                $('#timeModal').find('ul.times[data-date="'+date+'"]').show();
                $('#timeModal').foundation('open');
            });

            // behavior of time button clicked
            $('button[data-time]').click(function() {
                if( $(this).hasClass('disabled') ) {
                    return false;
                }

                // set time in schedule form
                var time = $(this).attr('data-time');
                $('#scheduleForm').find('input[name=time]').val(time);
                $('#scheduleTime').text(time);

                // close time modal and open adress modal
                $('#timeModal').foundation('close');
                $('#adressModal').foundation('open');
            });

            // hide all time buttons if modal closed
            $('[data-reveal]').on('closed.zf.reveal', function() {
                $(this).find('ul.times').hide();
            });

            // behavior of prev or next button clicked
            $('a[data-startdate]').click(function() {
                var attr = $(this).attr('data-startdate');
                loadScheduler(attr);
                return false;
            });

            // button to go back from adress modal to time modal
            $('button.back').click(function() {
                var date = $('#scheduleForm input[name=date]').val();
                $('#timeModal').find('ul.times[data-date="'+date+'"]').show();
                $('#adressModal').foundation('close');
                $('#timeModal').foundation('open');
            });

            // hover effect for time buttons
            $('button[data-time]').hover(
	            function() { $(this).removeClass('hollow') },
	            function() { $(this).addClass('hollow') }
            );
        }
    });
}

function setLoader() {
    var loaderHtml = '<p class="text-center">Kalender werden durchsucht...</p><div class="lds-ring"><div></div><div></div><div></div><div></div></div>';
    $('#scheduler').html(loaderHtml);
}

if( $("#scheduler").length ) {
    loadScheduler();
}

$('.events a').hover(
	function() { $(this).removeClass('hollow') },
	function() { $(this).addClass('hollow') }
);
