import template from './dmf-mail-media.html.twig';
import './dmf-mail-media.scss';

const { Component } = Shopware;

Component.register('dmf-mail-media', {
    template,

    data() {
        return {
            isCustomFieldsLoaded: false,
        };
    },

    props: {
        mailTemplate: {
            type: [Object, Boolean],
            required: true,
        },
    },

    watch: {
        mailTemplate() {
            this.mailTemplateChanged();
        },
    },

    methods: {
        mailTemplateChanged() {
            if (!this.mailTemplate) {
                return;
            }
            if (!this.mailTemplate.customFields) {
                this.mailTemplate.customFields = {};
            }
            this.isCustomFieldsLoaded = true;
        },
    },
});
