import template from './tinect-redirects-list.html.twig';

const { Component} = Shopware;

Component.register('tinect-redirects-list', {
    template,

    props: {
        items: {
            type: Array,
            required: false,
            default: null,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        isFullPage: {
            type: Boolean,
            required: false,
            default: true,
        },
    },

    inject: [
        'repositoryFactory',
    ],

    computed: {
        open(id)  {
            const resolved = this.$router.resolve({
                name: 'tinect.redirects.details',
                params: { id: id }
            });
            window.location.replace(resolved.href);
        },

        columns() {
            return [
                {
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$tc('tinect-redirects.detail.activeLabel'),
                    routerLink: 'tinect.redirects.details',
                },
                {
                    property: 'source',
                    dataIndex: 'source',
                    label: this.$tc('tinect-redirects.detail.sourceUrlLabel'),
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'target',
                    dataIndex: 'target',
                    label: this.$tc('tinect-redirects.detail.targetUrlLabel'),
                    allowResize: true,
                },
                {
                    property: 'httpCode',
                    dataIndex: 'httpCode',
                    label: this.$tc('tinect-redirects.detail.httpCodeLabel'),
                    allowResize: true,
                },
                {
                    property: 'count',
                    dataIndex: 'count',
                    label: this.$tc('tinect-redirects.detail.countLabel'),
                    allowResize: true,
                },
            ];
        },

        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },
    },

});
