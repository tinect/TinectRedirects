const { Component, Module } = Shopware;

Component.register('tinect-redirects-index', () => import('./page/tinect-redirects-index'));
Component.register('tinect-redirects-details', () => import('./page/tinect-redirects-details'));
Component.register('tinect-similar-requests', () => import('./component/requests'));
Component.register('tinect-similar-redirects', () => import('./component/similar-redirects'));

Shopware.Module.register('tinect-redirects', {
    type: 'plugin',
    name: 'tinect-redirects',
    title: 'tinect-redirects.general.title',
    description: 'tinect-redirects.general.title',
    color: '#189eff',
    icon: 'regular-rocket',
    entity: 'tinect_redirects_redirect',

    routes: {
        index: {
            component: 'tinect-redirects-index',
            path: 'index',
        },
        create: {
            component: 'tinect-redirects-details',
            path: 'create',
            meta: {
                parentPath: 'tinect.redirects.index',
            },
        },
        details: {
            component: 'tinect-redirects-details',
            path: 'details/:id',
            props: {
                default: (route) => {
                    return {
                        redirectId: route.params.id,
                    };
                },
            },
            meta: {
                parentPath: 'tinect.redirects.index',
            },
        }
    },

    settingsItem: [{
        id: 'tinect-redirects',
        path: 'tinect.redirects.index',
        parent: 'sw-settings',
        color: '#189eff',
        group: 'plugins',
        to: 'tinect.redirects.index',
        icon: 'regular-double-chevron-right-s',
        label: 'tinect-redirects.general.title',
    }],

    defaultSearchConfiguration: {
        _searchable: true,
        source: {
            _searchable: true,
            _score: 500,
        },
        target: {
            _searchable: true,
            _score: 500,
        },
    },
});
