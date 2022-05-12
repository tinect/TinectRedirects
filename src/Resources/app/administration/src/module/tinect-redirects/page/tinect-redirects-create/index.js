const { Component } = Shopware;

Component.extend('tinect-redirects-create', 'tinect-redirects-details', {
    methods: {
        getRedirect() {
            this.redirect = this.redirectRepository.create(Shopware.Context.api);
            this.redirect.httpCode = 301;

            this.isLoading = false;
        },

        onClickSave() {
            if (this.redirect.source === this.redirect.target) {
                this.createNotificationError({
                    title: this.$tc('tinect-redirects.detail.errorTitle'),
                    message: this.$tc('tinect-redirects.detail.errorSameUrlDescription'),
                });
                return;
            }

            this.isLoading = true;
            this.redirectRepository.save(this.redirect, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.$router.push({ name: 'tinect.redirects.details', params: { id: this.redirect.id } });
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('tinect-redirects.detail.errorTitle'),
                    message: exception,
                });
            });
        },
    },

});
