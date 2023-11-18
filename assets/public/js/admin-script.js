$(document).ready(function () {
    $(document).on('change', '#select-payment-method', function (e) {
        let method = $(this).val();
        if (!method) {
            $('#show-configs').hide('slow').empty();
            return false;
        }

        $.ajax({
            type: 'GET',
            url: juzaweb.adminUrl + '/ajax/subscription/payment-config',
            dataType: 'json',
            data: {method: method}
        }).done(function (response) {
            $('#show-configs').show('slow').html(response.html);

            return false;
        }).fail(function (response) {
            show_notify(response);
            return false;
        });
    });

    $(document).on('change', '#plan-form input[name=is_free]', function (e) {
        if ($(this).is(':checked')) {
            $('#plan-form input[name=price]').prop('disabled', true);
        } else {
            $('#plan-form input[name=price]').prop('disabled', false);
        }
    });

    $(document).on('change', '#plan-form input[name=enable_trial]', function (e) {
        if ($(this).is(':checked')) {
            $('#free-trial-days-box').show('slow').find('input[name=free_trial_days]').val(7);
        } else {
            $('#free-trial-days-box').hide('slow').find('input[name=free_trial_days]').val(0);
        }
    });

    $(document).on('click', '.sync-plan', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: juzaweb.adminUrl + '/ajax/subscription/update-plan',
            dataType: 'json',
            data: {
                plan_id: $(this).data('plan-id'),
            }
        }).done(function (response) {
            show_notify(response);
            return false;
        }).fail(function (response) {
            show_notify(response);
            return false;
        });
    });
});
