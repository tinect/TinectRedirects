import template from './template.html.twig';

const { Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            isLoading: true,
            total: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            filter: {
                salesChannelDomainId: null,
                active: 0,
                hidden: 0,
            }
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
                    routerLink: 'tinect.redirects.details',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'target',
                    dataIndex: 'target',
                    label: this.$tc('tinect-redirects.detail.targetUrlLabel'),
                    routerLink: 'tinect.redirects.details',
                    allowResize: true,
                },
                {
                    property: 'httpCode',
                    dataIndex: 'httpCode',
                    label: this.$tc('tinect-redirects.detail.httpCodeLabel'),
                    routerLink: 'tinect.redirects.details',
                    allowResize: true,
                },
                {
                    property: 'count',
                    dataIndex: 'count',
                    label: this.$tc('tinect-redirects.list.countColumn'),
                    routerLink: 'tinect.redirects.details',
                    allowResize: true,
                },
            ];
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addFilter(Criteria.equals('active', this.filter.active));
            criteria.addFilter(Criteria.equals('hidden', this.filter.hidden));
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            if (this.filter.salesChannelDomainId) {
                criteria.addFilter(Criteria.equals('salesChannelDomainId', this.filter.salesChannelDomainId));
            }

            return criteria;
        },
    },

    watch: {
        filter: {
            handler() {
                this.getList();
            },
            deep: true,
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            return this.redirectRepository.search(this.criteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        resetFilter() {
            this.filter = {
                salesChannelDomainId: null,
                active: 0,
                hidden: 0,
            };
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },
};
