// Shopware >= 6.7.x: supportedEntities moved to the general tab as computed property
Shopware.Component.override('sw-import-export-edit-profile-general', {
    computed: {
        supportedEntities() {
            const supportedEntities = this.$super('supportedEntities');

            supportedEntities.push({
                value: 'tinect_redirects_redirect',
                label: this.$tc('sw-import-export.profile.rlRedirectLabel'),
                type: 'import-export',
            });

            return supportedEntities;
        },
    },
});
