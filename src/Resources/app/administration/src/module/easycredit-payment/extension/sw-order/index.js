import { Component, State } from 'src/core/shopware';
import { get } from 'src/core/service/utils/object.utils';
import template from './sw-order.html.twig';

const easycreditFormattedHandlerIdentifier = 'handler_swag_easycreditpaymenthandler';

Component.override('sw-order-detail', {
    template,

    data() {
        return {
            isEasyCreditPayment: false
        };
    },

    computed: {
        paymentMethodStore() {
            return State.getStore('payment_method');
        },

        // TODO remove with PT-10455
        showTabs() {
            return true;
        }
    },

    watch: {
        order: {
            deep: true,
            handler() {
                const paymentMethodId = get(this.order, 'transactions[0].paymentMethod.id');
                if (paymentMethodId !== undefined && paymentMethodId !== null) {
                    this.setIsEasyCreditPayment(paymentMethodId);
                }
            }
        }
    },

    methods: {
        setIsEasyCreditPayment(paymentMethodId) {
            this.paymentMethodStore.getByIdAsync(paymentMethodId).then(
                (paymentMethod) => {
                    this.isEasyCreditPayment =
                        paymentMethod.formattedHandlerIdentifier === easycreditFormattedHandlerIdentifier;
                }
            );
        }
    }
});
