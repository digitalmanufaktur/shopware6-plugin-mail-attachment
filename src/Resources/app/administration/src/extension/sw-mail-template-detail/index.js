import template from './sw-mail-template-detail.html.twig';

const { Component } = Shopware;

Component.override('sw-mail-template-detail', {
    template
});
