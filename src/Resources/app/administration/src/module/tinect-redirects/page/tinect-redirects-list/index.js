import template from './tinect-redirects-list.html.twig';

const { Component} = Shopware;
const { Criteria } = Shopware.Data;

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

    data() {
        return {
            firstEntryDate: null
        }
    },

    inject: [
        'repositoryFactory',
    ],

    async created() {
        const criteria = new Criteria();
        criteria.addAggregation(Criteria.min('createdAt', 'createdAt'));
        criteria.setLimit(1);

        await this.redirectRequestRepository.search(criteria, Shopware.Context.api).then((result) => {
            const today = new Date();
            this.firstEntryDate = Math.ceil((today - new Date(result.aggregations.createdAt.min)) / (1000 * 60 * 60 * 24));
        });
    },

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
                    align: 'center',
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
                    label: this.$tc('tinect-redirects.list.countColumn', this.firstEntryDate),
                    allowResize: true,
                },
            ];
        },

        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },

        redirectRequestRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect_request');
        },
    },

});
