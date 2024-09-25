import template from './template.html.twig';
import './style.scss';

const { Component, Mixin } = Shopware;

export default {
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    props: {
        redirectId: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            redirect: null,
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('tinect_redirects_redirect');
        },

        hasSwUrlExt() {
            return Component.getComponentRegistry().has('sw-dynamic-url-ext-field');
        },

        tooltipSave() {
            const systemKey = this.$device.getSystemKey();

            return {
                message: `${systemKey} + S`,
                appearance: 'light',
            };
        },

        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light',
            };
        },
    },

    watch: {
        redirectId() {
            this.getRedirect();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            Shopware.ExtensionAPI.publishData({
                id: 'tinect-redirects-detail__redirect',
                path: 'redirect',
                scope: this,
            });
            if (this.redirectId) {
                this.getRedirect();
                return;
            }

            this.redirect = this.repository.create();
        },

        getRedirect() {
            this.isLoading = true;

            this.repository.get(this.redirectId, Shopware.Context.api)
                .then((entity) => {
                    this.redirect = entity;
                    this.isLoading = false;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc(
                            'global.notification.notificationLoadingDataErrorMessage',
                        ),
                    });
                    this.isLoading = false;
                });
        },

        onSave() {
            this.isLoading = true;

            this.repository.save(this.redirect).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.redirectId === null) {
                    this.$router.push({ name: 'tinect.redirects.details', params: { id: this.redirect.id } });
                    return;
                }

                this.getRedirect();
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc('global.notification.notificationSaveErrorMessage', 0, {
                        entityName: this.download.id,
                    }),
                });
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'tinect.redirects.index' });
        }
    }
};
