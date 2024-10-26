import template from './template.html.twig';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
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
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$tc('tinect-redirects.detail.activeLabel'),
                    routerLink: 'tinect.redirects.details',
                    align: 'center',
                    sortable: false,
                },
                {
                    property: 'source',
                    dataIndex: 'source',
                    label: this.$tc('tinect-redirects.detail.sourceUrlLabel'),
                    routerLink: 'tinect.redirects.details',
                    allowResize: true,
                    primary: true,
                    sortable: false,
                },
                {
                    property: 'target',
                    dataIndex: 'target',
                    label: this.$tc('tinect-redirects.detail.targetUrlLabel'),
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'httpCode',
                    dataIndex: 'httpCode',
                    label: this.$tc('tinect-redirects.detail.httpCodeLabel'),
                    allowResize: true,
                    sortable: false,
                },
                {
                    property: 'count',
                    dataIndex: 'count',
                    label: this.$tc('tinect-redirects.list.countColumn'),
                    allowResize: true,
                    sortable: false,
                },
            ];
        },

        repository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('source', this.redirect.source));
            criteria.addFilter(Criteria.not('and', [
                Criteria.equals('id', this.redirect.id),
            ]));
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return criteria;
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