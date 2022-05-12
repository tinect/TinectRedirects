import template from './tinect-redirects-list.html.twig';

const { Component } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('tinect-redirects-list', {
    template,

    inject: [
        'repositoryFactory',
    ],

    data() {
        return {
            redirects: null,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        columns() {
            return [{
                property: 'source',
                dataIndex: 'source',
                label: this.$tc('tinect-redirects.list.columnSourceUrl'),
                routerLink: 'tinect.redirects.details',
                inlineEdit: 'string',
                allowResize: true,
                primary: true,
            }, {
                property: 'target',
                dataIndex: 'target',
                label: this.$tc('tinect-redirects.list.columnTargetUrl'),
                inlineEdit: 'string',
                allowResize: true,
            }, {
                property: 'httpCode',
                dataIndex: 'httpCode',
                label: this.$tc('tinect-redirects.list.columnHttpCode'),
                allowResize: true,
            }];
        },
        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },
    },

    created() {
        this.redirectRepository.search(new Criteria(), Shopware.Context.api).then((result) => {
            this.redirects = result;
        });
    },

});
