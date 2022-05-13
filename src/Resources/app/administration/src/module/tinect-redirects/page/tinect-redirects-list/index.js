import template from './tinect-redirects-list.html.twig';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('tinect-redirects-list', {
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
            filter: {
                term: null,
                salesChannelDomainId: null,
            },
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
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
            ];
        },

        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            let criteria = new Criteria();
            criteria.addAssociation('salesChannelDomain');

            if (this.filter.term) {
                criteria.setTerm(this.filter.term);
            }

            if (this.filter.salesChannelDomainId) {
                criteria.addFilter(Criteria.equals('salesChannelDomainId', this.filter.salesChannelDomainId));
            }

            criteria.addSorting(Criteria.sort('active', 'ASC'));
            criteria.addSorting(Criteria.sort('source', 'ASC'));

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
            };
        },
    },

    watch: {
        filter: {
            deep: true,
            handler: Shopware.Utils.debounce(function () {
                this.getList();
            }, 400)
        }
    }

});
