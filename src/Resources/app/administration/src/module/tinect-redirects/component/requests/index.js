import template from './template.html.twig';

const {Mixin} = Shopware;
const {Criteria} = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    props: {
        redirect: {
            type: Object,
            default: null,
        },
    },

    data() {
        return {
            items: null,
            isLoading: true,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            page: 1,
            limit: 10,
        };
    },

    computed: {
        columns() {
            return [
                {
                    property: 'ipAddress',
                    label: 'tinect-redirects.detail.columnIpAddress',
                    allowResize: true,
                    sortable: false,
                }, {
                    property: 'userAgent',
                    label: 'tinect-redirects.detail.columnUserAgent',
                    allowResize: true,
                    sortable: false,
                }, {
                    property: 'createdAt',
                    label: 'tinect-redirects.detail.columnCreatedAt',
                    allowResize: true,
                    sortable: false,
                }
            ];
        },

        repository() {
            return this.repositoryFactory.create('tinect_redirects_redirect_request');
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('redirectId', this.redirect.id));
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return criteria;
        },

        date() {
            return Shopware.Filter.getByName('date');
        },
    },

    methods: {
        getList() {
            this.isLoading = true;
            return this.repository.search(this.criteria, Shopware.Context.api).then((result) => {
                this.total = result.total;
                this.items = result;
                this.isLoading = false;
            });
        },

        updateTotal({ total }) {
            this.total = total;
        },
    }
}