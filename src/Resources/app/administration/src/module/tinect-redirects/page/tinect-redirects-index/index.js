import template from './tinect-redirects-index.html.twig';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('tinect-redirects-index', {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            isLoading: true,
            total: 0,
            isFullPage: true,
            filter: {
                term: null,
                salesChannelDomainId: null,
                active: null,
                hidden: 0,
            },
            firstEntryDate: null,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

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
        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },

        redirectRequestRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect_request');
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
    },

    methods: {
        getList() {
            this.isLoading = true;

            const criteria = new Criteria(this.page, this.limit);

            if (this.filter.term) {
                criteria.setTerm(this.filter.term);
            }

            if (this.filter.salesChannelDomainId) {
                criteria.addFilter(Criteria.equals('salesChannelDomainId', this.filter.salesChannelDomainId));
            }

            if (this.filter.active) {
                criteria.addFilter(Criteria.equals('active', this.filter.active));
            }

            criteria.addFilter(Criteria.equals('hidden', this.filter.hidden));

            criteria.addSorting(Criteria.sort('active', 'ASC'));
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            return this.redirectRepository.search(criteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        resetFilter() {
            this.filter = {
                term: null,
                salesChannelDomainId: null,
                active: null,
                hidden: 0,
            };
        },
    },

    watch: {
        filter: {
            deep: true,
            handler: Shopware.Utils.debounce(function () {
                this.getList();
            }, 400),
        },
    },

});
