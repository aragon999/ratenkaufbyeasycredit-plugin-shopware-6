import Plugin from 'src/plugin-system/plugin.class';

export default class EasyCreditRatenkaufCheckout extends Plugin {
    init() {

        var handleEasyCreditRequired = function() {
            var form = $('#easycredit-payment-form');
            if (form.closest('.payment-method').find($(this)).length > 0) {
                $('#easycredit-agreement').attr('required','required');
            } else {
                $('#easycredit-agreement').removeAttr('required');
            }
        }

        $('input[type=radio][name=paymentMethodId]:checked').each(handleEasyCreditRequired);
        $('input[type=radio][name=paymentMethodId]').change(handleEasyCreditRequired);

        $('#easycredit-recalculate').click(function(){
            $('#confirmPaymentForm').submit();
            return false;
        });
    }
}
