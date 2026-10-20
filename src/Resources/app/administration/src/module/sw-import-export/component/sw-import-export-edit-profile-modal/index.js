// Shopware < 6.7.x: supportedEntities is defined in the modal
Shopware.Component.override('sw-import-export-edit-profile-modal', {
    created() {
        if (!Array.isArray(this.supportedEntities)) {
            return;
        }

        this.supportedEntities.push({
            value: 'tinect_redirects_redirect',
            label: this.$tc('sw-import-export.profile.rlRedirectLabel'),
        });
    },
});
