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
            filter: {
                term: null,
                salesChannelDomainId: null,
                active: null,
                hidden: 0
            },
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        redirectRepository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            let criteria = new Criteria();

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
            }, 400)
        }
    }

});
