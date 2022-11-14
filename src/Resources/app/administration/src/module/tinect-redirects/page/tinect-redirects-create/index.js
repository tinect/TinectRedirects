const { Component } = Shopware;

Component.extend('tinect-redirects-create', 'tinect-redirects-details', {
    methods: {
        getRedirect() {
            this.detail = this.redirectRepository.create(Shopware.Context.api);
            this.detail.httpCode = 302;
            this.detail.active = false;
            this.detail.hidden = false;

            this.isLoading = false;
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
                this.isLoading = false;
                this.$router.push({ name: 'tinect.redirects.details', params: { id: this.detail.id } });
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
