pimcore.registerNS('pimcore.plugin.GreenriversMagentoIntegrationBundle.settings');

pimcore.plugin.GreenriversMagentoIntegrationBundle.settings = Class.create({
    initialize: function () {
        this.getData();
    },

    getData: function () {
        Ext.Ajax.request({
            url: Routing.generate('pimcore_bundle_greenrivers_magento_integration_settings_get'),
            success: function (response) {
                this.data = Ext.decode(response.responseText);
                this.getTabPanel();
            }.bind(this)
        });
    },

    getValue: function (key, ignoreCheck) {
        const nk = key.split('\.');
        let current = this.data.values;

        for (let i = 0; i < nk.length; i++) {
            if (current[nk[i]]) {
                current = current[nk[i]];
            } else {
                current = null;
                break;
            }
        }

        if (ignoreCheck || (typeof current != 'object' && typeof current != 'array' && typeof current != 'function')) {
            return current;
        }

        return '';
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.panel = Ext.create('Ext.panel.Panel', {
                id: 'pimcore_settings_greenrivers_magento_integration',
                title: t('magento_integration_settings'),
                iconCls: "pimcore_icon_settings",
                border: false,
                layout: 'fit',
                closable: true
            });

            this.panel.on('destroy', function () {
                pimcore.globalmanager.remove('greenrivers_magento_integration_bundle');
            }.bind(this));

            this.layout = Ext.create('Ext.form.Panel', {
                bodyStyle: 'padding:20px 5px;',
                border: false,
                autoScroll: true,
                forceLayout: true,
                defaults: {
                    forceLayout: true
                },
                fieldDefaults: {
                    labelWidth: 250
                },
                buttons: [
                    {
                        text: t('save'),
                        handler: this.save.bind(this),
                        iconCls: 'pimcore_icon_apply'
                    }
                ],
                items: [
                    {
                        xtype: 'fieldset',
                        title: t('general'),
                        collapsible: false,
                        autoHeight: true,
                        items: [
                            {
                                xtype: 'textfield',
                                width: 650,
                                fieldLabel: t('magento_url'),
                                name: 'magentoUrl',
                                value: this.getValue('general.magentoUrl')
                            },
                            {
                                xtype: 'textfield',
                                width: 650,
                                fieldLabel: t('magento_token'),
                                name: 'magentoToken',
                                value: this.getValue('general.magentoToken')
                            }
                        ]
                    },
                    {
                        xtype: 'fieldset',
                        title: t('Pimcore'),
                        collapsible: false,
                        autoHeight: true,
                        items: [
                            {
                                xtype: 'checkboxfield',
                                fieldLabel: t('send_product_on_save'),
                                name: 'sendProductOnSave',
                                value: this.getValue('pimcore.sendProductOnSave')
                            },
                            {
                                xtype: 'checkboxfield',
                                fieldLabel: t('send_category_on_save'),
                                name: 'sendCategoryOnSave',
                                value: this.getValue('pimcore.sendCategoryOnSave')
                            }
                        ]
                    }
                ]
            });

            this.panel.add(this.layout);

            const tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem(this.panel);

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    activate: function () {
        const tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.setActiveItem("pimcore_settings_greenrivers_magento_integration");
    },

    getValues: function () {
        const values = this.layout.getForm().getFieldValues();

        Object.keys(values).forEach(function (key) {
            if (key.includes('displayfield')) {
                delete values[key];
            }
        });

        return values;
    },

    save: function () {
        const values = this.getValues();

        Ext.Ajax.request({
            url: Routing.generate('pimcore_bundle_greenrivers_magento_integration_settings_set'),
            method: 'PUT',
            params: {
                data: Ext.encode(values)
            },
            success: function (response) {
                try {
                    const res = Ext.decode(response.responseText);

                    if (res.success) {
                        pimcore.helpers.showNotification(t('success'), t('saved_successfully'), 'success');

                        Ext.MessageBox.confirm(t('info'), t('reload_pimcore_changes'), function (buttonValue) {
                            if (buttonValue === 'yes') {
                                window.location.reload();
                            }
                        }.bind(this));
                    } else {
                        pimcore.helpers.showNotification(t('error'), t('saving_failed'),
                            'error', t(res.message));
                    }
                } catch (e) {
                    pimcore.helpers.showNotification(t('error'), t('saving_failed'), 'error');
                }
            }
        });
    }
});
