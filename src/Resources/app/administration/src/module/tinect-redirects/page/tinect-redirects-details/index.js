import './index.scss';
import template from './tinect-redirects-details.html.twig';
const Criteria = Shopware.Data.Criteria;

const { Component, Mixin } = Shopware;

Component.register('tinect-redirects-details', {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        redirectId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            detail: null,
            isLoading: true,
            processSuccess: false,
            similarItems: null,
            similarItemsIsLoading: true,
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

        hasSwUrlExt() {
            return Component.getComponentRegistry().has('sw-url-ext-field');
        },
    },

    created() {
        this.getRedirect();
    },

    methods: {
        getRedirect() {
            this.isLoading = true;
            this.redirectRepository.get(
                this.redirectId,
                Shopware.Context.api,
            ).then((entity) => {
                this.detail = entity;
                this.isLoading = false;

                let criteria = new Criteria();
                criteria.addFilter(Criteria.equals('source', entity.source));
                criteria.addFilter(Criteria.not('and', [
                    Criteria.equals('id', entity.id),
                ]));

                this.redirectRepository.search(criteria, Shopware.Context.api)
                    .then((searchResult) => {
                        this.similarItems = searchResult;
                        this.similarItemsIsLoading = false;
                    });
            });
        },

        onClickSave() {
            if (this.detail.source === this.detail.target) {
                this.createNotificationError({
                    title: this.$tc('tinect-redirects.detail.errorTitle'),
                    message: this.$tc('tinect-redirects.detail.errorSameUrlDescription'),
                });
                return;
            }

            this.isLoading = true;
            this.redirectRepository.save(this.detail, Shopware.Context.api).then(() => {
                this.getRedirect();

                this.isLoading = false;
                this.processSuccess = true;
            }).catch((exception) => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('tinect-redirects.detail.errorTitle'),
                    message: exception,
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        },
    },

    watch: {
        '$route.params.id': {
            handler: function(id) {
                if (this.detail && this.detail['id'] !== id) {
                    this.$router.go();
                }
            },
        }
    },
});
