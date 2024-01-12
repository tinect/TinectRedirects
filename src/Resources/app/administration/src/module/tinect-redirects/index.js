import './page/tinect-redirects-index';
import './page/tinect-redirects-list';
import './page/tinect-redirects-details';
import './page/tinect-redirects-create';

Shopware.Module.register('tinect-redirects', {
    type: 'plugin',
    name: 'tinect-redirects',
    title: 'tinect-redirects.general.title',
    description: 'tinect-redirects.general.title',
    color: '#189eff',
    icon: 'regular-rocket',

    routes: {
        list: {
            component: 'tinect-redirects-index',
            path: 'list',
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
                parentPath: 'tinect.redirects.list',
            },
        },
        create: {
            component: 'tinect-redirects-create',
            path: 'create',
            meta: {
                parentPath: 'tinect.redirects.list',
            },
        },
    },

    settingsItem: [{
        id: 'tinect-redirects',
        path: 'tinect.redirects.list',
        parent: 'sw-settings',
        group: 'plugins',
        to: 'tinect.redirects.list',
        icon: 'regular-double-chevron-right-s',
        label: 'tinect-redirects.general.title',
    }],
});
