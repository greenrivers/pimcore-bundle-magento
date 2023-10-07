pimcore.registerNS('pimcore.plugin.GreenriversMagentoIntegrationBundle');

pimcore.plugin.GreenriversMagentoIntegrationBundle = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (event) {
        const {menu} = event.detail;
        const user = pimcore.globalmanager.get('user');
        const perspectiveCfg = pimcore.globalmanager.get('perspective');

        const greenriversItems = [];
        if (
            user.isAllowed('greenrivers_magento_integration') &&
            perspectiveCfg.inToolbar('settings.greenrivers.magento_integration')
        ) {
            greenriversItems.push(
                {
                    text: t('menu_magento_integration'),
                    handler: this.greenriversMagentoIntegration,
                    iconCls: 'pimcore_icon_support',
                    itemId: 'pimcore_menu_settings_greenrivers_magento_integration'
                }
            );
        }

        if (user.isAllowed('greenrivers') && perspectiveCfg.inToolbar('settings.greenrivers')) {
            menu.settings.items.push({
                text: t('menu_greenrivers'),
                iconCls: 'pimcore_icon_greenrivers',
                priority: 75,
                menu: {
                    cls: 'pimcore_navigation_flyout',
                    shadow: false,
                    items: greenriversItems
                },
                itemId: 'pimcore_menu_settings_greenrivers',
                hideOnClick: false
            });
        }
    },

    greenriversMagentoIntegration: function (event) {
        try {
            pimcore.globalmanager.get('greenrivers_magento_integration_bundle').activate();
        } catch (e) {
            pimcore.globalmanager.add(
                'greenrivers_magento_integration_bundle',
                new pimcore.plugin.GreenriversMagentoIntegrationBundle.settings()
            );
        }
    }
});

const GreenriversMagentoIntegrationBundlePlugin = new pimcore.plugin.GreenriversMagentoIntegrationBundle();
